<?php
session_start();
include 'db.php';

$post_id = $_GET['id'] ?? 0;

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $comment);
        $stmt->execute();
        header("Location: index.php"); // redirect to index page after comment added
        exit();
    }
}

// Fetch post data with user info
$stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

// Fetch comments for this post
$com_stmt = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY comments.created_at DESC");
$com_stmt->bind_param("i", $post_id);
$com_stmt->execute();
$comments = $com_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($post['title']) ?></title>
    <style>
        body {
            background-color: #e6f2e6;
            color: #2a3a2a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background-color: #f9fff9;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(150, 180, 150, 0.5);
        }
        h2 {
            color: #4a7c4a;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        p.byline {
            color: #6ba96b;
            font-style: italic;
            margin-top: 0;
            margin-bottom: 24px;
        }
        p.content {
            font-size: 1.1rem;
            line-height: 1.6;
            white-space: pre-line;
            margin-bottom: 40px;
            color: #355a35;
        }
        hr {
            border: 1px solid #b5d6b5;
            margin-bottom: 30px;
        }
        h3 {
            color: #6ba96b;
            margin-bottom: 20px;
            letter-spacing: 1.2px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .comment {
            background-color: #d9f0d9;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 15px;
            box-shadow: inset 0 0 10px rgba(120, 180, 120, 0.3);
            color: #2a3a2a;
            position: relative;
        }
        .comment strong {
            color: #3b703b;
        }
        textarea {
            width: 100%;
            height: 90px;
            border-radius: 12px;
            border: 1.5px solid #a3cfa3;
            padding: 12px 14px;
            background-color: #f1fbf1;
            color: #2a3a2a;
            font-size: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 0 8px #9bd29b;
            resize: vertical;
            outline: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        textarea:focus {
            background-color: #e3f6e3;
            box-shadow: 0 0 12px #7fc67f;
            border-color: #7fc67f;
        }
        button {
            margin-top: 12px;
            width: 100%;
            padding: 14px 0;
            border: none;
            border-radius: 12px;
            background-color: #7fc67f;
            color: #f9fff9;
            font-weight: 700;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(127, 198, 127, 0.7);
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #5ca65c;
            color: #e6f2e6;
        }
        a.login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-weight: 600;
            color: #7fc67f;
            text-decoration: none;
            letter-spacing: 1px;
        }
        a.login-link:hover {
            text-decoration: underline;
            color: #5ca65c;
        }
        .post-media {
            max-width: 100%;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(100, 150, 100, 0.3);
        }
        .delete-link {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1rem;
            color: #8b0000;
            text-decoration: none;
        }
        .delete-link:hover {
            color: #ff0000;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($post['title']) ?></h2>
    <p class="byline">by <strong><?= htmlspecialchars($post['username']) ?></strong></p>

    <?php if (!empty($post['media_path'])): ?>
        <?php
        $media_filename = basename($post['media_path']);
        $media_url = "uploads/" . $media_filename;

        $ext = strtolower(pathinfo($media_filename, PATHINFO_EXTENSION));
        if (file_exists(__DIR__ . "/uploads/" . $media_filename)):
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                <img src="<?= htmlspecialchars($media_url) ?>" alt="Post Media" class="post-media" />
            <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                <video controls class="post-media">
                    <source src="<?= htmlspecialchars($media_url) ?>" type="video/<?= $ext ?>">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p><a href="<?= htmlspecialchars($media_url) ?>" target="_blank">View media file</a></p>
            <?php endif; ?>
        <?php else: ?>
            <p style="color:red;">Media file not found: <?= htmlspecialchars($media_filename) ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <p class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    <hr>

    <h3>Comments</h3>

    <?php if ($comments->num_rows === 0): ?>
        <p style="text-align:center; color:#7ca97c;">No comments yet. Be the first!</p>
    <?php endif; ?>

    <?php while ($row = $comments->fetch_assoc()): ?>
        <div class="comment">
            <p><strong><?= htmlspecialchars($row['username']) ?>:</strong> <?= htmlspecialchars($row['comment']) ?></p>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                <a href="delete_comment.php?id=<?= $row['id'] ?>&post_id=<?= $post_id ?>"
                   onclick="return confirm('Are you sure you want to delete this comment?');"
                   class="delete-link" title="Delete comment">🗑️</a>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post">
            <textarea name="comment" placeholder="Write your comment here..." required></textarea><br>
            <button type="submit">Add Comment</button>
        </form>
    <?php else: ?>
        <a class="login-link" href="login.php">Login</a> to comment.
    <?php endif; ?>
</div>
</body>
</html>
