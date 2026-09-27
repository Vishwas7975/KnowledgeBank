<?php
// api/audit/upload-logs.php — GET: all file upload records (Admin only)

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

$countStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE action_type = 'upload'");
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare("
    SELECT
        al.id, al.description, al.ip_address, al.created_at,
        u.name        AS uploaded_by_name,
        u.employee_id AS uploaded_by_emp_id,
        d.original_name AS file_name,
        d.file_type,
        d.file_size
    FROM activity_logs al
    JOIN  users u    ON u.id  = al.user_id
    LEFT JOIN documents d ON d.id = al.document_id
    WHERE al.action_type = 'upload'
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$logs = $stmt->fetchAll();

foreach ($logs as &$log) {
    if ($log['file_size']) {
        $bytes = (int)$log['file_size'];
        $log['file_size_formatted'] = $bytes >= 1048576
            ? round($bytes/1048576, 2).' MB'
            : round($bytes/1024, 2).' KB';
    }
}

echo json_encode([
    'success'     => true,
    'total'       => $total,
    'page'        => $page,
    'limit'       => $limit,
    'total_pages' => ceil($total / $limit),
    'logs'        => $logs,
]);