<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$payments = [];
$filter_method = $_GET['method'] ?? '';
$filter_date = $_GET['date'] ?? '';
$q = sanitize($_GET['q'] ?? '');

// Build query with filters
$query = "SELECT p.payment_id, p.order_id, p.payment_date, p.amount_paid, p.payment_method, o.total_amount, CONCAT(c.first_name, ' ', c.last_name) AS client_name FROM payments p 
          JOIN orders o ON p.order_id = o.order_id 
          JOIN clients c ON o.client_id = c.client_id 
          WHERE c.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($filter_method)) {
    $query .= " AND p.payment_method = ?";
    $params[] = $filter_method;
    $types .= "s";
}

if (!empty($filter_date)) {
    $query .= " AND DATE(p.payment_date) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if (!empty($q)) {
    $like = "%$q%";
    $query .= " AND (p.payment_id LIKE ? OR o.order_id LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR p.payment_method LIKE ? OR p.payment_date LIKE ? OR CAST(p.amount_paid AS CHAR) LIKE ?)";
    $params[] = $like; // payment_id
    $params[] = $like; // order_id
    $params[] = $like; // client name
    $params[] = $like; // method
    $params[] = $like; // date
    $params[] = $like; // amount
    $types .= "ssssss";
}

$query .= " ORDER BY p.payment_date DESC";

$result = executeQuery($conn, $query, $types, $params);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($payment_id, $order_id, $payment_date, $amount_paid, $payment_method, $order_total, $client_name);
    
    while ($stmt->fetch()) {
        $payments[] = [
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'payment_date' => $payment_date,
            'amount_paid' => $amount_paid,
            'payment_method' => $payment_method,
            'order_total' => $order_total,
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
    <title>Payments - BizManagerPro</title>
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
                    <h1 class="fw-bold"><i class="fas fa-credit-card"></i> Payments Management</h1>
                    <p class="text-secondary">Record and track all payment transactions</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Record Payment
                    </a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Payments</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" method="get" action="index.php">
                        <div class="col-md-4">
                            <label for="method" class="form-label">Payment Method</label>
                            <select id="method" name="method" class="form-select" value="<?php echo htmlspecialchars($filter_method, ENT_QUOTES, 'UTF-8'); ?>">
                                <option value="">All Methods</option>
                                <option value="Cash" <?php echo $filter_method === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="Card" <?php echo $filter_method === 'Card' ? 'selected' : ''; ?>>Card</option>
                                <option value="Online" <?php echo $filter_method === 'Online' ? 'selected' : ''; ?>>Online</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date" class="form-label">Payment Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filter_date, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (empty($payments)): ?>
                <div class="alert alert-info border-0">
                    <i class="fas fa-info-circle"></i> No payments found. <a href="add.php" class="alert-link">Record one now</a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form class="d-flex" method="get" action="index.php">
                        <input type="hidden" name="method" value="<?php echo htmlspecialchars($filter_method, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($filter_date, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="text" class="form-control me-2" name="q" placeholder="Search payments..." value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <th><i class="fas fa-hashtag"></i> Payment ID</th>
                                        <th><i class="fas fa-shopping-cart"></i> Order ID</th>
                                        <th><i class="fas fa-user"></i> Client</th>
                                        <th><i class="fas fa-calendar"></i> Payment Date</th>
                                        <th><i class="fas fa-peso-sign"></i> Amount Paid</th>
                                        <th><i class="fas fa-wallet"></i> Method</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="<?php echo $payment['payment_id']; ?>"></td>
                                            <td><strong>#<?php echo $payment['payment_id']; ?></strong></td>
                                            <td><?php echo $payment['order_id']; ?></td>
                                            <td><?php echo $payment['client_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            <td><strong>₱<?php echo number_format($payment['amount_paid'], 0); ?></strong></td>
                                            <td><?php echo $payment['payment_method']; ?></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $payment['payment_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $payment['payment_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $payment['payment_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
