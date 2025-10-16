<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

try {
    if ($courseId > 0) {
        $stmt = $conn->prepare("SELECT cu.id, cu.name, cu.code, cu.description, c.name as course_name, c.code as course_code 
                               FROM course_units cu 
                               JOIN courses c ON cu.course_id = c.id 
                               WHERE cu.course_id = ? AND cu.is_active = 1 ORDER BY cu.name");
        $stmt->execute([$courseId]);
    } else {
        $stmt = $conn->prepare("SELECT cu.id, cu.name, cu.code, cu.description, c.name as course_name, c.code as course_code 
                               FROM course_units cu 
                               JOIN courses c ON cu.course_id = c.id 
                               WHERE cu.is_active = 1 ORDER BY c.name, cu.name");
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
