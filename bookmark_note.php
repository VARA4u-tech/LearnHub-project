<?php
session_start();
header('Content-Type: application/json');

require_once "config/database.php";

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $response['message'] = 'You must be logged in to bookmark a note.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_id'])) {
    $database = new Database();
    $pdo = $database->getConnection();

    $user_id = $_SESSION['id'];
    $note_id = filter_var($_POST['note_id'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($note_id)) {
        $response['message'] = 'Invalid note ID.';
        echo json_encode($response);
        exit;
    }

    try {
        // Check if already bookmarked
        $stmt = $pdo->prepare("SELECT id FROM note_bookmarks WHERE user_id = :user_id AND note_id = :note_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Already bookmarked, so unbookmark
            $stmt = $pdo->prepare("DELETE FROM note_bookmarks WHERE user_id = :user_id AND note_id = :note_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();
            $response['success'] = true;
            $response['bookmarked'] = false;
            $response['message'] = 'Note unbookmarked successfully.';
        } else {
            // Not bookmarked, so bookmark
            $stmt = $pdo->prepare("INSERT INTO note_bookmarks (user_id, note_id) VALUES (:user_id, :note_id)");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();
            $response['success'] = true;
            $response['bookmarked'] = true;
            $response['message'] = 'Note bookmarked successfully.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    unset($pdo);
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
?>