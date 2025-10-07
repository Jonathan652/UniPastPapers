<?php
require_once 'config.php';
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$required_fields = ['full_name', 'email', 'username', 'password', 'role'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        exit;
    }
}

// Sanitize inputs
$full_name = $conn->real_escape_string(trim($input['full_name']));
$email = $conn->real_escape_string(trim($input['email']));
$username = $conn->real_escape_string(trim($input['username']));
$password = trim($input['password']);
$role = $conn->real_escape_string(trim($input['role']));

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

// Validate username length
if (strlen($username) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Username must be at least 3 characters long'
    ]);
    exit;
}

// Validate role (only allow 'user' role for registration)
if ($role !== 'user') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid account type'
    ]);
    exit;
}

// Check if username already exists
$check_username = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($check_username);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Username already exists'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Check if email already exists
$check_email = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email already exists'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insert_sql = "INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("sssss", $full_name, $email, $username, $hashed_password, $role);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully! You can now login.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
?>