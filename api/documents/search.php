<?php
// api/documents/search.php — GET: search documents by name, uploader, type

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Search query is required.']);
    exit;
}

$db     = getDB();
$search = '%' . $q . '%';

$stmt = $db->prepare("
    SELECT
        d.id, d.original_name, d.file_type, d.file_size, d.upload_time,
        u.name        AS uploaded_by_name,
        u.id          AS uploaded_by_id,
        u.employee_id AS uploaded_by_emp_id
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    WHERE d.is_deleted = 0
      AND (
          d.original_name LIKE ?
          OR u.name        LIKE ?
          OR d.file_type   LIKE ?
      )
    ORDER BY d.upload_time DESC
");
$stmt->execute([$search, $search, $search]);
$results = $stmt->fetchAll();

foreach ($results as &$doc) {
    $doc['file_size_formatted'] = formatFileSize((int)$doc['file_size']);
    $doc['can_delete'] = (
        $authUser['role'] === 'admin' ||
        (int)$doc['uploaded_by_id'] === (int)$authUser['id']
    );
}

echo json_encode([
    'success' => true,
    'query'   => $q,
    'count'   => count($results),
    'results' => $results,
]);

function formatFileSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}