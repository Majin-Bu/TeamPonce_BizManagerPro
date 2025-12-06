<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireLogin();

$user_id = getCurrentUserId();

// Get statistics
$stats = [
    'total_users' => 0,
    'total_clients' => 0,
    'total_products' => 0,
    'total_orders' => 0,
    'total_sales' => 0,
    'pending_orders' => 0,
    'completed_orders' => 0,
    'cancelled_orders' => 0,
    'total_payments' => 0
];

// Total users (admin only)
if (isAdmin()) {
    $user_query = "SELECT COUNT(*) as count FROM users";
    $user_result = executeQuery($conn, $user_query, "", []);
    if ($user_result['success']) {
        $stmt = $user_result['stmt'];
        $stmt->bind_result($count);
        $stmt->fetch();
        $stats['total_users'] = $count;
        $stmt->close();
    }
}

// Total clients
$client_query = "SELECT COUNT(*) as count FROM clients WHERE user_id = ?";
$client_result = executeQuery($conn, $client_query, "i", [$user_id]);
if ($client_result['success']) {
    $stmt = $client_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['total_clients'] = $count;
    $stmt->close();
}

// Total products
$product_query = "SELECT COUNT(*) as count FROM products WHERE user_id = ?";
$product_result = executeQuery($conn, $product_query, "i", [$user_id]);
if ($product_result['success']) {
    $stmt = $product_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['total_products'] = $count;
    $stmt->close();
}

// Total orders
$order_query = "SELECT COUNT(*) as count FROM orders o JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ?";
$order_result = executeQuery($conn, $order_query, "i", [$user_id]);
if ($order_result['success']) {
    $stmt = $order_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['total_orders'] = $count;
    $stmt->close();
}

// Total sales
$sales_query = "SELECT SUM(total_amount) as total FROM orders o JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ?";
$sales_result = executeQuery($conn, $sales_query, "i", [$user_id]);
if ($sales_result['success']) {
    $stmt = $sales_result['stmt'];
    $stmt->bind_result($total);
    $stmt->fetch();
    $stats['total_sales'] = $total ?? 0;
    $stmt->close();
}

// Pending orders
$pending_query = "SELECT COUNT(*) as count FROM orders o JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ? AND o.status = 'Pending'";
$pending_result = executeQuery($conn, $pending_query, "i", [$user_id]);
if ($pending_result['success']) {
    $stmt = $pending_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['pending_orders'] = $count;
    $stmt->close();
}

// Completed orders
$completed_query = "SELECT COUNT(*) as count FROM orders o JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ? AND o.status = 'Completed'";
$completed_result = executeQuery($conn, $completed_query, "i", [$user_id]);
if ($completed_result['success']) {
    $stmt = $completed_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['completed_orders'] = $count;
    $stmt->close();
}

// Cancelled orders
$cancelled_query = "SELECT COUNT(*) as count FROM orders o JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ? AND o.status = 'Cancelled'";
$cancelled_result = executeQuery($conn, $cancelled_query, "i", [$user_id]);
if ($cancelled_result['success']) {
    $stmt = $cancelled_result['stmt'];
    $stmt->bind_result($count);
    $stmt->fetch();
    $stats['cancelled_orders'] = $count;
    $stmt->close();
}

// Total payments
$payment_query = "SELECT SUM(amount_paid) as total FROM payments p JOIN orders o ON p.order_id = o.order_id JOIN clients c ON o.client_id = c.client_id WHERE c.user_id = ?";
$payment_result = executeQuery($conn, $payment_query, "i", [$user_id]);
if ($payment_result['success']) {
    $stmt = $payment_result['stmt'];
    $stmt->bind_result($total);
    $stmt->fetch();
    $stats['total_payments'] = $total ?? 0;
    $stmt->close();
}

// Get recent orders
$recent_orders = [];
$recent_query = "SELECT o.order_id, o.order_date, o.total_amount, o.status, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM orders o 
                 JOIN clients c ON o.client_id = c.client_id 
                 WHERE c.user_id = ? 
                 ORDER BY o.order_date DESC LIMIT 5";
$recent_result = executeQuery($conn, $recent_query, "i", [$user_id]);
if ($recent_result['success']) {
    $stmt = $recent_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($order_id, $order_date, $total_amount, $status, $client_name);
    
    while ($stmt->fetch()) {
        $recent_orders[] = [
            'order_id' => $order_id,
            'order_date' => $order_date,
            'total_amount' => $total_amount,
            'status' => $status,
            'client_name' => $client_name
        ];
    }
    $stmt->close();
}

// Get top products
$top_products = [];
$top_query = "SELECT p.product_id, p.product_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_sales FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              WHERE p.user_id = ? 
              GROUP BY p.product_id 
              ORDER BY total_qty DESC LIMIT 5";
$top_result = executeQuery($conn, $top_query, "i", [$user_id]);
if ($top_result['success']) {
    $stmt = $top_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($product_id, $product_name, $total_qty, $total_sales);
    
    while ($stmt->fetch()) {
        $top_products[] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'total_qty' => $total_qty,
            'total_sales' => $total_sales
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
    <title>Dashboard - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.js">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <main class="container-fluid">
        <div class="container">
            <div class="row mb-5 mt-4">
                <div class="col-md-8">
                    <h1 class="fw-bold">Dashboard</h1>
                    <p class="text-secondary">Welcome back! Here's your business overview.</p>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-5">
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-users stat-icon"></i>
                        <h5>Total Clients</h5>
                        <h2><?php echo $stats['total_clients']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-box stat-icon"></i>
                        <h5>Total Products</h5>
                        <h2><?php echo $stats['total_products']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-shopping-cart stat-icon"></i>
                        <h5>Total Orders</h5>
                        <h2><?php echo $stats['total_orders']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-peso-sign stat-icon"></i>
                        <h5>Total Sales</h5>
                        <h2>₱<?php echo number_format($stats['total_sales'], 0); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- Order Status Cards -->
            <div class="row mb-5">
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-clock stat-icon"></i>
                        <h5>Pending Orders</h5>
                        <h2><?php echo $stats['pending_orders']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <h5>Completed Orders</h5>
                        <h2><?php echo $stats['completed_orders']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-ban stat-icon"></i>
                        <h5>Cancelled Orders</h5>
                        <h2><?php echo $stats['cancelled_orders']; ?></h2>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stat-card">
                        <i class="fas fa-credit-card stat-icon"></i>
                        <h5>Total Payments</h5>
                        <h2>₱<?php echo number_format($stats['total_payments'], 0); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders and Top Products -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_orders)): ?>
                                <p class="text-muted text-center py-4">No orders yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Client</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                                    <td><?php echo $order['client_name']; ?></td>
                                                    <td>₱<?php echo number_format($order['total_amount'], 0); ?></td>
                                                    <td><span class="badge <?php echo $order['status'] === 'Completed' ? 'bg-success' : 'bg-warning'; ?>"><i class="fas fa-<?php echo $order['status'] === 'Completed' ? 'check' : 'hourglass'; ?>"></i> <?php echo $order['status']; ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-star"></i> Top Products</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_products)): ?>
                                <p class="text-muted text-center py-4">No products sold yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty Sold</th>
                                                <th>Sales</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_products as $product): ?>
                                                <tr>
                                                    <td><?php echo $product['product_name']; ?></td>
                                                    <td><span class="badge bg-info"><?php echo $product['total_qty']; ?></span></td>
                                                    <td>₱<?php echo number_format($product['total_sales'], 0); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reports Section -->
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Generate Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <a href="reports/sales-report.php" class="btn btn-primary w-100">
                                        <i class="fas fa-chart-bar"></i> Sales Report
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="reports/inventory-report.php" class="btn btn-success w-100">
                                        <i class="fas fa-warehouse"></i> Inventory Report
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <a href="reports/payment-report.php" class="btn btn-info w-100">
                                        <i class="fas fa-receipt"></i> Payment Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
