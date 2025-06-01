<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$post_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "<p style='text-align:center; margin-top:50px; font-size:1.2rem; color:#ff4d4d;'>Post not found or unauthorized access.</p>";
    exit();
}

$uploadError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Handle file upload if a file is selected
    if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['media'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = "Error uploading file.";
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $uploadError = "File type not allowed. Only images (jpg, png, gif) and videos (mp4, webm, ogg) are accepted.";
        } elseif ($file['size'] > $maxFileSize) {
            $uploadError = "File size exceeds 5MB limit.";
        } else {
            // Generate unique filename to avoid conflicts
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('media_', true) . "." . $ext;
            $destination = __DIR__ . "/uploads/" . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Delete old media file if exists
                if (!empty($post['media']) && file_exists(__DIR__ . "/uploads/" . $post['media'])) {
                    unlink(__DIR__ . "/uploads/" . $post['media']);
                }

                // Update post with new media filename
                $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, media = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("sssii", $title, $content, $newFileName, $post_id, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                header("Location: index.php");
                exit();
            } else {
                $uploadError = "Failed to move uploaded file.";
            }
        }
    } else {
        // No new file uploaded, update title & content only
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $content, $post_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <style>
        body {
            background-color: #e6f2e6; /* soft pastel green background */
            color: #2a3a2a; /* dark green text */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #f9fff9; /* very light green */
            max-width: 600px;
            margin: 70px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(150, 180, 150, 0.5);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #4a7c4a; /* medium pastel green */
        }
        label {
            font-weight: 600;
            font-size: 1.1rem;
            color: #355a35; /* darker green */
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            box-sizing: border-box;
            outline: none;
            resize: vertical;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #d9f0d9; /* light pastel green */
            color: #2a3a2a;
            box-shadow: 0 0 8px #9bd29b;
        }
        input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
            background-color: #e3f6e3;
            box-shadow: 0 0 12px #7fc67f;
            border: 1.5px solid #7fc67f;
            color: #204020;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #7fc67f; /* pastel green button */
            border: none;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #f9fff9;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(127, 198, 127, 0.7);
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #5ca65c;
            color: #e6f2e6;
        }
        .media-preview {
            margin-bottom: 20px;
            text-align: center;
        }
        .media-preview img, .media-preview video {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(127, 198, 127, 0.5);
        }
        .error {
            color: #b22222;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Post</h2>

    <?php if ($uploadError): ?>
        <p class="error"><?= htmlspecialchars($uploadError) ?></p>
    <?php endif; ?>

    <?php if (!empty($post['media'])): ?>
        <div class="media-preview">
            <?php
            $mediaPath = 'uploads/' . htmlspecialchars($post['media']);
            $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?= $mediaPath ?>" alt="Post Media">
            <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                <video controls>
                    <source src="<?= $mediaPath ?>" type="video/<?= $ext ?>">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p>Unsupported media format.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <label for="title">Title:</label><br>
        <input id="title" type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required autofocus><br>

        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea><br>

        <label for="media">Change Media File (optional):</label><br>
        <input id="media" type="file" name="media" accept="image/*,video/*"><br>

        <button type="submit">Update</button>
    </form>
</div>
</body>
</html>
