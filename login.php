<?php
require_once 'config/database.php';

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
if (!isset($input['username']) || !isset($input['password']) || !isset($input['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$username = $conn->real_escape_string(trim($input['username']));
$password = trim($input['password']);
$role = $conn->real_escape_string(trim($input['role']));

// Validate role
if (!in_array($role, ['admin', 'user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid role selected'
    ]);
    exit;
}

// Query to find user
$sql = "SELECT id, full_name, username, email, password, role FROM users WHERE username = ? AND role = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Password is correct, create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        
        // Determine redirect URL based on role
        $redirect_url = ($user['role'] === 'admin') ? 'admin-dashboard.php' : 'user-dashboard.php';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $redirect_url,
            'user' => [
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
}

$stmt->close();
$conn->close();
?>