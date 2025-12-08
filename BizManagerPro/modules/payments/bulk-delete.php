<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$ids = $_POST['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    header('Location: index.php?error=No items selected');
    exit();
}

$ids = array_values(array_unique(array_map('intval', $ids)));
if (empty($ids)) {
    header('Location: index.php?error=No valid items selected');
    exit();
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types_with_user = 'i' . str_repeat('i', count($ids));
$params_with_user = array_merge([$user_id], $ids);

// Delete payments that belong to orders of the current user
$query = "DELETE p FROM payments p 
    JOIN orders o ON p.order_id = o.order_id 
    JOIN clients c ON o.client_id = c.client_id 
    WHERE c.user_id = ? AND p.payment_id IN ($placeholders)";
$result = executeQuery($conn, $query, $types_with_user, $params_with_user);

$deleted = 0;
if ($result['success']) {
    $stmt = $result['stmt'];
    $deleted = $stmt->affected_rows;
    $stmt->close();
    header('Location: index.php?deleted=' . $deleted);
} else {
    $error = urlencode($result['error'] ?? 'Failed to delete payments');
    header('Location: index.php?error=' . $error);
}
exit();