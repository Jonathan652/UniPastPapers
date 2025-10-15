<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

// Debug session user_id
error_log('Session user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

// Set JSON header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

// Validate required fields
if (!isset($_POST['unit_id']) || !isset($_POST['title']) || !isset($_POST['year']) || !isset($_POST['semester'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'All fields are required'
    ], 400);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    sendJSONResponse([
        'success' => false,
        'message' => 'File upload is required'
    ], 400);
}

$unitId = (int)$_POST['unit_id'];
$title = sanitizeInput($_POST['title']);
$year = (int)$_POST['year'];
$semester = sanitizeInput($_POST['semester']);
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'User not authenticated'
    ], 401);
}

$uploadedBy = $_SESSION['user_id'];

$file = $_FILES['file'];
$fileName = basename($file['name']);
$fileSize = $file['size'];
$fileTmp = $file['tmp_name'];

// Validate file type
$allowedTypes = ['application/pdf'];
$fileType = $file['type'];

if (!in_array($fileType, $allowedTypes)) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Only PDF files are allowed'
    ], 400);
}

// Validate file size
if ($fileSize > MAX_FILE_SIZE) {
    sendJSONResponse([
        'success' => false,
        'message' => 'File size exceeds maximum allowed size'
    ], 400);
}

// Validate year
if ($year < 2000 || $year > date('Y') + 1) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid year'
    ], 400);
}

// Validate semester
$validSemesters = ['1', '2', '3', 'recess', 'supplementary'];
if (!in_array($semester, $validSemesters)) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid semester'
    ], 400);
}

try {
    error_log('Upload debug: unitId=' . $unitId . ', title=' . $title . ', year=' . $year . ', semester=' . $semester . ', uploadedBy=' . $uploadedBy);
    error_log('Upload debug: fileName=' . $fileName . ', fileSize=' . $fileSize . ', fileType=' . $fileType . ', fileTmp=' . $fileTmp);
    // Check if unit exists
    $stmt = $conn->prepare("SELECT id FROM course_units WHERE id = ? AND is_active = 1");
    $stmt->execute([$unitId]);
    if (!$stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Invalid unit selected'
        ], 400);
    }

    // Create upload directory if it doesn't exist (resolve relative to project root)
    $projectRoot = realpath(__DIR__ . '/..');
    $uploadDir = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!file_exists($uploadDir)) {
        $dirCreated = mkdir($uploadDir, 0755, true);
        error_log("Upload directory creation: " . ($dirCreated ? 'SUCCESS' : 'FAILED') . " for path: " . $uploadDir);
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload directory is not writable: " . $uploadDir);
        sendJSONResponse([
            'success' => false,
            'message' => 'Upload directory is not writable'
        ], 500);
    }

    // Generate unique filename
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $uniqueFileName = time() . '_' . uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $uniqueFileName;

    // Move uploaded file
    error_log("Attempting to move file from: " . $fileTmp . " to: " . $filePath);
    if (move_uploaded_file($fileTmp, $filePath)) {
        error_log("File moved successfully to: " . $filePath);
        // Insert paper record
        $stmt = $conn->prepare("INSERT INTO past_papers (course_unit_id, title, year, semester, file_name, file_path, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$unitId, $title, $year, $semester, $fileName, $filePath, $fileSize, $uploadedBy]);

        if ($result) {
            $paperId = $conn->lastInsertId();
            
            // Log activity
            logActivity($uploadedBy, 'upload_paper', "Uploaded paper: {$title}");
            
            sendJSONResponse([
                'success' => true,
                'message' => 'Paper uploaded successfully',
                'id' => $paperId
            ]);
        } else {
            // Delete file if database insert fails
            unlink($filePath);
            sendJSONResponse([
                'success' => false,
                'message' => 'Database error occurred'
            ], 500);
        }
    } else {
        error_log("File move failed from: " . $fileTmp . " to: " . $filePath);
        sendJSONResponse([
            'success' => false,
            'message' => 'File upload failed - could not move file to upload directory'
        ], 500);
    }

} catch (PDOException $e) {
    error_log("Upload paper error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>
