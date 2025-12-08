<?php
require_once 'config/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: dashboard/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BizManagerPro - Enterprise Business Management Platform">
    <title>BizManagerPro - Business Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="fade-in">
                        <h1 class="display-4 fw-bold mb-4">
                            <span style="background: linear-gradient(135deg, #06b6d4 0%, #14b8a6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                BizManagerPro
                            </span>
                        </h1>
                        <p class="lead text-secondary mb-4">
                            Enterprise-grade business management platform designed for modern businesses. Manage clients, products, orders, and payments all in one place.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="modules/users/login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="modules/users/register.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </div>
                        <hr class="my-5">
                        <div class="alert alert-info border-0">
                            <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong><br>
                            <small>Username: <code>admin</code> | Password: <code>admin123</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="slide-in-right">
                        <div class="card border-0 shadow-lg">
                            <div class="card-body p-5">
                                <div class="row g-4 text-center">
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-users" style="font-size: 2rem; color: #06b6d4; margin-bottom: 1rem;"></i>
                                            <h5>Client Management</h5>
                                            <p class="text-muted small">Organize and track all your clients</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-box" style="font-size: 2rem; color: #10b981; margin-bottom: 1rem;"></i>
                                            <h5>Inventory</h5>
                                            <p class="text-muted small">Manage product stock efficiently</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-shopping-cart" style="font-size: 2rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                                            <h5>Orders</h5>
                                            <p class="text-muted small">Process and track orders seamlessly</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <i class="fas fa-credit-card" style="font-size: 2rem; color: #f43f5e; margin-bottom: 1rem;"></i>
                                            <h5>Payments</h5>
                                            <p class="text-muted small">Record and manage all transactions</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
