<?php
session_start();
require_once "config/database.php";
require_once "includes/google_api_helper.php";

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: notes.php");
    exit;
}

// CSRF Token Verification
if (!isset($_GET['csrf_token']) || !isset($_SESSION['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header("location: notes.php");
    exit;
}

$note_id = trim($_GET["id"]);
$user_id = $_SESSION["id"];

$sql = "SELECT uploader_id, file_path FROM notes WHERE id = :id";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":id", $note_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($note["uploader_id"] == $user_id) {
                // Delete file from Google Drive
                delete_file_from_drive($note["file_path"]);

                // Delete note from database
                $sql_delete = "DELETE FROM notes WHERE id = :id";
                if ($stmt_delete = $pdo->prepare($sql_delete)) {
                    $stmt_delete->bindParam(":id", $note_id, PDO::PARAM_INT);
                    $stmt_delete->execute();
                }
            }
        }
    }
}

header("location: notes.php");
exit;
?>