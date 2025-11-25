<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$export = $_GET['export'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build query
$query = "SELECT p.payment_id, p.order_id, p.payment_date, p.amount_paid, p.payment_method, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM payments p 
          JOIN orders o ON p.order_id = o.order_id 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE c.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($start_date)) {
    $query .= " AND DATE(p.payment_date) >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($end_date)) {
    $query .= " AND DATE(p.payment_date) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$query .= " ORDER BY p.payment_date DESC";

$result = executeQuery($conn, $query, $types, $params);
$payments = [];

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($payment_id, $order_id, $payment_date, $amount_paid, $payment_method, $client_name);
    
    while ($stmt->fetch()) {
        $payments[] = [
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'payment_date' => $payment_date,
            'amount_paid' => $amount_paid,
            'payment_method' => $payment_method,
            'client_name' => $client_name
        ];
    }
    $stmt->close();
}

// Export to CSV
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payment-report-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Payment ID', 'Order ID', 'Date', 'Client', 'Amount', 'Method']);
    
    foreach ($payments as $payment) {
        fputcsv($output, [
            $payment['payment_id'],
            $payment['order_id'],
            $payment['payment_date'],
            $payment['client_name'],
            $payment['amount_paid'],
            $payment['payment_method']
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
    <title>Payment Report - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Payment Report</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="?export=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success">Export CSV</a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="payment-report.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($payments as $payment): 
                        $total += $payment['amount_paid'];
                    ?>
                        <tr>
                            <td><?php echo $payment['payment_id']; ?></td>
                            <td><?php echo $payment['order_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo $payment['client_name']; ?></td>
                            <td>₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                            <td><?php echo $payment['payment_method']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                        <td colspan="4" class="text-end"><strong>Total Payments:</strong></td>
                        <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                        <td></td>
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
