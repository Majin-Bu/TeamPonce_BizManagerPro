<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Delete product
$query = "DELETE FROM products WHERE product_id = ? AND user_id = ?";
$result = executeQuery($conn, $query, "ii", [$product_id, $user_id]);

if ($result['success']) {
    header("Location: index.php?success=Product deleted successfully");
} else {
    header("Location: index.php?error=Failed to delete product");
}
exit();
?>
