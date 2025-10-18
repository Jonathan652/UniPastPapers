<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

$id = (int)$input['id'];

try {
    // Check if course exists
    $stmt = $conn->prepare("SELECT name FROM courses WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }

    // Check if course has units
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_units WHERE course_id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete course with existing units']);
        exit;
    }

    // Soft delete course
    $stmt = $conn->prepare("UPDATE courses SET is_active = 0, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete course']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
