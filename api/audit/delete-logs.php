<?php
// api/audit/delete-logs.php — GET: all file deletion records (Admin only)

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

$countStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE action_type = 'delete'");
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare("
    SELECT
        al.id, al.description, al.ip_address, al.created_at,
        u.name        AS deleted_by_name,
        u.employee_id AS deleted_by_emp_id,
        u.role        AS deleted_by_role,
        d.original_name AS file_name,
        d.file_type
    FROM activity_logs al
    JOIN  users u    ON u.id  = al.user_id
    LEFT JOIN documents d ON d.id = al.document_id
    WHERE al.action_type = 'delete'
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$logs = $stmt->fetchAll();

echo json_encode([
    'success'     => true,
    'total'       => $total,
    'page'        => $page,
    'limit'       => $limit,
    'total_pages' => ceil($total / $limit),
    'logs'        => $logs,
]);