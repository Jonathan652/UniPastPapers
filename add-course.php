<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

$faculty_id = intval($input['faculty_id']);
$name = $conn->real_escape_string($input['name']);
$code = $conn->real_escape_string($input['code']);

$sql = "INSERT INTO courses (faculty_id, name, code) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $faculty_id, $name, $code);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Course added successfully',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add course']);
}

$stmt->close();
$conn->close();
?>