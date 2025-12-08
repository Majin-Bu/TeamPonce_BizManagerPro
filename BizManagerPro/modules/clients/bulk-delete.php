<?php

//bulk delete


require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$ids = $_POST['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    header('Location: index.php?error=No items selected');
    exit();
}

// Normalize IDs to integers
$ids = array_values(array_unique(array_map('intval', $ids)));
if (empty($ids)) {
    header('Location: index.php?error=No valid items selected');
    exit();
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids)) . 'i';
$params = array_merge($ids, [$user_id]);

$query = "DELETE FROM clients WHERE client_id IN ($placeholders) AND user_id = ?";
$result = executeQuery($conn, $query, $types, $params);

$deleted = 0;
if ($result['success']) {
    $stmt = $result['stmt'];
    $deleted = $stmt->affected_rows;
    $stmt->close();
    header('Location: index.php?deleted=' . $deleted);
} else {
    $error = urlencode($result['error'] ?? 'Failed to delete clients');
    header('Location: index.php?error=' . $error);
}
exit();