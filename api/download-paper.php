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
if (!isset($input['paper_id']) || !isset($input['user_id'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Paper ID and User ID are required'
    ], 400);
}

$paperId = (int)$input['paper_id'];
$userId = (int)$input['user_id'];

try {
    // Get paper info
    $stmt = $conn->prepare("SELECT * FROM past_papers WHERE id = ? AND is_active = 1");
    $stmt->execute([$paperId]);
    $paper = $stmt->fetch();

    if (!$paper) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Paper not found'
        ], 404);
    }

    // Check if file exists
    if (!file_exists($paper['file_path'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'File not found on server'
        ], 404);
    }

    // Record download in history
    $stmt = $conn->prepare("INSERT INTO download_history (user_id, paper_id, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $paperId,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    // Update download count
    $stmt = $conn->prepare("UPDATE past_papers SET downloads = downloads + 1 WHERE id = ?");
    $stmt->execute([$paperId]);

    // Log activity
    logActivity($userId, 'download', "Downloaded paper: {$paper['title']}");

    sendJSONResponse([
        'success' => true,
        'file_path' => $paper['file_path'],
        'file_name' => $paper['file_name']
    ]);

} catch (PDOException $e) {
    error_log("Download paper error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
