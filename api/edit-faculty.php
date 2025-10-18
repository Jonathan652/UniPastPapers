<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['name']) || !isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => 'ID, name, and code are required']);
    exit;
}

$id = (int)$input['id'];
$name = trim($input['name']);
$code = trim($input['code']);
$description = isset($input['description']) ? trim($input['description']) : '';

try {
    // Check if faculty exists
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        exit;
    }

    // Check if code already exists for another faculty
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE code = ? AND id != ?");
    $stmt->execute([$code, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Faculty code already exists']);
        exit;
    }

    // Update faculty
    $stmt = $conn->prepare("UPDATE faculties SET name = ?, code = ?, description = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$name, $code, $description, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Faculty updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update faculty']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
