<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';
$first_name = '';
$last_name = '';
$contact = '';
$email = '';
$street_number = '';
$street_name = '';
$barangay = '';
$city = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $contact = sanitize($_POST['contact_number'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $street_number = sanitize($_POST['street_number'] ?? '');
    $street_name = sanitize($_POST['street_name'] ?? '');
    $barangay = sanitize($_POST['barangay'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First and last name are required.';
    } else {
        $query = "INSERT INTO clients (user_id, first_name, last_name, contact_number, email, street_number, street_name, barangay, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = executeQuery($conn, $query, "issssssss", [$user_id, $first_name, $last_name, $contact, $email, $street_number, $street_name, $barangay, $city]);
        
        if ($result['success']) {
            $success = 'Client added successfully!';
            $first_name = $last_name = $contact = $email = $street_number = $street_name = $barangay = $city = '';
        } else {
            $error = 'Failed to add client.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4">Add New Client</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="card p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $first_name; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $last_name; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" value="<?php echo $contact; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="street_number" class="form-label">Street No.</label>
                            <input type="text" class="form-control" id="street_number" name="street_number" value="<?php echo $street_number; ?>">
                        </div>
                        <div class="col-md-9">
                            <label for="street_name" class="form-label">Street Name</label>
                            <input type="text" class="form-control" id="street_name" name="street_name" value="<?php echo $street_name; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" value="<?php echo $barangay; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo $city; ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Add Client</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
