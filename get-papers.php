<?php
require_once '../config/config.php';

$unit_id = isset($_GET['unit']) ? intval($_GET['unit']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;

$sql = "SELECT p.*, cu.name as unit_name, cu.code as unit_code 
        FROM past_papers p 
        JOIN course_units cu ON p.course_unit_id = cu.id";

$conditions = [];
$params = [];
$types = "";

if ($unit_id > 0) {
    $conditions[] = "p.course_unit_id = ?";
    $params[] = $unit_id;
    $types .= "i";
}

if ($year > 0) {
    $conditions[] = "p.year = ?";
    $params[] = $year;
    $types .= "i";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY p.year DESC, p.created_at DESC";

$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$papers = [];
while ($row = $result->fetch_assoc()) {
    $papers[] = $row;
}

echo json_encode([
    'success' => true,
    'papers' => $papers
]);

$conn->close();
?>