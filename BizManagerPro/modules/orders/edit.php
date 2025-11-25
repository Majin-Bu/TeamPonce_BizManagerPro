<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$order_id = $_GET['id'] ?? null;
$error = '';
$success = '';
$order = null;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = sanitize($_POST['status'] ?? '');
    
    if (empty($new_status)) {
        $error = 'Status is required.';
    } else {
        $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
        $update_result = executeQuery($conn, $update_query, "si", [$new_status, $order_id]);
        
        if ($update_result['success']) {
            $success = 'Order updated successfully!';
            $order['status'] = $new_status;
        } else {
            $error = 'Failed to update order.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Edit Order #<?php echo $order['oid']; ?></h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($order): ?>
                    <form method="POST" class="card p-4">
                        <div class="mb-3">
                            <label class="form-label">Client</label>
                            <input type="text" class="form-control" value="<?php echo $order['client_name']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Order Date</label>
                            <input type="text" class="form-control" value="<?php echo $order['order_date']; ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-control" value="₱<?php echo number_format($order['total_amount'], 2); ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Completed" <?php echo $order['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo $order['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Order</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
