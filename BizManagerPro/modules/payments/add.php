<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';
$orders = [];

// Fetch pending orders
$order_query = "SELECT o.order_id, o.total_amount, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM orders o 
                JOIN clients c ON o.client_id = c.client_id 
                WHERE c.user_id = ? AND o.status = 'Pending' 
                ORDER BY o.order_date DESC";
$order_result = executeQuery($conn, $order_query, "i", [$user_id]);

if ($order_result['success']) {
    $stmt = $order_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($order_id, $total_amount, $client_name);
    
    while ($stmt->fetch()) {
        $orders[] = ['order_id' => $order_id, 'total_amount' => $total_amount, 'client_name' => $client_name];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    
    if ($order_id <= 0 || $amount_paid <= 0 || empty($payment_method)) {
        $error = 'All fields are required.';
    } else {
        // Verify order belongs to user
        $verify_query = "SELECT o.total_amount FROM orders o 
                        JOIN clients c ON o.client_id = c.client_id 
                        WHERE o.order_id = ? AND c.user_id = ?";
        $verify_result = executeQuery($conn, $verify_query, "ii", [$order_id, $user_id]);
        
        if ($verify_result['success']) {
            $stmt = $verify_result['stmt'];
            $stmt->bind_result($order_total);
            $stmt->fetch();
            $stmt->close();
            
            if ($amount_paid > $order_total) {
                $error = 'Payment amount cannot exceed order total.';
            } else {
                // Insert payment
                $insert_query = "INSERT INTO payments (order_id, amount_paid, payment_method) VALUES (?, ?, ?)";
                $insert_result = executeQuery($conn, $insert_query, "ids", [$order_id, $amount_paid, $payment_method]);
                
                if ($insert_result['success']) {
                    $success = 'Payment recorded successfully!';
                    $order_id = $amount_paid = $payment_method = '';
                } else {
                    $error = 'Failed to record payment.';
                }
            }
        } else {
            $error = 'Order not found.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Record Payment</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="card p-4">
                    <div class="mb-3">
                        <label for="order_id" class="form-label">Select Order *</label>
                        <select class="form-control" id="order_id" name="order_id" required onchange="updateOrderTotal()">
                            <option value="">-- Select an Order --</option>
                            <?php foreach ($orders as $order): ?>
                                <option value="<?php echo $order['order_id']; ?>" data-total="<?php echo $order['total_amount']; ?>">
                                    Order #<?php echo $order['order_id']; ?> - <?php echo $order['client_name']; ?> (₱<?php echo number_format($order['total_amount'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order Total</label>
                        <input type="text" class="form-control" id="order_total" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid *</label>
                        <input type="number" class="form-control" id="amount_paid" name="amount_paid" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">-- Select Method --</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateOrderTotal() {
            const select = document.getElementById('order_id');
            const totalInput = document.getElementById('order_total');
            const selectedOption = select.options[select.selectedIndex];
            const total = selectedOption.dataset.total;
            totalInput.value = total ? '$' + parseFloat(total).toFixed(2) : '';
        }
    </script>
</body>
</html>
