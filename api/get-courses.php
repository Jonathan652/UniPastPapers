<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

$facultyId = isset($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : 0;

try {
    if ($facultyId > 0) {
        $stmt = $conn->prepare("SELECT c.id, c.name, c.code, c.description, f.name as faculty_name 
                               FROM courses c 
                               JOIN faculties f ON c.faculty_id = f.id 
                               WHERE c.faculty_id = ? AND c.is_active = 1 
                               ORDER BY c.name");
        $stmt->execute([$facultyId]);
    } else {
        $stmt = $conn->prepare("SELECT c.id, c.name, c.code, c.description, f.name as faculty_name 
                               FROM courses c 
                               JOIN faculties f ON c.faculty_id = f.id 
                               WHERE c.is_active = 1 
                               ORDER BY f.name, c.name");
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
