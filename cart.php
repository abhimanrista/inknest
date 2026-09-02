<?php
session_start();
require 'db.php';

// Handle quantity updates or item removal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $qty = intval($qty);
            if ($qty > 0 && isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = $qty;
            } elseif ($qty <= 0 && isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit();
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Shopping Cart</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 0; }
        header { background: #1f1f1f; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ff4757; }
        .container { max-width: 1000px; margin: auto; padding: 40px 20px; }
        h1 { color: #ff4757; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #1f1f1f; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #333; text-align: left; vertical-align: middle; }
        th { background: #2a2a2a; color: #ff4757; }
        .thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #444; }
        .btn { background: #ff4757; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn:hover { background: #ff6b81; }
        .btn-secondary { background: #555; }
        .cart-summary { margin-top: 30px; background: #1f1f1f; padding: 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #333; }
        .total-price { font-size: 22px; color: #2ecc71; font-weight: bold; }
        input[type="number"] { width: 60px; padding: 6px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; text-align: center; }
        .nav-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>

    <header>
        <h1>InkNest 🦅</h1>
        <nav>
            <a href="index.php" style="color:white; text-decoration:none;">&larr; Continue Shopping</a>
        </nav>
    </header>

    <div class="container">
        <h1>Your Shopping Cart</h1>

        <?php if (count($cart_items) > 0): ?>
            <form method="POST" action="cart.php">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $id => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" class="thumb" alt="Product">
                                <?php else: ?>
                                    <span style="color:#777; font-size:12px;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>Rs. <?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" name="quantities[<?= $id ?>]" value="<?= $item['quantity'] ?>" min="1">
                            </td>
                            <td>Rs. <?= number_format($subtotal, 2) ?></td>
                            <td>
                                <a href="cart.php?remove=<?= $id ?>" style="color: #e74c3c; text-decoration: none; font-weight: bold;">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                    <a href="cart.php?clear=true" onclick="<?php unset($_SESSION['cart']); ?>" class="btn" style="background:#e74c3c;">Clear Cart</a>
                </div>
            </form>

            <div class="cart-summary">
                <div>
                    <h3>Grand Total:</h3>
                    <div class="total-price">Rs. <?= number_format($grand_total, 2) ?></div>
                </div>
                <div>
                    <a href="#" onclick="alert('eSewa Checkout integration coming up next!'); return false;" class="btn">Proceed to Checkout</a>
                </div>
            </div>

        <?php else: ?>
            <p style="text-align: center; color: #888; margin-top: 50px;">Your cart is currently empty.</p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>