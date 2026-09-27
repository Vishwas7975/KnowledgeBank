<?php
// api/documents/download.php
// Office docs (docx, xlsx, pptx etc.) require admin approval for employees.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, original_name, stored_name, file_path, file_type, file_size
    FROM documents
    WHERE id = ? AND is_deleted = 0
    LIMIT 1
");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Document not found.']);
    exit;
}

// ── Office document lock for employees ────────────────────────
$officeExts = ['doc','docx','xls','xlsx','ppt','pptx','odt','ods','odp'];
$ext        = strtolower(pathinfo($doc['original_name'], PATHINFO_EXTENSION));

if ($authUser['role'] === 'employee' && in_array($ext, $officeExts)) {
    // Safe check — only enforce if table exists
    $dlTableExists = false;
    try {
        $db->query("SELECT 1 FROM download_requests LIMIT 1");
        $dlTableExists = true;
    } catch (PDOException $e) {
        $dlTableExists = false;
    }

    if ($dlTableExists) {
        $check = $db->prepare("
            SELECT id FROM download_requests
            WHERE document_id = ? AND requested_by = ?
              AND status = 'approved'
              AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");
        $check->execute([$id, $authUser['id']]);
        if (!$check->fetch()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'locked'  => true,
                'message' => 'This document requires admin approval to download. Please submit a download request.',
            ]);
            exit;
        }
    }
}
// ──────────────────────────────────────────────────────────────

$filePath = rtrim(STORAGE_PATH, '/\\') . DIRECTORY_SEPARATOR . $doc['stored_name'];

if (!file_exists($filePath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'File not found on server.']);
    exit;
}

logActivity($authUser['id'], 'download', $doc['id'], "Downloaded file: {$doc['original_name']}");

$asciiName   = preg_replace('/[^\x20-\x7E]/', '_', $doc['original_name']);
$encodedName = rawurlencode($doc['original_name']);

header('Content-Type: ' . $doc['file_type']);
header('Content-Disposition: attachment; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . $encodedName);
header('Content-Length: ' . $doc['file_size']);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;