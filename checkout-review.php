<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$cart_items = $_SESSION['cart'];
$payment_method = $_POST['payment_method'] ?? 'COD';

// Store payment method in session temporarily so it doesn't get lost in post requests
$_SESSION['pending_payment_method'] = $payment_method;

$grand_total = 0;
foreach ($cart_items as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Order Review</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; }
        h1, h2 { color: #ff4757; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #333; text-align: left; }
        th { background: #2a2a2a; color: #ff4757; }
        .btn { background: #2ecc71; color: white; border: none; padding: 12px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-secondary { background: #555; margin-right: 10px; }
        .summary-box { background: #2a2a2a; padding: 15px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Review Your Order</h1>
    <p>Please double-check your items and payment method before confirming.</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>Rs. <?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Payment Method: <span style="color: #3498db;"><?= htmlspecialchars($payment_method) ?></span></h3>
        <h2>Grand Total: Rs. <?= number_format($grand_total, 2) ?></h2>
    </div>

    <div style="margin-top: 30px; display: flex; justify-content: space-between;">
        <a href="cart.php" class="btn btn-secondary">&larr; Back to Cart</a>
        
        <form action="checkout.php" method="POST">
            <button type="submit" class="btn">Confirm and Pay Now &rarr;</button>
        </form>
    </div>
</div>

</body>
</html>