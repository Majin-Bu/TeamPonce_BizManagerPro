<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$client_id = $_GET['id'] ?? null;
$client = null;
$orders = [];

if (!$client_id) {
    header("Location: index.php");
    exit();
}

// Fetch client
$query = "SELECT client_id, user_id, CONCAT(first_name, ' ', last_name) AS name, contact_number, email, 
          CONCAT_WS(', ', CONCAT_WS(' ', street_number, street_name), barangay, city) AS address, created_at, updated_at 
          FROM clients WHERE client_id = ? AND user_id = ?";
$result = executeQuery($conn, $query, "ii", [$client_id, $user_id]);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_result($cid, $uid, $name, $contact, $email, $address, $created_at, $updated_at);
    $stmt->fetch();
    $client = compact('cid', 'name', 'contact', 'email', 'address', 'created_at');
    $stmt->close();
}

// Fetch client orders
$orders_query = "SELECT o.order_id, o.order_date, o.total_amount, o.status FROM orders o WHERE o.client_id = ? ORDER BY o.order_date DESC";
$orders_result = executeQuery($conn, $orders_query, "i", [$client_id]);

if ($orders_result['success']) {
    $stmt = $orders_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($order_id, $order_date, $total_amount, $status);
    
    while ($stmt->fetch()) {
        $orders[] = [
            'order_id' => $order_id,
            'order_date' => $order_date,
            'total_amount' => $total_amount,
            'status' => $status
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
    <title>View Client - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($client): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2><?php echo $client['name']; ?></h2>
                </div>
                <div class="col-md-4 text-end">
                    <a href="edit.php?id=<?php echo $client['cid']; ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Client Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Contact Number:</strong> <?php echo $client['contact']; ?></p>
                    <p><strong>Email:</strong> <?php echo $client['email']; ?></p>
                    <p><strong>Address:</strong> <?php echo $client['address']; ?></p>
                    <p><strong>Created:</strong> <?php echo $client['created_at']; ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Order History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p class="text-muted">No orders found for this client.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_id']; ?></td>
                                            <td><?php echo $order['order_date']; ?></td>
                                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><span class="badge bg-info"><?php echo $order['status']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
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
