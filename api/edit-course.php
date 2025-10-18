<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['faculty_id']) || !isset($input['name']) || !isset($input['code'])) {
    echo json_encode(['success' => false, 'message' => 'ID, faculty ID, name, and code are required']);
    exit;
}

$id = (int)$input['id'];
$facultyId = (int)$input['faculty_id'];
$name = trim($input['name']);
$code = trim($input['code']);
$description = isset($input['description']) ? trim($input['description']) : '';

try {
    // Check if course exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }

    // Check if faculty exists
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE id = ? AND is_active = 1");
    $stmt->execute([$facultyId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        exit;
    }

    // Check if code already exists for another course
    $stmt = $conn->prepare("SELECT id FROM courses WHERE code = ? AND id != ?");
    $stmt->execute([$code, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Course code already exists']);
        exit;
    }

    // Update course
    $stmt = $conn->prepare("UPDATE courses SET faculty_id = ?, name = ?, code = ?, description = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$facultyId, $name, $code, $description, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update course']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
