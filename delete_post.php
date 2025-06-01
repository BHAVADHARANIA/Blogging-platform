<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$post_id = $_GET['id'] ?? 0;

if ($post_id <= 0) {
    echo "<p style='text-align:center; margin-top:50px; font-size:1.2rem; color:#ff4d4d;'>Invalid post ID.</p>";
    exit();
}

$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();
    header("Location: index.php");
    exit();
} else {
    $stmt->close();
    echo "<p style='text-align:center; margin-top:50px; font-size:1.2rem; color:#ff4d4d;'>Post not found or unauthorized deletion attempt.</p>";
}
