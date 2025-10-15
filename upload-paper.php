<?php
// Prevent any output before JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../config/config.php';

// Check if required fields are present
if (!isset($_FILES['file']) || !isset($_POST['unit_id']) || !isset($_POST['title']) || !isset($_POST['year']) || !isset($_POST['semester']) || !isset($_POST['uploaded_by'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

$unit_id = intval($_POST['unit_id']);
$title = $conn->real_escape_string($_POST['title']);
$year = intval($_POST['year']);
$semester = $conn->real_escape_string($_POST['semester']);
$uploaded_by = intval($_POST['uploaded_by']);

$file = $_FILES['file'];
$file_name = basename($file['name']);
$file_size = $file['size'];
$file_tmp = $file['tmp_name'];

// Check for file upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
    exit;
}

// Validate file type
$allowed = ['pdf'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
    exit;
}

// Validate file size (e.g., max 10MB)
if ($file_size > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
    exit;
}

// Create upload directory if not exists
$upload_dir = '../uploads/papers/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $file_name);
$file_path = $upload_dir . $new_filename;
$relative_path = 'uploads/papers/' . $new_filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $file_path)) {
    // Insert into database
    $sql = "INSERT INTO past_papers (course_unit_id, title, year, semester, file_name, file_path, file_size, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        unlink($file_path);
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("ississii", $unit_id, $title, $year, $semester, $file_name, $relative_path, $file_size, $uploaded_by);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Paper uploaded successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        unlink($file_path); // Delete file if database insert fails
        echo json_encode(['success' => false, 'message' => 'Database insert error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}

$conn->close();
?>