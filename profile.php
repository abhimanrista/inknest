<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's orders
$order_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$order_stmt->execute([$user_id]);
$orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - My Profile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 0; }
        header { background: #1f1f1f; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ff4757; }
        .container { max-width: 1000px; margin: auto; padding: 40px 20px; }
        h1, h2 { color: #ff4757; }
        .card { background: #1f1f1f; padding: 25px; border-radius: 8px; border: 1px solid #333; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #2a2a2a; border-radius: 6px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #333; text-align: left; }
        th { background: #333; color: #ff4757; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-completed { background: #2ecc71; color: white; }
        .badge-pending { background: #f39c12; color: white; }
        .btn-invoice { background: #3498db; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .btn-invoice:hover { background: #2980b9; }
        .nav-link { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>

    <header>
        <h1>InkNest 🦅</h1>
        <nav>
            <a href="index.php" class="nav-link" style="margin-right: 20px;">&larr; Back to Shop</a>
            <a href="logout.php" style="color: #e74c3c; text-decoration: none;">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h1>My Account Profile</h1>

        <!-- User Information Card -->
        <div class="card">
            <h2>Account Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Account Type:</strong> <span style="text-transform: uppercase; color: #3498db; font-weight: bold;"><?= htmlspecialchars($user['role']) ?></span></p>
        </div>

        <!-- Order History Section -->
        <div class="card">
            <h2>Order History</h2>
            <?php if (count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>#<?= $ord['id'] ?></td>
                            <td>Rs. <?= number_format($ord['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($ord['payment_method']) ?></td>
                            <td>
                                <?php if ($ord['payment_status'] === 'Completed'): ?>
                                    <span class="badge badge-completed">Completed</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $ord['created_at'] ?></td>
                            <td>
                                <a href="invoice.php?order_id=<?= $ord['id'] ?>" class="btn-invoice" target="_blank">View Bill</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #888; margin-top: 15px;">You haven't placed any orders yet.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>