<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, full_name, email, username, role, is_active, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'users' => $users
    ]);

} catch (PDOException $e) {
    error_log("Get users error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
