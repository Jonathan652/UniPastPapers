<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

$course_id = intval($input['course_id']);
$name = $conn->real_escape_string($input['name']);
$code = $conn->real_escape_string($input['code']);

$sql = "INSERT INTO course_units (course_id, name, code) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $course_id, $name, $code);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Course unit added successfully',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add course unit']);
}

$stmt->close();
$conn->close();
?>