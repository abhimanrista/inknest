<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Ensure cart is not empty
$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// 3. Capture payment method from cart or confirmation step
$payment_method = $_POST['payment_method'] ?? $_POST['confirmed_payment_method'] ?? 'COD';

// Calculate grand total
$grand_total = 0;
foreach ($cart_items as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}

// 4. If user clicked "Confirm and Pay Now", process the order into MySQL
if (isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $transaction_uuid = "INK-" . time() . "-" . mt_rand(1000, 9999);
    $db_payment_label = ($payment_method === 'COD') ? 'Cash on Delivery' : 'eSewa';

    try {
        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, payment_status, transaction_uuid) VALUES (?, ?, ?, 'Pending', ?)");
        $stmt->execute([$_SESSION['user_id'], $grand_total, $db_payment_label, $transaction_uuid]);
        $order_id = $pdo->lastInsertId();

        // Insert into order_items table
        foreach ($cart_items as $product_id => $item) {
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $item_stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
        }

        if ($payment_method === 'COD') {
            // Clear cart and redirect to invoice bill (which updates profile history)
            unset($_SESSION['cart']);
            header("Location: invoice.php?order_id=" . $order_id);
            exit();
        } else {
            // eSewa Sandbox Gateway Parameters
            $merchant_code = "EPAYTEST";
            $secret_key = "8gBm/:&EnhH.1/q";
            $success_url = "http://localhost/inknest/payment-success.php?order_id=" . $order_id;
            $failure_url = "http://localhost/inknest/cart.php";

            $message = "total_amount={$grand_total},transaction_uuid={$transaction_uuid},product_code={$merchant_code}";
            $signature = base64_encode(hash_hmac('sha256', $message, $secret_key, true));
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head><title>Redirecting to eSewa...</title></head>
            <body onload="document.getElementById('esewa-form').submit();">
                <div style="text-align: center; margin-top: 20vh; font-family: Arial, sans-serif; color: #fff; background: #121212;">
                    <h2>Connecting to eSewa Secure Gateway...</h2>
                    <form id="esewa-form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                        <input type="hidden" name="amount" value="<?= $grand_total ?>">
                        <input type="hidden" name="tax_amount" value="0">
                        <input type="hidden" name="total_amount" value="<?= $grand_total ?>">
                        <input type="hidden" name="transaction_uuid" value="<?= $transaction_uuid ?>">
                        <input type="hidden" name="product_code" value="<?= $merchant_code ?>">
                        <input type="hidden" name="product_service_charge" value="0">
                        <input type="hidden" name="product_delivery_charge" value="0">
                        <input type="hidden" name="success_url" value="<?= $success_url ?>">
                        <input type="hidden" name="failure_url" value="<?= $failure_url ?>">
                        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
                        <input type="hidden" name="signature" value="<?= $signature ?>">
                    </form>
                </div>
            </body>
            </html>
            <?php
            exit();
        }
    } catch (\PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>

<!-- 5. Review Screen Display (Shown before saving to database) -->
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
        .btn-secondary { background: #555; margin-right: 10px; text-decoration: none; padding: 12px 20px; border-radius: 4px; color: white; }
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
        <a href="cart.php" class="btn-secondary">&larr; Back to Cart</a>
        
        <form action="checkout.php" method="POST">
            <input type="hidden" name="confirmed_payment_method" value="<?= htmlspecialchars($payment_method) ?>">
            <input type="hidden" name="action" value="place_order">
            <button type="submit" class="btn">Confirm and Pay Now &rarr;</button>
        </form>
    </div>
</div>

</body>
</html>