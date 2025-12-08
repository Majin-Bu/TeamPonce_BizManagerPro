<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    header("Location: index.php");
    exit();
}

// Delete client
$query = "DELETE FROM clients WHERE client_id = ? AND user_id = ?";
$result = executeQuery($conn, $query, "ii", [$client_id, $user_id]);

if ($result['success']) {
    header("Location: index.php?success=Client deleted successfully");
} else {
    header("Location: index.php?error=Failed to delete client");
}
exit();
?>
