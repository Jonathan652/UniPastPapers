<?php
require_once 'config.php';

// Set JSON header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['full_name', 'email', 'username', 'password', 'confirm_password', 'role'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        sendJSONResponse([
            'success' => false,
            'message' => 'All fields are required'
        ], 400);
    }
}

// Sanitize inputs
$fullName = sanitizeInput($input['full_name']);
$email = sanitizeInput($input['email']);
$username = sanitizeInput($input['username']);
$password = $input['password'];
$confirmPassword = $input['confirm_password'];
// Force all registrations to be regular users only
$role = 'user';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Invalid email format'
    ], 400);
}

// Validate password
if (strlen($password) < PASSWORD_MIN_LENGTH) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
    ], 400);
}

if ($password !== $confirmPassword) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Passwords do not match'
    ], 400);
}

// Validate username
if (strlen($username) < 3) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Username must be at least 3 characters long'
    ], 400);
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Username can only contain letters, numbers, and underscores'
    ], 400);
}

// Validate full name
if (strlen($fullName) < 2) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Full name must be at least 2 characters long'
    ], 400);
}

try {
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Username already exists'
        ], 400);
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Email already exists'
        ], 400);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([$fullName, $email, $username, $hashedPassword, $role]);

    if ($result) {
        $userId = $conn->lastInsertId();
        
        // Log activity
        logActivity($userId, 'register', 'New user registered');
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Account created successfully! You can now login.'
        ]);
    } else {
        sendJSONResponse([
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ], 500);
    }

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>