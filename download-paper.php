<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

$paper_id = intval($input['paper_id']);
$user_id = intval($input['user_id']);

// Get paper info
$sql = "SELECT * FROM past_papers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $paper_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Paper not found']);
    exit;
}

$paper = $result->fetch_assoc();

// Record download
$sql = "INSERT INTO download_history (user_id, paper_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $paper_id);
$stmt->execute();

// Update download count
$sql = "UPDATE past_papers SET downloads = downloads + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $paper_id);
$stmt->execute();

echo json_encode([
    'success' => true,
    'file_path' => $paper['file_path'],
    'file_name' => $paper['file_name']
]);

$conn->close();
?>