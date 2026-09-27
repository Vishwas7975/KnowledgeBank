<?php
// api/dashboard/recent-uploads.php — GET: last 10 uploaded documents (Admin only)

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

$limit = min(50, max(5, (int)($_GET['limit'] ?? 10)));

$stmt = $db->prepare("
    SELECT
        d.id, d.original_name, d.file_type,
        d.file_size, d.upload_time,
        u.name        AS uploaded_by_name,
        u.employee_id AS uploaded_by_emp_id,
        u.role        AS uploaded_by_role
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    WHERE d.is_deleted = 0
    ORDER BY d.upload_time DESC
    LIMIT ?
");
$stmt->execute([$limit]);
$docs = $stmt->fetchAll();

foreach ($docs as &$doc) {
    $bytes = (int)$doc['file_size'];
    $doc['file_size_formatted'] = $bytes >= 1048576
        ? round($bytes/1048576, 2).' MB'
        : round($bytes/1024, 2).' KB';
}

echo json_encode([
    'success'   => true,
    'count'     => count($docs),
    'documents' => $docs,
]);