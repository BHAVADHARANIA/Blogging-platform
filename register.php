<?php
include 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        header("Location: login.php");
        exit();
    } else {
        $error = "Username already exists.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0fff0, #e8f5e9);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #2f4f4f;
        }

        .container {
            background: #ffffff;
            border-radius: 16px;
            max-width: 440px;
            width: 100%;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4a4a4a;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #c8e6c9;
            background-color: #f9fdf9;
            font-size: 1rem;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #81c784;
            box-shadow: 0 0 8px #a5d6a7;
            background-color: #ffffff;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background-color: #66bb6a;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #388e3c;
        }

        .error-message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            background: #ffcdd2;
            color: #c62828;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(200, 0, 0, 0.1);
            text-align: center;
        }

        .error-message a {
            display: inline-block;
            margin-left: 8px;
            color: #b71c1c;
            text-decoration: underline;
            font-weight: 600;
        }

        .error-message a:hover {
            color: #880e4f;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 0.95rem;
            text-align: center;
            color: #555;
        }

        .footer-text a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" role="main">
        <h2>Create Account</h2>
        <form method="post" novalidate>
            <label for="username">Username</label>
            <input id="username" type="text" name="username" required placeholder="Enter username" autocomplete="username" />

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Create password" autocomplete="new-password" />

            <button type="submit" aria-label="Register">Register</button>
        </form>

        <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
                <a href="login.php">Login</a>
            </div>
        <?php endif; ?>

        <p class="footer-text">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
