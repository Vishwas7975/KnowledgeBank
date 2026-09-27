<?php
// api/documents/reject.php — POST: admin rejects a pending document

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAdmin();
$db       = getDB();

$body   = json_decode(file_get_contents('php://input'), true);
$id     = (int)($body['id'] ?? 0);
$reason = trim($body['reason'] ?? '');

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Document ID is required.']);
    exit;
}

// Verify document exists
$check = $db->prepare("SELECT id, original_name, status FROM documents WHERE id = ? AND is_deleted = 0");
$check->execute([$id]);
$doc = $check->fetch();

if (!$doc) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Document not found.']);
    exit;
}

if ($doc['status'] === 'rejected') {
    echo json_encode(['success' => false, 'message' => 'Document is already rejected.']);
    exit;
}

$stmt = $db->prepare("UPDATE documents SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), reject_reason = ? WHERE id = ?");
$stmt->execute([$authUser['id'], $reason ?: null, $id]);

logActivity($authUser['id'], 'reject', $id, "Rejected document: {$doc['original_name']}" . ($reason ? " — Reason: {$reason}" : ''));

echo json_encode([
    'success' => true,
    'message' => 'Document rejected.',
]);