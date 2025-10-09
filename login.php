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

// Validate input
if (!isset($input['username']) || !isset($input['password'])) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Username and password are required'
    ], 400);
}

$username = sanitizeInput($input['username']);
$password = $input['password'];

// Validate input length
if (strlen($username) < 3) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Username must be at least 3 characters long'
    ], 400);
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    sendJSONResponse([
        'success' => false,
        'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
    ], 400);
}

try {
    // Find user by username
    $stmt = $conn->prepare("SELECT id, full_name, username, email, password, role, is_active FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Invalid username or password'
        ], 401);
    }

    // Check if user is active
    if (!$user['is_active']) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Account is deactivated. Please contact administrator.'
        ], 401);
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendJSONResponse([
            'success' => false,
            'message' => 'Invalid username or password'
        ], 401);
    }

    // Check if password needs rehashing
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$newHash, $user['id']]);
    }

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = date('Y-m-d H:i:s');

    // Log activity
    logActivity($user['id'], 'login', 'User logged in successfully');

    // Determine redirect URL
    $redirectUrl = ($user['role'] === 'admin') ? 'admin-dashboard.html' : 'user-dashboard.html';

    sendJSONResponse([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirectUrl,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendJSONResponse([
        'success' => false,
        'message' => 'Database error occurred'
    ], 500);
}
?>