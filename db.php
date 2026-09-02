<?php
$host = 'localhost';
$db   = 'inknest_db';
$user = 'root';
$pass = ''; // Leave blank for default XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Set error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>