<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$products = [];
$q = sanitize($_GET['q'] ?? '');

// Fetch products for current user with optional search
$query = "SELECT * FROM products WHERE user_id = ?";
$params = [$user_id];
$types = "i";
if (!empty($q)) {
    $like = "%$q%";
    $query .= " AND (product_name LIKE ? OR description LIKE ?)";
    $params = [$user_id, $like, $like];
    $types = "iss";
}
$query .= " ORDER BY created_at DESC";
$result = executeQuery($conn, $query, $types, $params);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($product_id, $uid, $product_name, $description, $price, $stock, $created_at, $updated_at);
    
    while ($stmt->fetch()) {
        $products[] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => $stock,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <main class="container-fluid">
        <div class="container">
            <div class="row mb-4 mt-4">
                <div class="col-md-8">
                    <h1 class="fw-bold"><i class="fas fa-box"></i> Product Inventory</h1>
                    <p class="text-secondary">Manage your product catalog and stock levels</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info border-0">
                    <i class="fas fa-info-circle"></i> No products found. <a href="add.php" class="alert-link">Add one now</a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form class="d-flex" method="get" action="index.php">
                        <input type="text" class="form-control me-2" name="q" placeholder="Search products..." value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
                    </form>
                    <button type="submit" form="bulkDeleteForm" class="btn btn-danger" id="bulkDeleteBtn" disabled onclick="return confirm('Delete selected items?')">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                <div class="card">
                    <form id="bulkDeleteForm" method="post" action="bulk-delete.php">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="form-check-input select-all"></th>
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-tag"></i> Product Name</th>
                                        <th><i class="fas fa-align-left"></i> Description</th>
                                        <th><i class="fas fa-peso-sign"></i> Price</th>
                                        <th><i class="fas fa-warehouse"></i> Stock</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="<?php echo $product['product_id']; ?>"></td>
                                            <td><strong>#<?php echo $product['product_id']; ?></strong></td>
                                            <td><?php echo $product['product_name']; ?></td>
                                            <td><small><?php echo substr($product['description'], 0, 30) . '...'; ?></small></td>
                                            <td><strong>₱<?php echo number_format($product['price'], 0); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <i class="fas fa-<?php echo $product['stock_quantity'] > 0 ? 'check' : 'times'; ?>"></i> <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
