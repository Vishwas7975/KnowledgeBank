<?php
// api/dashboard/stats.php — GET: summary counts (Admin only)

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

// Total documents (not deleted)
$totalDocs = (int)$db->query("SELECT COUNT(*) FROM documents WHERE is_deleted = 0")->fetchColumn();

// Total employees (excluding admins)
$totalEmployees = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();

// Active employees
$activeEmployees = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'employee' AND status = 'active'")->fetchColumn();

// Docs uploaded this week
$docsThisWeek = (int)$db->query("
    SELECT COUNT(*) FROM documents
    WHERE is_deleted = 0
    AND upload_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetchColumn();

// Docs uploaded today
$docsToday = (int)$db->query("
    SELECT COUNT(*) FROM documents
    WHERE is_deleted = 0
    AND DATE(upload_time) = CURDATE()
")->fetchColumn();

// Total downloads (all time)
$totalDownloads = (int)$db->query("
    SELECT COUNT(*) FROM activity_logs WHERE action_type = 'download'
")->fetchColumn();

// Total storage used (bytes)
$storageUsed = (int)$db->query("
    SELECT COALESCE(SUM(file_size), 0) FROM documents WHERE is_deleted = 0
")->fetchColumn();

$storageFormatted = $storageUsed >= 1048576
    ? round($storageUsed / 1048576, 2) . ' MB'
    : round($storageUsed / 1024, 2) . ' KB';

// Failed logins today
$failedLoginsToday = (int)$db->query("
    SELECT COUNT(*) FROM login_logs
    WHERE status = 'failed' AND DATE(login_time) = CURDATE()
")->fetchColumn();

echo json_encode([
    'success' => true,
    'stats'   => [
        'total_documents'   => $totalDocs,
        'total_employees'   => $totalEmployees,
        'active_employees'  => $activeEmployees,
        'docs_this_week'    => $docsThisWeek,
        'docs_today'        => $docsToday,
        'total_downloads'   => $totalDownloads,
        'storage_used_bytes'=> $storageUsed,
        'storage_used'      => $storageFormatted,
        'failed_logins_today' => $failedLoginsToday,
    ]
]);