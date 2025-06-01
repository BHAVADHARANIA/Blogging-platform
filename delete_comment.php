<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    // User not logged in
    header("Location: login.php");
    exit;
}

$commentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if ($commentId <= 0 || $postId <= 0) {
    echo "Invalid request.";
    exit;
}

// Check if the comment belongs to the logged-in user
$sql = "SELECT user_id FROM comments WHERE id = $commentId LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $comment = $result->fetch_assoc();
    if ($comment['user_id'] == $_SESSION['user_id']) {
        // User owns the comment, delete it
        $deleteSql = "DELETE FROM comments WHERE id = $commentId";
        if ($conn->query($deleteSql)) {
            // Redirect back to the post page after deletion
            header("Location: view_post.php?id=$postId");
            exit;
        } else {
            echo "Failed to delete the comment. Please try again.";
        }
    } else {
        echo "You are not authorized to delete this comment.";
    }
} else {
    echo "Comment not found.";
}
