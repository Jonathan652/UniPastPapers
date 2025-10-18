<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['course_id']) || !isset($input['name']) || !isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => 'ID, course ID, name, and code are required']);
    exit;
}

$id = (int)$input['id'];
$courseId = (int)$input['course_id'];
$name = trim($input['name']);
$code = trim($input['code']);
$description = isset($input['description']) ? trim($input['description']) : '';

try {
    // Check if unit exists
    $stmt = $conn->prepare("SELECT id FROM course_units WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unit not found']);
        exit;
    }

    // Check if course exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND is_active = 1");
    $stmt->execute([$courseId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }

    // Check if code already exists for another unit
    $stmt = $conn->prepare("SELECT id FROM course_units WHERE code = ? AND id != ?");
    $stmt->execute([$code, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unit code already exists']);
        exit;
    }

    // Update unit
    $stmt = $conn->prepare("UPDATE course_units SET course_id = ?, name = ?, code = ?, description = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$courseId, $name, $code, $description, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Unit updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update unit']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
