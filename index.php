<?php
session_start();
include 'db.php';

$sql = "
    SELECT posts.*, users.username,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
";

$posts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Blog Home</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0fff0, #e8f5e9);
            color: #2f4f4f;
        }

        nav {
            background: #ffffff;
            border-bottom: 2px solid #c8e6c9;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
        }

        nav .nav-left {
            font-weight: bold;
            font-size: 1.8rem;
            color: #388e3c;
        }

        nav .nav-links a {
            margin-left: 20px;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            color: #4a4a4a;
            background-color: #e0f2f1;
            transition: all 0.3s ease;
        }

        nav .nav-links a:hover {
            background-color: #a5d6a7;
            color: #1b5e20;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #2e7d32;
        }

        .post {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.07);
            transition: transform 0.2s;
        }

        .post:hover {
            transform: scale(1.01);
        }

        .post h2 {
            margin-top: 0;
            font-size: 24px;
            color: #2e7d32;
        }

        .post h2 a {
            text-decoration: none;
            color: #2e7d32;
        }

        .post h2 a:hover {
            text-decoration: underline;
        }

        .post .author {
            color: #757575;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .post p {
            font-size: 15px;
            color: #4a4a4a;
        }

        .actions {
            margin-top: 12px;
            font-size: 14px;
        }

        .actions a {
            margin-right: 15px;
            text-decoration: none;
            font-weight: 600;
            color: #388e3c;
        }

        .actions a:hover {
            color: #1b5e20;
        }

        .like-count {
            color: #e53935;
            margin-left: 5px;
            font-weight: bold;
        }

        .post-media {
            margin-top: 15px;
        }

        .post-media img,
        .post-media video {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        hr {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 30px 0;
        }

        @media (max-width: 600px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
            }

            nav .nav-links {
                margin-top: 10px;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .post h2 {
                font-size: 20px;
            }

            .post p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-left">MyBlog</div>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="create_post.php">➕ Create Post</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h1>Blog Posts</h1>

    <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
            <h2><a href="view_post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
            <p class="author">by <?= htmlspecialchars($post['username']) ?></p>
            <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>

            <?php if (!empty($post['media_path'])): ?>
                <div class="post-media">
                    <?php
                        $mediaPath = htmlspecialchars($post['media_path']);
                        $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                    ?>
                        <img src="<?= $mediaPath ?>" alt="Post Image">
                    <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                        <video controls>
                            <source src="<?= $mediaPath ?>" type="video/<?= $ext ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <a href="like.php?id=<?= $post['id'] ?>">❤️ Like</a><span class="like-count">(<?= $post['like_count'] ?>)</span> |
                <a href="view_post.php?id=<?= $post['id'] ?>">💬 Comment</a> |
                <a href="https://wa.me/?text=<?= urlencode('Check this blog post: http://localhost/blog/view_post.php?id=' . $post['id']) ?>" target="_blank">🔗 Share</a>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                    | <a href="edit_post.php?id=<?= $post['id'] ?>">✏️ Edit</a>
                    | <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">🗑️ Delete</a>
                <?php endif; ?>
            </div>
        </div>
        <hr>
    <?php endwhile; ?>

</div>
</body>
</html>
