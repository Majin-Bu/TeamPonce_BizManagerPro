<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$order_id = $_GET['id'] ?? null;
$order = null;
$order_items = [];

if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Fetch order
$query = "SELECT o.order_id, o.client_id, o.order_date, o.total_amount, o.status, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM orders o 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE o.order_id = ? AND c.user_id = ?";
$result = executeQuery($conn, $query, "ii", [$order_id, $user_id]);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_result($oid, $client_id, $order_date, $total_amount, $status, $client_name);
    $stmt->fetch();
    $order = compact('oid', 'client_id', 'order_date', 'total_amount', 'status', 'client_name');
    $stmt->close();
}

// Fetch order items
$items_query = "SELECT oi.product_id, p.product_name, oi.quantity, oi.unit_price, oi.subtotal FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_id = ?";
$items_result = executeQuery($conn, $items_query, "i", [$order_id]);

if ($items_result['success']) {
    $stmt = $items_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($product_id, $product_name, $quantity, $unit_price, $subtotal);
    
    while ($stmt->fetch()) {
        $order_items[] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'subtotal' => $subtotal
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
    <title>View Order - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($order): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2>Order #<?php echo $order['oid']; ?></h2>
                </div>
                <div class="col-md-4 text-end">
                    <a href="edit.php?id=<?php echo $order['oid']; ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Client:</strong> <?php echo $order['client_name']; ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                    <p><strong>Status:</strong> <span class="badge <?php echo $order['status'] === 'Completed' ? 'bg-success' : ($order['status'] === 'Pending' ? 'bg-warning' : 'bg-danger'); ?>"><?php echo $order['status']; ?></span></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($order_items)): ?>
                        <p class="text-muted">No items in this order.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['product_name']; ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
