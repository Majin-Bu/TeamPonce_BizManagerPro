<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Verify order belongs to user
$verify_query = "SELECT o.order_id FROM orders o 
                 JOIN clients c ON o.client_id = c.client_id 
                 WHERE o.order_id = ? AND c.user_id = ?";
$verify_result = executeQuery($conn, $verify_query, "ii", [$order_id, $user_id]);

if ($verify_result['success']) {
    $stmt = $verify_result['stmt'];
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Delete order items first
        $delete_items_query = "DELETE FROM order_items WHERE order_id = ?";
        executeQuery($conn, $delete_items_query, "i", [$order_id]);
        
        // Delete order
        $delete_query = "DELETE FROM orders WHERE order_id = ?";
        $delete_result = executeQuery($conn, $delete_query, "i", [$order_id]);
        
        if ($delete_result['success']) {
            header("Location: index.php?success=Order deleted successfully");
        } else {
            header("Location: index.php?error=Failed to delete order");
        }
    } else {
        header("Location: index.php?error=Order not found");
    }
    $stmt->close();
}
exit();
?>
