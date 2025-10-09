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
if (!isset($input['faculty_id']) || !isset($input['name']) || !isset($input['code'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Faculty ID, name, and code are required'
    ], 400);
}

$facultyId = (int)$input['faculty_id'];
$name = sanitizeInput($input['name']);
$code = sanitizeInput($input['code']);
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

try {
    // Check if faculty exists
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE id = ? AND is_active = 1");
    $stmt->execute([$facultyId]);
    if (!$stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Invalid faculty selected'
        ], 400);
    }

    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM courses WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Course code already exists'
        ], 400);
    }

    // Insert new course
    $stmt = $conn->prepare("INSERT INTO courses (faculty_id, name, code, description) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$facultyId, $name, $code, $description]);

    if ($result) {
        $courseId = $conn->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'add_course', "Added course: {$name}");
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Course added successfully',
            'id' => $courseId
        ]);
    } else {
        sendJSONResponse([
            'success' => false,
            'message' => 'Failed to add course'
        ], 500);
    }

} catch (PDOException $e) {
    error_log("Add course error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
