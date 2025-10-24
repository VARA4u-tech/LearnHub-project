<?php
session_start();
header('Content-Type: application/json');

require_once "config/database.php";

$database = new Database();
$pdo = $database->getConnection();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a note.']);
    exit;
}

if (isset($_POST['note_id'])) {
    $note_id = $_POST['note_id'];
    $user_id = $_SESSION['id'];

    try {
        $pdo->beginTransaction();

        // Check if the user has already liked the note
        $sql = "SELECT id FROM note_likes WHERE user_id = :user_id AND note_id = :note_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User has liked it, so unlike it
            $sql = "DELETE FROM note_likes WHERE user_id = :user_id AND note_id = :note_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE notes SET like_count = like_count - 1 WHERE id = :note_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();

            $liked = false;
        } else {
            // User has not liked it, so like it
            $sql = "INSERT INTO note_likes (user_id, note_id) VALUES (:user_id, :note_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE notes SET like_count = like_count + 1 WHERE id = :note_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
            $stmt->execute();

            $liked = true;
        }

        // Get the new like count
        $sql = "SELECT like_count FROM notes WHERE id = :note_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':note_id', $note_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $like_count = $result['like_count'];

        $pdo->commit();

        echo json_encode(['success' => true, 'like_count' => $like_count, 'liked' => $liked]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>