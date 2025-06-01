<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $postId = intval($_POST['post_id']);
    $userId = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $postId, $userId, $comment);
        $stmt->execute();
    }
}

header("Location: view_post.php?id=" . $_POST['post_id']);
exit();
