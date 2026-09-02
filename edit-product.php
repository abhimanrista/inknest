<?php
session_start();
require 'db.php';

$message = "";
$product = null;

// 1. Check if product ID is provided in URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: admin.php");
        exit();
    }
} else {
    header("Location: admin.php");
    exit();
}

// 2. Handle Form Submission for Updating the Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // Keep existing image by default
    $imageName = $product['image'];

    // Check if a new image was uploaded
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = 'uploads/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Delete old image if it exists
                if (!empty($product['image']) && file_exists('uploads/' . $product['image'])) {
                    unlink('uploads/' . $product['image']);
                }
                $imageName = $newFileName;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->execute([$category_id, $name, $description, $price, $stock, $imageName, $id]);
        
        $message = "Product updated successfully!";
        // Refresh product data
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
    }
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>InkNest - Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; margin: 0; padding: 20px; }
        h1, h2 { color: #ff4757; }
        .container { max-width: 600px; margin: auto; background: #1f1f1f; padding: 30px; border-radius: 8px; border: 1px solid #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #ccc; }
        input, textarea, select { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        input[type="file"] { padding: 5px; background: transparent; border: none; }
        button { background: #3498db; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background: #2980b9; }
        .alert { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .nav-link { color: #3498db; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #444; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <a href="admin.php" class="nav-link">&larr; Back to Admin Dashboard</a>
    <h1>Edit Tattoo Gear</h1>

    <?php if (!empty($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit-product.php?id=<?= $product['id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $product['id'] ?>">

        <div class="form-group">
            <label>Product Name:</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="form-group">
            <label>Category:</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Price (NPR):</label>
            <input type="number" step="0.01" name="price" required value="<?= $product['price'] ?>">
        </div>

        <div class="form-group">
            <label>Stock Quantity:</label>
            <input type="number" name="stock" required value="<?= $product['stock'] ?>">
        </div>

        <div class="form-group">
            <label>Current Product Image:</label>
            <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="thumb" alt="Current Image">
            <?php else: ?>
                <p style="color:#777; font-size:13px;">No image uploaded</p>
            <?php endif; ?>
            
            <label style="margin-top: 10px;">Upload New Image (Optional - leaves current image if left blank):</label>
            <input type="file" name="product_image" accept="image/*">
        </div>

        <button type="submit" name="update_product">Update Product</button>
    </form>
</div>

</body>
</html>