<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, name, code, description FROM faculties WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $faculties = $stmt->fetchAll();

    sendJSONResponse([
        'success' => true,
        'faculties' => $faculties
    ]);

} catch (PDOException $e) {
    error_log("Get faculties error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
