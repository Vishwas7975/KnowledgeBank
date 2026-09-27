<?php
// api/documents/my-uploads.php — GET: documents uploaded by current logged-in user

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();
$db       = getDB();

$stmt = $db->prepare("
    SELECT
        d.id, d.original_name, d.file_type, d.file_size, d.upload_time,
        d.status,
        u.name AS uploaded_by_name
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    WHERE d.is_deleted = 0 AND d.uploaded_by = ?
    ORDER BY d.upload_time DESC
");
$stmt->execute([$authUser['id']]);
$docs = $stmt->fetchAll();

foreach ($docs as &$doc) {
    $doc['file_size_formatted'] = formatFileSize((int)$doc['file_size']);
    $doc['can_delete'] = true; // always true — these are their own uploads
}

echo json_encode([
    'success'   => true,
    'count'     => count($docs),
    'documents' => $docs,
]);

function formatFileSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}