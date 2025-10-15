<?php
require_once __DIR__ . '/../config.php';

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// Require user to be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$paperId = isset($_GET['paper_id']) ? (int)$_GET['paper_id'] : 0;
if ($paperId <= 0) {
    http_response_code(400);
    echo 'Invalid paper id';
    exit;
}

try {
    $stmt = $conn->prepare('SELECT * FROM past_papers WHERE id = ? AND is_active = 1');
    $stmt->execute([$paperId]);
    $paper = $stmt->fetch();

    if (!$paper) {
        http_response_code(404);
        echo 'Paper not found';
        exit;
    }

    // Resolve file path: handle relative paths stored in DB like 'uploads/papers/...'
    $filePath = $paper['file_path'];
    if ($filePath && $filePath[0] !== '/' && !preg_match('/^[A-Za-z]:\\\\/', $filePath)) {
        $filePath = realpath(__DIR__ . '/../' . $filePath) ?: __DIR__ . '/../' . $paper['file_path'];
    }

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found on server';
        exit;
    }

    // Record download (use session user)
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO download_history (user_id, paper_id, ip_address, user_agent, downloaded_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$userId, $paperId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

    // Update download count
    $stmt = $conn->prepare('UPDATE past_papers SET downloads = downloads + 1 WHERE id = ?');
    $stmt->execute([$paperId]);

    // Log activity
    logActivity($userId, 'download', "Downloaded paper: {$paper['title']}");

    // Stream file
    $fileName = $paper['file_name'] ?: basename($filePath);
    $fileSize = filesize($filePath);

    if (ob_get_level()) ob_end_clean();

    header('Content-Description: File Transfer');
    $mime = function_exists('mime_content_type') ? mime_content_type($filePath) : 'application/pdf';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    $chunkSize = 8192;
    $fh = fopen($filePath, 'rb');
    if ($fh === false) {
        http_response_code(500);
        echo 'Failed to open file';
        exit;
    }

    while (!feof($fh)) {
        echo fread($fh, $chunkSize);
        flush();
    }
    fclose($fh);
    exit;

} catch (PDOException $e) {
    error_log('Serve file error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Server error';
    exit;
}

?>


