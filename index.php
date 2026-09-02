<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

// Handle age verification session flag FIRST (before any HTML output)
if (isset($_GET['verify']) && $_GET['verify'] == 'true') {
    $_SESSION['verified_18'] = true;
    header("Location: index.php");
    exit();
}

// Fetch all products from MySQL to display in the shop
try {
    $stmt = $pdo->query("SELECT products.*, categories.name as category_name FROM products JOIN categories ON products.category_id = categories.id ORDER BY products.id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Tattoo Supply Nepal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #121212; color: #fff; }
        header { background: #1f1f1f; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ff4757; }
        .container { max-width: 1200px; margin: auto; padding: 40px 20px; }
        .hero { text-align: center; margin-bottom: 40px; }
        .hero h2 { color: #ff4757; font-size: 32px; margin-bottom: 10px; }
        
        /* Product Grid Styling */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: #1f1f1f; border: 1px solid #333; border-radius: 8px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card h3 { margin: 0 0 10px 0; color: #fff; font-size: 20px; }
        .category-tag { font-size: 12px; background: #333; color: #ff4757; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-bottom: 10px; }
        .description { color: #aaa; font-size: 14px; margin-bottom: 15px; flex-grow: 1; }
        .price-stock { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .price { color: #2ecc71; font-size: 18px; font-weight: bold; }
        .stock { font-size: 13px; color: #888; }
        .btn-buy { background: #ff4757; color: white; border: none; padding: 10px; text-align: center; border-radius: 4px; cursor: pointer; text-decoration: none; font-weight: bold; }
        .btn-buy:hover { background: #ff6b81; }
        
        /* Age Modal Styling */
        #age-modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content { background: #1f1f1f; padding: 30px; border-radius: 8px; text-align: center; border: 1px solid #ff4757; max-width: 400px; }
        .btn { padding: 10px 20px; background: #ff4757; color: white; border: none; cursor: pointer; font-size: 16px; margin: 10px; border-radius: 4px; }
        .btn-secondary { background: #555; }
    </style>
</head>
<body>

    <!-- 18+ Age Verification Modal -->
    <?php if (!isset($_SESSION['verified_18'])): ?>
    <div id="age-modal">
        <div class="modal-content">
            <h2>Age Verification Required</h2>
            <p>InkNest sells professional tattoo equipment. You must be 18 years or older to enter.</p>
            <button class="btn" onclick="verifyAge(true)">I am 18 or older</button>
            <button class="btn btn-secondary" onclick="verifyAge(false)">I am under 18</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Single Main Header -->
    <header>
        <h1>InkNest 🦅</h1>
        <nav>
            <a href="index.php" style="color:white; margin-right:15px; text-decoration:none;">Shop</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #ff4757; margin-right: 15px;">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin.php" style="color:#3498db; margin-right:15px; text-decoration:none;">Admin Panel</a>
                <?php endif; ?>
                
                <a href="logout.php" style="color:#e74c3c; text-decoration:none;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="color:white; margin-right:15px; text-decoration:none;">Login</a>
                <a href="register.php" style="color:white; text-decoration:none;">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <div class="hero">
            <h2>Professional Tattoo Equipment Catalog</h2>
            <p>Browse high-grade rotary machines, organic inks, and sterile cartridge needles.</p>
        </div>

    
      <!-- Product Grid inside index.php -->
<div class="product-grid">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <div>
                    <?php if (!empty($p['image']) && file_exists('uploads/' . $p['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($p['image']) ?>" style="width:100%; height:180px; object-fit:cover; border-radius:4px; margin-bottom:15px;" alt="Product">
                    <?php endif; ?>
                    <span class="category-tag"><?= htmlspecialchars($p['category_name']) ?></span>
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="description"><?= htmlspecialchars($p['description']) ?></p>
                </div>
                <div>
                    <div class="price-stock">
                        <span class="price">Rs. <?= number_format($p['price'], 2) ?></span>
                        <span class="stock">Stock: <?= $p['stock'] ?></span>
                    </div>
                   <a href="add-to-cart.php?id=<?= $p['id'] ?>" class="btn-buy">Add to Cart</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; color: #888;">No tattoo gear available at the moment. Check back soon!</p>
    <?php endif; ?>
</div>

    <script>
        function verifyAge(isAdult) {
            if (isAdult) {
                window.location.href = "index.php?verify=true";
            } else {
                alert("You must be 18+ to access this website.");
                window.location.href = "https://www.google.com";
            }
        }
    </script>
</body>
</html>