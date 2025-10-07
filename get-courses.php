<?php
require_once '../config/config.php';

$faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

if ($faculty_id > 0) {
    $sql = "SELECT * FROM courses WHERE faculty_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM courses ORDER BY name";
    $result = $conn->query($sql);
}

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode([
    'success' => true,
    'courses' => $courses
]);

$conn->close();
?>