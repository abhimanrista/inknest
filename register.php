<?php
session_start();
require 'db.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Email is already registered!";
        } else {
            // Hash the password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user with default role 'customer'
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $message = "Registration successful! You can now <a href='login.php' style='color:#ff4757;'>Login</a>.";
            } else {
                $message = "Something went wrong. Please try again.";
            }
        }
    } else {
        $message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; width: 350px; }
        h2 { color: #ff4757; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #ccc; }
        input { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; background: #ff4757; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #ff6b81; }
        .msg { color: #2ecc71; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>Create Account</h2>
    <?php if (!empty($message)): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="name" required placeholder="John Doe">
        </div>
        <div class="form-group">
            <label>Email Address:</label>
            <input type="email" name="email" required placeholder="john@example.com">
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit">Register</button>
    </form>
    <div class="link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>