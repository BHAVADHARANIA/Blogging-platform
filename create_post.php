<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    // Handle file upload if provided
    $media_path = null;
    if (!empty($_FILES['media']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
        $fileType = $_FILES['media']['type'];
        $fileSize = $_FILES['media']['size'];
        $fileTmp = $_FILES['media']['tmp_name'];

        if (!in_array($fileType, $allowedTypes)) {
            $error = "Unsupported file type. Allowed: JPG, PNG, GIF images and MP4, WEBM, OGG videos.";
        } elseif ($fileSize > 20 * 1024 * 1024) { // max 20MB
            $error = "File size exceeds 20MB limit.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '-' . basename($_FILES['media']['name']);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $media_path = $targetFile;
            } else {
                $error = "Failed to upload file.";
            }
        }
    }

    if (!$error && $title !== '' && $content !== '') {
        // Insert post with media_path (nullable)
        $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, media_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $content, $media_path);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php");
            exit();
        } else {
            $error = "Database error: " . $conn->error;
        }
    } elseif (!$error) {
        $error = "Please fill in both title and content.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Post</title>
    <style>
        body {
            background: #f9f9f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
        }
        .container {
            max-width: 600px;
            background: #fff;
            margin: 70px auto;
            padding: 30px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            color: #4a4a4a;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #555;
            font-size: 1.1rem;
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 20px;
            border: 1.5px solid #c8e6c9;
            border-radius: 10px;
            font-size: 1.1rem;
            box-sizing: border-box;
            outline: none;
            resize: vertical;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
            border-color: #4caf50;
            box-shadow: 0 0 10px #a8e063;
            background-color: #e6f4d9;
            color: #333;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #a8e063;
            border: none;
            border-radius: 10px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #2e4a1f;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #7bb436;
            color: #fff;
        }
        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1.5px solid #f5c2c7;
        }
        .info-text {
            font-size: 0.9rem;
            color: #666;
            margin-top: -15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create New Post</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <label for="title">Title:</label>
        <input id="title" type="text" name="title" required autofocus value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">

        <label for="content">Content:</label>
        <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>

        <label for="media">Upload Photo or Video (optional):</label>
        <input type="file" id="media" name="media" accept="image/jpeg,image/png,image/gif,video/mp4,video/webm,video/ogg">
        <div class="info-text">Allowed types: JPG, PNG, GIF, MP4, WEBM, OGG. Max size: 20MB.</div>

        <button type="submit">Post</button>
    </form>
</div>

</body>
</html>
