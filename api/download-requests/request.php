<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}

$authUser = requireAuth();
validateCsrf();
$input = json_decode(file_get_contents('php://input'), true);
$docId = isset($input['document_id']) ? (int)$input['document_id'] : 0;
if (!$docId) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Document ID is required.']); exit; }

$db = getDB();

$stmt = $db->prepare("SELECT id, original_name FROM documents WHERE id = ? AND is_deleted = 0 LIMIT 1");
$stmt->execute([$docId]);
$doc = $stmt->fetch();
if (!$doc) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Document not found.']); exit; }

// Already pending?
$check = $db->prepare("SELECT id FROM download_requests WHERE document_id = ? AND requested_by = ? AND status = 'pending' LIMIT 1");
$check->execute([$docId, $authUser['id']]);
if ($check->fetch()) {
    echo json_encode(['success'=>false,'message'=>'You already have a pending request for this document. Please wait for admin approval.']); exit;
}

// Already approved and not expired?
$approved = $db->prepare("SELECT id FROM download_requests WHERE document_id = ? AND requested_by = ? AND status = 'approved' AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1");
$approved->execute([$docId, $authUser['id']]);
if ($approved->fetch()) {
    echo json_encode(['success'=>false,'message'=>'You already have an active approval for this document.']); exit;
}

$insert = $db->prepare("INSERT INTO download_requests (document_id, requested_by, status, requested_at) VALUES (?, ?, 'pending', NOW())");
$insert->execute([$docId, $authUser['id']]);

logActivity($authUser['id'], 'download_request', $docId, "Requested download approval for: {$doc['original_name']}");
echo json_encode(['success'=>true,'message'=>'Download request submitted. You will be notified once approved.']);