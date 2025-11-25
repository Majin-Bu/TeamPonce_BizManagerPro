<?php
require_once '../../config/database.php';
require_once '../../config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        // Query user
        $query = "SELECT user_id, username, password, role FROM users WHERE username = ?";
        $result = executeQuery($conn, $query, "s", [$username]);
        
        if ($result['success']) {
            $stmt = $result['stmt'];
            $stmt->store_result();
            
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $db_username, $db_password, $role);
                $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $db_password)) {
                    // Set session
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['role'] = $role;
                    
                    // Redirect to dashboard
                    header("Location: ../../dashboard/dashboard.php");
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
            $stmt->close();
        } else {
            $error = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">BizManagerPro Login</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <p class="text-center mt-3">
                            Don't have an account? <a href="register.php">Register here</a>
                        </p>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
