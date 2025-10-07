<?php
require_once '../config/config.php';

// Count faculties
$result = $conn->query("SELECT COUNT(*) as count FROM faculties");
$faculties_count = $result->fetch_assoc()['count'];

// Count courses
$result = $conn->query("SELECT COUNT(*) as count FROM courses");
$courses_count = $result->fetch_assoc()['count'];

// Count course units
$result = $conn->query("SELECT COUNT(*) as count FROM course_units");
$units_count = $result->fetch_assoc()['count'];

// Count papers
$result = $conn->query("SELECT COUNT(*) as count FROM past_papers");
$papers_count = $result->fetch_assoc()['count'];

// Count users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'");
$users_count = $result->fetch_assoc()['count'];

// Total downloads
$result = $conn->query("SELECT SUM(downloads) as total FROM past_papers");
$downloads_count = $result->fetch_assoc()['total'] ?: 0;

echo json_encode([
    'success' => true,
    'stats' => [
        'faculties' => $faculties_count,
        'courses' => $courses_count,
        'units' => $units_count,
        'papers' => $papers_count,
        'users' => $users_count,
        'downloads' => $downloads_count
    ]
]);

$conn->close();
?>