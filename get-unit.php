<?php
require_once '../config/config.php';

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id > 0) {
    $sql = "SELECT * FROM course_units WHERE course_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM course_units ORDER BY name";
    $result = $conn->query($sql);
}

$units = [];
while ($row = $result->fetch_assoc()) {
    $units[] = $row;
}

echo json_encode([
    'success' => true,
    'units' => $units
]);

$conn->close();
?>