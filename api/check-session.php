<?php
require_once '../config.php';

header('Content-Type: application/json');

echo json_encode([
    'session_id' => session_id(),
    'session_status' => session_status(),
    'is_logged_in' => isLoggedIn(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE
]);
?>