<?php
session_start();

if (!isset($_SESSION['google_access_token']) || !$_SESSION['google_access_token']) {
    header("location: login.php?error=google_auth_required");
    exit;
}

require_once "config/database.php";
require_once "includes/google_api_helper.php";

$database = new Database();
$pdo = $database->getConnection();

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $note_id = filter_var(trim($_GET['id']), FILTER_SANITIZE_NUMBER_INT);

    if (empty($note_id)) {
        // Redirect to an error page or display a message
        header("location: notes.php?error=invalid_note_id");
        exit;
    }

    $sql = "SELECT file_path, file_name FROM notes WHERE id = :id";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":id", $note_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $file_id = $row['file_path']; // This is the google_drive_file_id
                    $file_name = $row['file_name'];

                    $file_content = download_file_from_drive($file_id);

                    if ($file_content) {
                        // Increment download count
                        $update_sql = "UPDATE notes SET download_count = download_count + 1 WHERE id = :id";
                        if ($update_stmt = $pdo->prepare($update_sql)) {
                            $update_stmt->bindParam(":id", $note_id, PDO::PARAM_INT);
                            $update_stmt->execute();
                        }

                        // Force download
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . strlen($file_content));
                        echo $file_content;
                        exit;
                    } else {
                        $_SESSION['error'] = "Error: File not found in Google Drive.";
                        header("location: notes.php");
                        exit;
                    }
                }
            } else {
                $_SESSION['error'] = "Error: Note not found.";
                header("location: notes.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Oops! Something went wrong. Please try again later.";
            header("location: notes.php");
            exit;
        }
    }
} else {
    $_SESSION['error'] = "Error: Invalid request.";
    header("location: notes.php");
    exit;
}