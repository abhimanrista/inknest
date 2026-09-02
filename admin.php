<?php
session_start();
require 'db.php';

$message = "";

// Handle Form Submission for Adding a Product with Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $category_id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    $imageName = "";

    // Check if an image file was uploaded without errors
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate a unique file name to prevent overwriting
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageName = $newFileName;
            } else {
                $message = "Error moving the uploaded file.";
            }
        } else {
            $message = "Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.";
        }
    }

    if (empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $description, $price, $stock, $imageName]);
            $message = "Product added successfully with image!";
        } catch (\PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
        }
    }
}

// Handle Product Deletion (and remove its image file too)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch image filename first to delete it from folder
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prod && !empty($prod['image'])) {
        $filePath = 'uploads/' . $prod['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT products.*, categories.name as category_name FROM products JOIN categories ON products.category_id = categories.id ORDER BY products.id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 20px; }
        h1, h2 { color: #ff4757; }
        .container { max-width: 1000px; margin: auto; background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #333; text-align: left; vertical-align: middle; }
        th { background: #2a2a2a; color: #ff4757; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #ccc; }
        input, textarea, select { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        input[type="file"] { padding: 5px; background: transparent; border: none; }
        button { background: #ff4757; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #ff6b81; }
        .btn-danger { background: #e74c3c; padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; }
        .alert { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .nav-link { color: #3498db; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #444; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="nav-link">&larr; Back to Shop Website</a>
    <h1>InkNest Admin Dashboard</h1>
    <p>Manage tattoo equipment inventory and product images.</p>

    <?php if (!empty($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Add Product Form with multipart/form-data -->
    <h2>Add New Tattoo Gear</h2>
    <form method="POST" action="admin.php" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name:</label>
            <input type="text" name="name" required placeholder="e.g. Wireless Battery Pen">
        </div>
        <div class="form-group">
            <label>Category:</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="3" placeholder="Enter product specs..."></textarea>
        </div>
        <div class="form-group">
            <label>Price (NPR):</label>
            <input type="number" step="0.01" name="price" required placeholder="25000">
        </div>
        <div class="form-group">
            <label>Stock Quantity:</label>
            <input type="number" name="stock" required placeholder="10">
        </div>
        <div class="form-group">
            <label>Product Image:</label>
            <input type="file" name="product_image" accept="image/*" required>
        </div>
        <button type="submit" name="add_product">Save Product</button>
    </form>

    <hr style="border: 0; border-top: 1px solid #333; margin: 40px 0;">

    <!-- Inventory Table -->
    <h2>Inventory List</h2>
    <table>
        <thead>
        <td>
    <a href="edit-product.php?id=<?= $p['id'] ?>" style="background:#f39c12; padding:6px 12px; text-decoration:none; color:white; border-radius:4px; font-size:14px; margin-right:5px; display:inline-block;">Edit</a>
    <a href="admin.php?delete=<?= $p['id'] ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
</td>
        </thead>
        <tbody>
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <!-- Product Image Thumbnail -->
                    <td>
                        <?php if (!empty($p['image']) && file_exists('uploads/' . $p['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="thumb" alt="Product Image">
                        <?php else: ?>
                            <span style="color:#777; font-size:12px;">No image</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Product Name -->
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    
                    <!-- Category Name -->
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    
                    <!-- Price -->
                    <td>Rs. <?= number_format($p['price'], 2) ?></td>
                    
                    <!-- Stock -->
                    <td><?= $p['stock'] ?></td>
                    
                    <!-- Aligned Edit and Delete Buttons -->
                    <td>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <a href="edit-product.php?id=<?= $p['id'] ?>" style="background: #f39c12; padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; display: inline-block;">Edit</a>
                            <a href="admin.php?delete=<?= $p['id'] ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this item?');" style="display: inline-block;">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">No products found in database.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>