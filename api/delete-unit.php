<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unit ID is required']);
    exit;
}

$id = (int)$input['id'];

try {
    // Check if unit exists
    $stmt = $conn->prepare("SELECT name FROM course_units WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $unit = $stmt->fetch();
    
    if (!$unit) {
        echo json_encode(['success' => false, 'message' => 'Unit not found']);
        exit;
    }

    // Check if unit has papers
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM past_papers WHERE course_unit_id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete unit with existing papers']);
        exit;
    }

    // Soft delete unit
    $stmt = $conn->prepare("UPDATE course_units SET is_active = 0, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Unit deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete unit']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
