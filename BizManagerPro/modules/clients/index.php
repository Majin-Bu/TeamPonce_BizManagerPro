<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$clients = [];
$q = sanitize($_GET['q'] ?? '');

// Fetch clients for current user with optional search
$query = "SELECT client_id, user_id, CONCAT(first_name, ' ', last_name) AS name, contact_number, email, 
          CONCAT_WS(', ', CONCAT_WS(' ', street_number, street_name), barangay, city) AS address, created_at, updated_at 
          FROM clients WHERE user_id = ?";
$params = [$user_id];
$types = "i";
if (!empty($q)) {
    $like = "%$q%";
    $query .= " AND (CONCAT(first_name, ' ', last_name) LIKE ? OR contact_number LIKE ? OR email LIKE ? OR CONCAT_WS(', ', CONCAT_WS(' ', street_number, street_name), barangay, city) LIKE ?)";
    $params = [$user_id, $like, $like, $like, $like];
    $types = "issss";
}
$query .= " ORDER BY created_at DESC";
$result = executeQuery($conn, $query, $types, $params);

if ($result['success']) {
    $stmt = $result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($client_id, $uid, $name, $contact, $email, $address, $created_at, $updated_at);
    
    while ($stmt->fetch()) {
        $clients[] = [
            'client_id' => $client_id,
            'name' => $name,
            'contact_number' => $contact,
            'email' => $email,
            'address' => $address,
            'created_at' => $created_at
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
    <title>Clients - BizManagerPro</title>
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
                    <h1 class="fw-bold"><i class="fas fa-users"></i> Clients Management</h1>
                    <p class="text-secondary">Manage and organize all your business clients</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Client
                    </a>
                </div>
            </div>
            
            <?php if (empty($clients)): ?>
                <div class="alert alert-info border-0">
                    <i class="fas fa-info-circle"></i> No clients found. <a href="add.php" class="alert-link">Add one now</a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form class="d-flex" method="get" action="index.php">
                        <input type="text" class="form-control me-2" name="q" placeholder="Search clients..." value="<?php echo htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-user"></i> Name</th>
                                        <th><i class="fas fa-phone"></i> Contact</th>
                                        <th><i class="fas fa-envelope"></i> Email</th>
                                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                                        <th><i class="fas fa-cog"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="<?php echo $client['client_id']; ?>"></td>
                                            <td><strong>#<?php echo $client['client_id']; ?></strong></td>
                                            <td><?php echo $client['name']; ?></td>
                                            <td><?php echo $client['contact_number']; ?></td>
                                            <td><?php echo $client['email']; ?></td>
                                            <td><small><?php echo substr($client['address'], 0, 30) . '...'; ?></small></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $client['client_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $client['client_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $client['client_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
