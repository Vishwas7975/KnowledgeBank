<?php
// api/auth/session-check.php — GET: called every 60s by frontend JS
// Returns session status; frontend redirects to login if expired

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

startSession();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No active session.']);
    exit;
}

$timeout = (int) getSystemSetting(
    $_SESSION['role'] === 'admin' ? 'admin_session_timeout' : 'employee_session_timeout',
    1800
);

$lastActivity = $_SESSION['last_activity'] ?? 0;
$elapsed      = time() - $lastActivity;
$remaining    = $timeout - $elapsed;

if ($remaining <= 0) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

// Refresh last activity
$_SESSION['last_activity'] = time();

echo json_encode([
    'success'           => true,
    'message'           => 'Session active.',
    'user_id'           => $_SESSION['user_id'],
    'role'              => $_SESSION['role'],
    'name'              => $_SESSION['name'],
    'seconds_remaining' => $remaining,
]);