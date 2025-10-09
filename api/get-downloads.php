<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId === 0) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid user ID'
    ], 400);
}

try {
    $sql = "SELECT dh.*, p.title, p.file_path, p.year, p.semester, 
                   cu.name as unit_name, cu.code as unit_code,
                   c.name as course_name, f.name as faculty_name
            FROM download_history dh 
            JOIN past_papers p ON dh.paper_id = p.id 
            JOIN course_units cu ON p.course_unit_id = cu.id
            JOIN courses c ON cu.course_id = c.id
            JOIN faculties f ON c.faculty_id = f.id
            WHERE dh.user_id = ? 
            ORDER BY dh.downloaded_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $downloads = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'downloads' => $downloads
    ]);

} catch (PDOException $e) {
    error_log("Get downloads error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
