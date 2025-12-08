<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$orders = [];
$q = sanitize($_GET['q'] ?? '');

// Fetch all orders for clients of current user with optional search
$query = "SELECT o.order_id, o.order_date, o.total_amount, o.status, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM orders o 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE c.user_id = ?";
$params = [$user_id];
$types = "i";
if (!empty($q)) {
    $like = "%$q%";
    $query .= " AND (CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR o.status LIKE ? OR o.order_id LIKE ?)";
    $params = [$user_id, $like, $like, $like];
    $types = "isss";
}
$query .= " ORDER BY o.order_date DESC";
$result = executeQuery($conn, $query, $types, $params);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($order_id, $order_date, $total_amount, $status, $client_name);
    
    while ($stmt->fetch()) {
        $orders[] = [
            'order_id' => $order_id,
            'order_date' => $order_date,
            'total_amount' => $total_amount,
            'status' => $status,
            'client_name' => $client_name
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
    <title>Orders - BizManagerPro</title>
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
                    <h1 class="fw-bold"><i class="fas fa-shopping-cart"></i> Orders Management</h1>
                    <p class="text-secondary">Track and manage all customer orders</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Order
                    </a>
                </div>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="alert alert-info border-0">
                    <i class="fas fa-info-circle"></i> No orders found. <a href="add.php" class="alert-link">Create one now</a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form class="d-flex" method="get" action="index.php">
                        <input type="text" class="form-control me-2" name="q" placeholder="Search orders..." value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <th><i class="fas fa-hashtag"></i> Order ID</th>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-calendar"></i> Order Date</th>
                                        <th><i class="fas fa-peso-sign"></i> Total Amount</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="<?php echo $order['order_id']; ?>"></td>
                                            <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                            <td><?php echo $order['client_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td><strong>₱<?php echo number_format($order['total_amount'], 0); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $order['status'] === 'Completed' ? 'bg-success' : ($order['status'] === 'Pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <i class="fas fa-<?php echo $order['status'] === 'Completed' ? 'check' : ($order['status'] === 'Pending' ? 'hourglass' : 'times'); ?>"></i> <?php echo $order['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
