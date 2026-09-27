<?php
// api/audit/logs.php — GET: full audit log, all actions, paginated (Admin only)

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAdmin();
$db = getDB();

$page    = max(1, (int)($_GET['page']    ?? 1));
$limit   = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset  = ($page - 1) * $limit;

// Optional filters
$userId     = isset($_GET['user_id'])     ? (int)$_GET['user_id']          : null;
$actionType = isset($_GET['action_type']) ? trim($_GET['action_type'])      : null;
$dateFrom   = isset($_GET['date_from'])   ? trim($_GET['date_from'])        : null;
$dateTo     = isset($_GET['date_to'])     ? trim($_GET['date_to'])          : null;

$where  = ['1=1'];
$params = [];

if ($userId) {
    $where[]  = 'al.user_id = ?';
    $params[] = $userId;
}
if ($actionType) {
    $where[]  = 'al.action_type = ?';
    $params[] = $actionType;
}
if ($dateFrom) {
    $where[]  = 'al.created_at >= ?';
    $params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo) {
    $where[]  = 'al.created_at <= ?';
    $params[] = $dateTo . ' 23:59:59';
}

$whereSQL = implode(' AND ', $where);

$isExport = isset($_GET['export']) && $_GET['export'] === 'csv';

if ($isExport) {
    // CSV export — fetch all matching rows (no pagination)
    $stmt = $db->prepare("
        SELECT
            al.id, al.action_type, al.description,
            al.ip_address, al.created_at,
            u.name        AS user_name,
            u.employee_id AS user_emp_id,
            u.role        AS user_role,
            d.original_name AS document_name
        FROM activity_logs al
        JOIN  users u ON u.id = al.user_id
        LEFT JOIN documents d ON d.id = al.document_id
        WHERE {$whereSQL}
        ORDER BY al.created_at DESC
    ");
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    $filename = 'audit_logs_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Log ID', 'User', 'Employee ID', 'Role', 'Action Type', 'Document', 'Description', 'IP Address', 'Date/Time']);
    foreach ($logs as $log) {
        fputcsv($out, [
            '#AUD-' . str_pad($log['id'], 4, '0', STR_PAD_LEFT),
            $log['user_name']     ?? '',
            $log['user_emp_id']   ?? '',
            $log['user_role']     ?? '',
            $log['action_type']   ?? '',
            $log['document_name'] ?? '',
            $log['description']   ?? '',
            $log['ip_address']    ?? '',
            $log['created_at']    ?? '',
        ]);
    }
    fclose($out);
    exit;
}

// JSON response (paginated)
header('Content-Type: application/json');

// Total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs al WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Paginated results
$stmt = $db->prepare("
    SELECT
        al.id, al.action_type, al.description,
        al.ip_address, al.created_at,
        u.name        AS user_name,
        u.employee_id AS user_emp_id,
        u.role        AS user_role,
        d.original_name AS document_name
    FROM activity_logs al
    JOIN  users u ON u.id = al.user_id
    LEFT JOIN documents d ON d.id = al.document_id
    WHERE {$whereSQL}
    ORDER BY al.created_at DESC
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