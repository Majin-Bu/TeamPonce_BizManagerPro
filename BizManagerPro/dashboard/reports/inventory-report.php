<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$export = $_GET['export'] ?? '';

// Get inventory data
$query = "SELECT product_id, product_name, price, stock_quantity FROM products WHERE user_id = ? ORDER BY product_name";
$result = executeQuery($conn, $query, "i", [$user_id]);
$products = [];

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($product_id, $product_name, $price, $stock_quantity);
    
    while ($stmt->fetch()) {
        $products[] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'price' => $price,
            'stock_quantity' => $stock_quantity
        ];
    }
    $stmt->close();
}

// Export to CSV
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory-report-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Product ID', 'Product Name', 'Price', 'Stock Quantity', 'Total Value']);
    
    foreach ($products as $product) {
        fputcsv($output, [
            $product['product_id'],
            $product['product_name'],
            $product['price'],
            $product['stock_quantity'],
            $product['price'] * $product['stock_quantity']
        ]);
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Inventory Report</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="?export=csv" class="btn btn-success">Export CSV</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock Quantity</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_value = 0;
                    foreach ($products as $product): 
                        $value = $product['price'] * $product['stock_quantity'];
                        $total_value += $value;
                    ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo $product['product_name']; ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                </span>
                            </td>
                            <td>₱<?php echo number_format($value, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                        <td colspan="4" class="text-end"><strong>Total Inventory Value:</strong></td>
                        <td><strong>₱<?php echo number_format($total_value, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <a href="../../dashboard/dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
