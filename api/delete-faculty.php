<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Faculty ID is required']);
    exit;
}

$id = (int)$input['id'];

try {
    // Check if faculty exists
    $stmt = $conn->prepare("SELECT name FROM faculties WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $faculty = $stmt->fetch();
    
    if (!$faculty) {
        echo json_encode(['success' => false, 'message' => 'Faculty not found']);
        exit;
    }

    // Check if faculty has courses
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE faculty_id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete faculty with existing courses']);
        exit;
    }

    // Soft delete faculty
    $stmt = $conn->prepare("UPDATE faculties SET is_active = 0, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Faculty deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete faculty']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
