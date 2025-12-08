<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';
$product_name = '';
$description = '';
$price = '';
$stock = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock_quantity'] ?? 0);
    
    if (empty($product_name) || $price <= 0) {
        $error = 'Product name and price are required.';
    } else {
        $query = "INSERT INTO products (user_id, product_name, description, price, stock_quantity) VALUES (?, ?, ?, ?, ?)";
        $result = executeQuery($conn, $query, "issdi", [$user_id, $product_name, $description, $price, $stock]);
        
        if ($result['success']) {
            $success = 'Product added successfully!';
            $product_name = $description = $price = $stock = '';
        } else {
            $error = 'Failed to add product.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Add New Product</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="card p-4">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product_name; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $description; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price *</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $price; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo $stock; ?>">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Add Product</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
