<?php
// api/users/activity.php — GET: view upload + download history for a user (Admin only)

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$db = getDB();

// Verify user exists
$userStmt = $db->prepare("SELECT id, name, email, employee_id, department FROM users WHERE id = ? LIMIT 1");
$userStmt->execute([$id]);
$user = $userStmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Fetch activity logs
$logStmt = $db->prepare("
    SELECT
        al.id, al.action_type, al.description,
        al.ip_address,  al.created_at,
        d.original_name AS document_name
    FROM activity_logs al
    LEFT JOIN documents d ON d.id = al.document_id
    WHERE al.user_id = ?
    ORDER BY al.created_at DESC
    LIMIT 200
");
$logStmt->execute([$id]);
$logs = $logStmt->fetchAll();

// Summary counts
$uploads   = 0; $downloads = 0;
foreach ($logs as $log) {
    if ($log['action_type'] === 'upload')   $uploads++;
    if ($log['action_type'] === 'download') $downloads++;
}

echo json_encode([
    'success'  => true,
    'user'     => $user,
    'summary'  => [
        'total_uploads'   => $uploads,
        'total_downloads' => $downloads,
        'total_actions'   => count($logs),
    ],
    'activity' => $logs,
]);