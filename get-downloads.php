<?php
require_once '../config/config.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

$sql = "SELECT dh.*, p.title, p.file_path, p.year, p.semester 
        FROM download_history dh 
        JOIN past_papers p ON dh.paper_id = p.id 
        WHERE dh.user_id = ? 
        ORDER BY dh.downloaded_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$downloads = [];
while ($row = $result->fetch_assoc()) {
    $downloads[] = $row;
}

echo json_encode([
    'success' => true,
    'downloads' => $downloads
]);

$conn->close();
?>