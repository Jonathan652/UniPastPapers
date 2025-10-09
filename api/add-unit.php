<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['course_id']) || !isset($input['name']) || !isset($input['code'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Course ID, name, and code are required'
    ], 400);
}

$courseId = (int)$input['course_id'];
$name = sanitizeInput($input['name']);
$code = sanitizeInput($input['code']);
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

try {
    // Check if course exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND is_active = 1");
    $stmt->execute([$courseId]);
    if (!$stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Invalid course selected'
        ], 400);
    }

    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM course_units WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Unit code already exists'
        ], 400);
    }

    // Insert new unit
    $stmt = $conn->prepare("INSERT INTO course_units (course_id, name, code, description) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$courseId, $name, $code, $description]);

    if ($result) {
        $unitId = $conn->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'add_unit', "Added unit: {$name}");
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Unit added successfully',
            'id' => $unitId
        ]);
    } else {
        sendJSONResponse([
            'success' => false,
            'message' => 'Failed to add unit'
        ], 500);
    }

} catch (PDOException $e) {
    error_log("Add unit error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
