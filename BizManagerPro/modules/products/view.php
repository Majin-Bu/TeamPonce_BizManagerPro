<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$product_id = $_GET['id'] ?? null;
$product = null;

if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Fetch product
$query = "SELECT * FROM products WHERE product_id = ? AND user_id = ?";
$result = executeQuery($conn, $query, "ii", [$product_id, $user_id]);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_result($pid, $uid, $product_name, $description, $price, $stock, $created_at, $updated_at);
    $stmt->fetch();
    $product = compact('pid', 'product_name', 'description', 'price', 'stock', 'created_at');
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($product): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2><?php echo $product['product_name']; ?></h2>
                </div>
                <div class="col-md-4 text-end">
                    <a href="edit.php?id=<?php echo $product['pid']; ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                    <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
                    <p><strong>Stock Quantity:</strong> 
                        <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $product['stock']; ?>
                        </span>
                    </p>
                    <p><strong>Created:</strong> <?php echo $product['created_at']; ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
