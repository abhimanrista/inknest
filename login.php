<?php
session_start();
require 'db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password hash
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; width: 350px; }
        h2 { color: #ff4757; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #ccc; }
        input { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; background: #ff4757; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #ff6b81; }
        .error { color: #e74c3c; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>InkNest Login</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Email Address:</label>
            <input type="email" name="email" required placeholder="john@example.com">
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit">Login</button>
    </form>
    <div class="link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>