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
if (!isset($input['name']) || !isset($input['code'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Name and code are required'
    ], 400);
}

$name = sanitizeInput($input['name']);
$code = sanitizeInput($input['code']);
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

try {
    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM faculties WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Faculty code already exists'
        ], 400);
    }

    // Insert new faculty
    $stmt = $conn->prepare("INSERT INTO faculties (name, code, description) VALUES (?, ?, ?)");
    $result = $stmt->execute([$name, $code, $description]);

    if ($result) {
        $facultyId = $conn->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'add_faculty', "Added faculty: {$name}");
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Faculty added successfully',
            'id' => $facultyId
        ]);
    } else {
        sendJSONResponse([
            'success' => false,
            'message' => 'Failed to add faculty'
        ], 500);
    }

} catch (PDOException $e) {
    error_log("Add faculty error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
