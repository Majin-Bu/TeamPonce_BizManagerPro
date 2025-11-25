<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    header("Location: index.php");
    exit();
}

// Verify payment belongs to user
$verify_query = "SELECT p.payment_id FROM payments p 
                 JOIN orders o ON p.order_id = o.order_id 
                 JOIN clients c ON o.client_id = c.client_id 
                 WHERE p.payment_id = ? AND c.user_id = ?";
$verify_result = executeQuery($conn, $verify_query, "ii", [$payment_id, $user_id]);

if ($verify_result['success']) {
    $stmt = $verify_result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Delete payment
        $delete_query = "DELETE FROM payments WHERE payment_id = ?";
        $delete_result = executeQuery($conn, $delete_query, "i", [$payment_id]);
        
        if ($delete_result['success']) {
            header("Location: index.php?success=Payment deleted successfully");
        } else {
            header("Location: index.php?error=Failed to delete payment");
        }
    } else {
        header("Location: index.php?error=Payment not found");
    }
    $stmt->close();
}
exit();
?>
