<?php
// api/documents/get.php — GET: return metadata for a single document

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT
        d.id, d.original_name, d.file_type, d.file_size, d.upload_time,
        u.name        AS uploaded_by_name,
        u.id          AS uploaded_by_id,
        u.employee_id AS uploaded_by_emp_id,
        u.department  AS uploaded_by_dept
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    WHERE d.id = ? AND d.is_deleted = 0
    LIMIT 1
");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Document not found.']);
    exit;
}

$doc['file_size_formatted'] = formatFileSize((int)$doc['file_size']);
$doc['can_delete'] = (
    $authUser['role'] === 'admin' ||
    (int)$doc['uploaded_by_id'] === (int)$authUser['id']
);

echo json_encode(['success' => true, 'document' => $doc]);

function formatFileSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}