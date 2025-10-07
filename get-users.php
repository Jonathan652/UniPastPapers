<?php
require_once '../config/config.php';

$sql = "SELECT id, full_name, email, username, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode([
    'success' => true,
    'users' => $users
]);

$conn->close();
?>