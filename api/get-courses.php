<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

$facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

try {
    if ($facultyId > 0) {
        $stmt = $conn->prepare("SELECT id, name, code, description FROM courses WHERE faculty_id = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$facultyId]);
    } else {
        $stmt = $conn->prepare("SELECT id, name, code, description FROM courses WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
    }
    
    $courses = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'courses' => $courses
    ]);

} catch (PDOException $e) {
    error_log("Get courses error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
