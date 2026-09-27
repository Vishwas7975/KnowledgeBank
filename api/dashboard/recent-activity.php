<?php
// api/dashboard/recent-activity.php — GET: last 20 actions across the system (Admin only)

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAdmin();
$db = getDB();

$limit = min(50, max(5, (int)($_GET['limit'] ?? 20)));

$stmt = $db->prepare("
    SELECT
        al.id, al.action_type, al.description,
        al.ip_address, al.created_at,
        u.name        AS user_name,
        u.employee_id AS user_emp_id,
        u.role        AS user_role,
        d.original_name AS document_name
    FROM activity_logs al
    JOIN  users u    ON u.id  = al.user_id
    LEFT JOIN documents d ON d.id = al.document_id
    ORDER BY al.created_at DESC
    LIMIT ?
");
$stmt->execute([$limit]);
$activity = $stmt->fetchAll();

// Add a human-readable label per action type
$labels = [
    'upload'           => '📤 File Uploaded',
    'download'         => '📥 File Downloaded',
    'delete'           => '🗑️ File Deleted',
    'login'            => '🔐 Logged In',
    'logout'           => '🔓 Logged Out',
    'user_create'      => '👤 User Created',
    'user_update'      => '✏️ User Updated',
    'user_deactivate'  => '🚫 User Deactivated',
    'user_reactivate'  => '✅ User Reactivated',
];

foreach ($activity as &$item) {
    $item['action_label'] = $labels[$item['action_type']] ?? $item['action_type'];
}

echo json_encode([
    'success'  => true,
    'count'    => count($activity),
    'activity' => $activity,
]);