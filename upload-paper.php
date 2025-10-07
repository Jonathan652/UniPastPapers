<?php
require_once '../config/config.php';

if (!isset($_FILES['file']) || !isset($_POST['unit_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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

// Validate file type
$allowed = ['pdf'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']);
    exit;
}

// Create upload directory if not exists
$upload_dir = '../uploads/papers/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$new_filename = time() . '_' . $file_name;
$file_path = $upload_dir . $new_filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $file_path)) {
    // Insert into database
    $sql = "INSERT INTO past_papers (course_unit_id, title, year, semester, file_name, file_path, file_size, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ississii", $unit_id, $title, $year, $semester, $file_name, $file_path, $file_size, $uploaded_by);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Paper uploaded successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        unlink($file_path); // Delete file if database insert fails
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
}

$conn->close();
?>