<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

try {
    if ($courseId > 0) {
        $stmt = $conn->prepare("SELECT id, name, code, description FROM course_units WHERE course_id = ? AND is_active = 1 ORDER BY name");
        $stmt->execute([$courseId]);
    } else {
        $stmt = $conn->prepare("SELECT id, name, code, description FROM course_units WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
    }
    
    $units = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'units' => $units
    ]);

} catch (PDOException $e) {
    error_log("Get units error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
