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

try {
    $conn->begin_transaction();

    // Delete order items for selected orders that belong to current user
    $delete_items_query = "DELETE oi FROM order_items oi 
        JOIN orders o ON oi.order_id = o.order_id 
        JOIN clients c ON o.client_id = c.client_id 
        WHERE c.user_id = ? AND oi.order_id IN ($placeholders)";
    $res_items = executeQuery($conn, $delete_items_query, $types_with_user, $params_with_user);
    if (!$res_items['success']) {
        throw new Exception($res_items['error'] ?? 'Failed to delete order items');
    }

    // Delete orders scoped to current user
    $delete_orders_query = "DELETE o FROM orders o 
        JOIN clients c ON o.client_id = c.client_id 
        WHERE c.user_id = ? AND o.order_id IN ($placeholders)";
    $res_orders = executeQuery($conn, $delete_orders_query, $types_with_user, $params_with_user);
    if (!$res_orders['success']) {
        throw new Exception($res_orders['error'] ?? 'Failed to delete orders');
    }

    $deleted = $res_orders['stmt']->affected_rows;
    $res_orders['stmt']->close();

    $conn->commit();
    header('Location: index.php?deleted=' . $deleted);
} catch (Exception $e) {
    $conn->rollback();
    $msg = urlencode($e->getMessage());
    header('Location: index.php?error=' . $msg);
}
exit();