<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

// Get filter parameters
$faculty = isset($_GET['faculty']) ? (int)$_GET['faculty'] : 0;
$course = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$unit = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';

try {
    // Build query with joins
    $sql = "SELECT p.*, cu.name as unit_name, cu.code as unit_code, 
                   c.name as course_name, c.code as course_code,
                   f.name as faculty_name, f.code as faculty_code
            FROM past_papers p
            JOIN course_units cu ON p.course_unit_id = cu.id
            JOIN courses c ON cu.course_id = c.id
            JOIN faculties f ON c.faculty_id = f.id
            WHERE p.is_active = 1";

    $params = [];
    $types = "";

    if ($faculty > 0) {
        $sql .= " AND f.id = ?";
        $params[] = $faculty;
        $types .= "i";
    }

    if ($course > 0) {
        $sql .= " AND c.id = ?";
        $params[] = $course;
        $types .= "i";
    }

    if ($unit > 0) {
        $sql .= " AND p.course_unit_id = ?";
        $params[] = $unit;
        $types .= "i";
    }

    if ($year > 0) {
        $sql .= " AND p.year = ?";
        $params[] = $year;
        $types .= "i";
    }

    if (!empty($semester)) {
        $sql .= " AND p.semester = ?";
        $params[] = $semester;
        $types .= "s";
    }

    $sql .= " ORDER BY p.year DESC, p.created_at DESC";

    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }

    $papers = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'papers' => $papers
    ]);

} catch (PDOException $e) {
    error_log("Get papers error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
