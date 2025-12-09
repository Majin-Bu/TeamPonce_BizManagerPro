<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

$payment_id = intval($_GET['id'] ?? 0);
$payment = null;

if ($payment_id <= 0) {
    $error = 'Invalid payment ID.';
} else {
    $query = "SELECT p.payment_id, p.order_id, p.amount_paid, p.payment_method, o.total_amount, CONCAT(c.first_name, ' ', c.last_name) AS client_name
              FROM payments p
              JOIN orders o ON p.order_id = o.order_id
              JOIN clients c ON o.client_id = c.client_id
              WHERE p.payment_id = ? AND c.user_id = ?";
    $result = executeQuery($conn, $query, 'ii', [$payment_id, $user_id]);
    if ($result['success']) {
        $stmt = $result['stmt'];
        $stmt->bind_result($pid, $order_id, $amount_paid, $payment_method, $order_total, $client_name);
        if ($stmt->fetch()) {
            $payment = [
                'payment_id' => $pid,
                'order_id' => $order_id,
                'amount_paid' => $amount_paid,
                'payment_method' => $payment_method,
                'order_total' => $order_total,
                'client_name' => $client_name,
            ];
        }
        $stmt->close();
        if (!$payment) {
            $error = 'Payment not found.';
        }
    } else {
        $error = 'Failed to fetch payment.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment) {
    $new_amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $new_payment_method = sanitize($_POST['payment_method'] ?? '');

    if ($new_amount_paid <= 0 || empty($new_payment_method)) {
        $error = 'All fields are required.';
    } elseif ($new_amount_paid > floatval($payment['order_total'])) {
        $error = 'Payment amount cannot exceed order total.';
    } else {
        $update = executeQuery($conn, "UPDATE payments SET amount_paid = ?, payment_method = ? WHERE payment_id = ?", 'dsi', [$new_amount_paid, $new_payment_method, $payment['payment_id']]);
        if ($update['success']) {
            $success = 'Payment updated successfully!';
            // Refresh payment data
            $payment['amount_paid'] = $new_amount_paid;
            $payment['payment_method'] = $new_payment_method;
        } else {
            $error = 'Failed to update payment.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script>
        function setOrderTotal(total) {
            var input = document.getElementById('order_total');
            if (input) {
                input.value = total ? '₱' + parseFloat(total).toFixed(2) : '';
            }
        }
    </script>
    <style>
        /* keep inline minimal; using requested color */
    </style>
    <?php /* no comments per instructions */ ?>
    </head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4"><i class="fas fa-edit"></i> Edit Payment</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($payment): ?>
                <div class="card p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Payment ID</label>
                            <input type="text" class="form-control" value="#<?php echo $payment['payment_id']; ?>" disabled style="color:#1e293b;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Order</label>
                            <input type="text" class="form-control" value="Order #<?php echo $payment['order_id']; ?> - <?php echo $payment['client_name']; ?>" disabled style="color:#1e293b;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Order Total</label>
                            <input type="text" class="form-control" id="order_total" disabled style="color:#1e293b;" value="<?php echo '₱' . number_format($payment['order_total'], 2); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="amount_paid" class="form-label">Amount Paid *</label>
                            <input type="number" class="form-control" id="amount_paid" name="amount_paid" step="0.01" value="<?php echo htmlspecialchars($payment['amount_paid'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <?php
                                    $methods = ['Cash', 'Credit Card', 'Debit Card', 'Bank Transfer', 'Check'];
                                    $current = $payment['payment_method'];
                                    if (!in_array($current, $methods)) {
                                        echo '<option value="' . htmlspecialchars($current, ENT_QUOTES, 'UTF-8') . '" selected>' . htmlspecialchars($current, ENT_QUOTES, 'UTF-8') . '</option>';
                                    }
                                    foreach ($methods as $m) {
                                        $sel = ($current === $m) ? 'selected' : '';
                                        echo '<option value="' . $m . '" ' . $sel . '>' . $m . '</option>';
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Payment</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                    <div class="alert alert-warning">No payment to edit.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>setOrderTotal(<?php echo isset($payment['order_total']) ? json_encode($payment['order_total']) : 'null'; ?>);</script>
</body>
</html>
