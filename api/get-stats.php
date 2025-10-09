<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Get statistics
    $stats = [];

    // Count faculties
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM faculties WHERE is_active = 1");
    $stmt->execute();
    $stats['faculties'] = $stmt->fetch()['count'];

    // Count courses
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE is_active = 1");
    $stmt->execute();
    $stats['courses'] = $stmt->fetch()['count'];

    // Count units
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_units WHERE is_active = 1");
    $stmt->execute();
    $stats['units'] = $stmt->fetch()['count'];

    // Count papers
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM past_papers WHERE is_active = 1");
    $stmt->execute();
    $stats['papers'] = $stmt->fetch()['count'];

    // Count users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stmt->execute();
    $stats['users'] = $stmt->fetch()['count'];

    // Count total downloads
    $stmt = $conn->prepare("SELECT SUM(downloads) as count FROM past_papers WHERE is_active = 1");
    $stmt->execute();
    $stats['downloads'] = $stmt->fetch()['count'] ?? 0;

    sendJSONResponse([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Get stats error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
