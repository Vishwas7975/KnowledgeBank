<?php
// api/documents/view.php
// Streams a file inline so the browser can preview it in a new tab.
// Must NOT use requireAuth() because that returns JSON 401 — useless in a tab.

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/settings.php';

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed.');
}

// ── Auth: redirect to login on failure (this is a browser tab, not fetch) ──
startSession();
if (empty($_SESSION['user_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
    header('Location: /login.php?redirect=' . $redirect);
    exit;
}

// ── Validate ID ───────────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    http_response_code(400);
    exit('Missing document ID.');
}

// ── Fetch document record ────────────────────────────────────
$db   = getDB();
$stmt = $db->prepare(
    'SELECT d.id, d.original_name, d.stored_name, d.file_type, d.file_size,
            d.status, d.folder_id,
            COALESCE(f.is_confidential, 0) AS is_confidential
     FROM documents d
     LEFT JOIN folders f ON f.id = d.folder_id AND f.is_deleted = 0
     WHERE d.id = ? AND d.is_deleted = 0
     LIMIT 1'
);
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    exit('Document not found.');
}

// ── Role-based access gates ───────────────────────────────────
$isAdmin = ($_SESSION['role'] === 'admin');

// 1. Employees cannot view unapproved documents
if (!$isAdmin && $doc['status'] !== 'approved') {
    http_response_code(403);
    exit('This document is not yet approved.');
}

// 2. For confidential folders, employees need an active approved download request
if (!$isAdmin && $doc['is_confidential']) {
    $drStmt = $db->prepare(
        "SELECT id FROM download_requests
         WHERE document_id = ? AND requested_by = ? AND status = 'approved'
           AND (expires_at IS NULL OR expires_at > NOW())
         LIMIT 1"
    );
    $drStmt->execute([$doc['id'], (int)$_SESSION['user_id']]);
    if (!$drStmt->fetch()) {
        http_response_code(403);
        exit('Access denied. You need an approved download request to view this document.');
    }
}

// ── Resolve file path ─────────────────────────────────────────
$filePath = rtrim(STORAGE_PATH, '/\\') . DIRECTORY_SEPARATOR . $doc['stored_name'];

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('File not found on server.');
}

// ── Log the view ──────────────────────────────────────────────
logActivity(
    (int)$_SESSION['user_id'],
    'download',
    $doc['id'],
    'Viewed file: ' . $doc['original_name']
);

// ── Choose inline vs attachment ───────────────────────────────
// Inline = browser opens it (PDF viewer, image). Attachment = download prompt.
$inlineMimes = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'text/plain',
];
$disposition = in_array($doc['file_type'], $inlineMimes, true) ? 'inline' : 'attachment';

// ── Safe filename for Content-Disposition header ──────────────
$ascii   = preg_replace('/[^\x20-\x7E]/', '_', $doc['original_name']);
$encoded = rawurlencode($doc['original_name']);

// ── Send file ─────────────────────────────────────────────────
// Clear any output buffer so we don't corrupt binary content
while (ob_get_level()) ob_end_clean();

header('Content-Type: '        . $doc['file_type']);
header('Content-Length: '      . $doc['file_size']);
header('Content-Disposition: ' . $disposition . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . $encoded);
header('Cache-Control: private, no-cache');
header('Pragma: no-cache');

readfile($filePath);
exit;