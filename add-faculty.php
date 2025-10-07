<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

$name = $conn->real_escape_string($input['name']);
$code = $conn->real_escape_string($input['code']);
$description = $conn->real_escape_string($input['description']);

$sql = "INSERT INTO faculties (name, code, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $code, $description);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Faculty added successfully',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add faculty']);
}

$stmt->close();
$conn->close();
?>