<?php
session_start();
require 'db.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Verify product exists in database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Initialize cart session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // If product is already in cart, increase quantity; otherwise, set to 1
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }
    }
}

// Redirect back to shop or cart page
header("Location: cart.php");
exit();
?>