<?php
// api/documents/delete.php — POST: soft delete a document
// Admin: can delete any | Employee: can delete only their own

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();
validateCsrf();

$data = json_decode(file_get_contents('php://input'), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, original_name, uploaded_by, file_path
    FROM documents
    WHERE id = ? AND is_deleted = 0
    LIMIT 1
");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Document not found.']);
    exit;
}

// ── Permission check ───────────────────────────────────────────
if ($authUser['role'] !== 'admin' && (int)$doc['uploaded_by'] !== (int)$authUser['id']) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'You can only delete documents you uploaded.'
    ]);
    exit;
}

// ── Soft delete in DB ──────────────────────────────────────────
$db->prepare("
    UPDATE documents
    SET is_deleted = 1, deleted_by = ?, deleted_at = NOW()
    WHERE id = ?
")->execute([$authUser['id'], $id]);

// ── Remove physical file ───────────────────────────────────────
if (file_exists($doc['file_path'])) {
    unlink($doc['file_path']);
}

logActivity($authUser['id'], 'delete', $id, "Deleted file: {$doc['original_name']}");

echo json_encode([
    'success' => true,
    'message' => "Document '{$doc['original_name']}' deleted successfully."
]);