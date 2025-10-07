<?php
require_once '../config/config.php';

$sql = "SELECT * FROM faculties ORDER BY name";
$result = $conn->query($sql);

$faculties = [];
while ($row = $result->fetch_assoc()) {
    $faculties[] = $row;
}

echo json_encode([
    'success' => true,
    'faculties' => $faculties
]);

$conn->close();
?>