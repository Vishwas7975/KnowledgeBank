<?php
// api/audit/login-logs.php — GET: login and logout history with IP and device (Admin only)

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

$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$status = isset($_GET['status']) ? trim($_GET['status']) : null; // success | failed

$where  = ['1=1'];
$params = [];

if ($status && in_array($status, ['success', 'failed'], true)) {
    $where[]  = 'll.status = ?';
    $params[] = $status;
}

$whereSQL = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM login_logs ll WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare("
    SELECT
        ll.id, ll.login_time, ll.ip_address,
        ll.browser, ll.device_type, ll.os, ll.status,
        u.name        AS user_name,
        u.email       AS user_email,
        u.employee_id AS user_emp_id
    FROM login_logs ll
    LEFT JOIN users u ON u.id = ll.user_id
    WHERE {$whereSQL}
    ORDER BY ll.login_time DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$limit, $offset]));
$logs = $stmt->fetchAll();

echo json_encode([
    'success'     => true,
    'total'       => $total,
    'page'        => $page,
    'limit'       => $limit,
    'total_pages' => ceil($total / $limit),
    'logs'        => $logs,
]);