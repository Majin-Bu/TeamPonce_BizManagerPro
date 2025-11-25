<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$payment_id = $_GET['id'] ?? null;
$payment = null;

if (!$payment_id) {
    header("Location: index.php");
    exit();
}

// Fetch payment
$query = "SELECT p.payment_id, p.order_id, p.payment_date, p.amount_paid, p.payment_method, o.total_amount, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM payments p 
          JOIN orders o ON p.order_id = o.order_id 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE p.payment_id = ? AND c.user_id = ?";
$result = executeQuery($conn, $query, "ii", [$payment_id, $user_id]);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: index.php");
        exit();
    }
    
    $stmt->bind_result($pid, $order_id, $payment_date, $amount_paid, $payment_method, $order_total, $client_name);
    $stmt->fetch();
    $payment = compact('pid', 'order_id', 'payment_date', 'amount_paid', 'payment_method', 'order_total', 'client_name');
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <?php if ($payment): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2>Payment #<?php echo $payment['pid']; ?></h2>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <p><strong>Order ID:</strong> <?php echo $payment['order_id']; ?></p>
                    <p><strong>Client:</strong> <?php echo $payment['client_name']; ?></p>
                    <p><strong>Payment Date:</strong> <?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></p>
                    <p><strong>Amount Paid:</strong> ₱<?php echo number_format($payment['amount_paid'], 2); ?></p>
                    <p><strong>Order Total:</strong> ₱<?php echo number_format($payment['order_total'], 2); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo $payment['payment_method']; ?></p>
                    <p><strong>Remaining Balance:</strong> ₱<?php echo number_format($payment['order_total'] - $payment['amount_paid'], 2); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
