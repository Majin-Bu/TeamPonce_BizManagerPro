<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$export = $_GET['export'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build query
$query = "SELECT o.order_id, o.order_date, o.total_amount, o.status, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM orders o 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE c.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($start_date)) {
    $query .= " AND DATE(o.order_date) >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($end_date)) {
    $query .= " AND DATE(o.order_date) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$query .= " ORDER BY o.order_date DESC";

$result = executeQuery($conn, $query, $types, $params);
$orders = [];

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

// Export to CSV
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales-report-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Date', 'Client', 'Amount', 'Status']);
    
    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_id'],
            $order['order_date'],
            $order['client_name'],
            $order['total_amount'],
            $order['status']
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
    <title>Sales Report - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Sales Report</h2>
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
                        <a href="sales-report.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($orders as $order): 
                        $total += $order['total_amount'];
                    ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['client_name']; ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="badge <?php echo $order['status'] === 'Completed' ? 'bg-success' : 'bg-warning'; ?>"><?php echo $order['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                        <td colspan="3" class="text-end"><strong>Total Sales:</strong></td>
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
