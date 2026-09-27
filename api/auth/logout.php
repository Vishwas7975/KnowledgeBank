<?php
// api/auth/logout.php — POST: destroy session

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

startSession();
validateCsrf();

if (!empty($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', null, 'User logged out.');
}

session_unset();
session_destroy();

// Clear session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);