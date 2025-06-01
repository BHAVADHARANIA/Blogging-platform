<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #f0fff0, #e8f5e9);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2f4f4f;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #ffffff;
            max-width: 400px;
            margin: 80px auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #2e7d32;
        }

        label {
            font-weight: 600;
            font-size: 1rem;
            display: block;
            margin-bottom: 6px;
            color: #4a4a4a;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
            background-color: #f9fdf9;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #81c784;
            box-shadow: 0 0 6px #a5d6a7;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #66bb6a;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #388e3c;
        }

        .register-link {
            margin-top: 18px;
            font-size: 14px;
            text-align: center;
        }

        .register-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error {
            background-color: #ffcdd2;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
            color: #c62828;
            box-shadow: 0 2px 6px rgba(200, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form method="post">
        <label for="username">Username:</label>
        <input id="username" type="text" name="username" required autofocus>

        <label for="password">Password:</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="register-link">
        Don't have an account?
        <a href="register.php">Register here</a>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
</body>
</html>
