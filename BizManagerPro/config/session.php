<?php
// Session Configuration
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Get the correct path to login page based on current location
        $path = '';
        if (strpos($_SERVER['PHP_SELF'], 'modules') !== false) {
            $path = '../users/login.php';
        } else if (strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) {
            $path = '../modules/users/login.php';
        } else {
            $path = 'modules/users/login.php';
        }
        header("Location: $path");
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: BizManagerPro/dashboard/dashboard.php");
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Logout function
function logout() {
    session_destroy();
    header("Location: ../../modules/users/login.php");
    exit();
}
?>
