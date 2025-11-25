<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bizmanagerpro');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to sanitize input
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to execute prepared statements
function executeQuery($conn, $query, $types = "", $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ['success' => false, 'error' => $conn->error];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if ($stmt->execute()) {
        return ['success' => true, 'stmt' => $stmt];
    } else {
        return ['success' => false, 'error' => $stmt->error];
    }
}
?>
