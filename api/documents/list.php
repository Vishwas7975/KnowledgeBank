<?php
// api/documents/list.php — GET: return all non-deleted documents

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
        d.id,
        d.original_name,
        d.file_type,
        d.file_size,
        d.upload_time,
        d.folder_id,
        d.status,
        f.name        AS folder_name,
        u.name        AS uploaded_by_name,
        u.id          AS uploaded_by_id,
        u.role        AS uploaded_by_role,
        u.employee_id AS uploaded_by_emp_id
    FROM documents d
    JOIN users u ON u.id = d.uploaded_by
    LEFT JOIN folders f ON f.id = d.folder_id AND f.is_deleted = 0
    WHERE d.is_deleted = 0
    ORDER BY d.upload_time DESC
");
$stmt->execute();
$documents = $stmt->fetchAll();

$officeExts = ['doc','docx','xls','xlsx','ppt','pptx','odt','ods','odp'];

// For employees: fetch ALL their download requests in ONE query upfront
// Map: document_id => ['status'=>..., 'expires_at'=>...]
$dlRequestMap = [];

if ($authUser['role'] === 'employee') {
    $dlTableExists = false;
    try {
        $db->query("SELECT 1 FROM download_requests LIMIT 1");
        $dlTableExists = true;
    } catch (PDOException $e) {
        $dlTableExists = false;
    }

    if ($dlTableExists) {
        // Get the latest request per document for this employee
        $rqStmt = $db->prepare("
            SELECT dr.document_id, dr.status, dr.expires_at
            FROM download_requests dr
            INNER JOIN (
                SELECT document_id, MAX(requested_at) AS latest
                FROM download_requests
                WHERE requested_by = ?
                GROUP BY document_id
            ) latest_rq ON latest_rq.document_id = dr.document_id
                        AND latest_rq.latest = dr.requested_at
            WHERE dr.requested_by = ?
        ");
        $rqStmt->execute([$authUser['id'], $authUser['id']]);
        $rows = $rqStmt->fetchAll();
        foreach ($rows as $row) {
            // treat approved-but-expired as expired
            $status = $row['status'];
            if ($status === 'approved' && $row['expires_at'] && strtotime($row['expires_at']) < time()) {
                $status = 'expired';
            }
            $dlRequestMap[(int)$row['document_id']] = $status;
        }
    }
}

foreach ($documents as &$doc) {
    $doc['file_size_formatted'] = formatFileSize((int)$doc['file_size']);
    $doc['can_delete'] = (
        $authUser['role'] === 'admin' ||
        (int)$doc['uploaded_by_id'] === (int)$authUser['id']
    );

    $ext = strtolower(pathinfo($doc['original_name'], PATHINFO_EXTENSION));
    $doc['is_locked'] = ($authUser['role'] === 'employee' && in_array($ext, $officeExts));
    $doc['download_request_status'] = $doc['is_locked']
        ? ($dlRequestMap[(int)$doc['id']] ?? null)
        : null;
}
unset($doc);

echo json_encode([
    'success'   => true,
    'count'     => count($documents),
    'documents' => $documents,
]);

function formatFileSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}