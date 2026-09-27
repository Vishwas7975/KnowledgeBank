<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit; }

$authUser = requireAdmin();
validateCsrf();
$input = json_decode(file_get_contents('php://input'), true);
$reqId = isset($input['request_id']) ? (int)$input['request_id'] : 0;
if (!$reqId) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Request ID required.']); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT dr.*, d.original_name, u.name AS requester_name FROM download_requests dr JOIN documents d ON d.id=dr.document_id JOIN users u ON u.id=dr.requested_by WHERE dr.id=? LIMIT 1");
$stmt->execute([$reqId]);
$req = $stmt->fetch();
if (!$req) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Request not found.']); exit; }

$upd = $db->prepare("UPDATE download_requests SET status='rejected', reviewed_by=?, reviewed_at=NOW() WHERE id=?");
$upd->execute([$authUser['id'], $reqId]);

logActivity($authUser['id'], 'reject_download_request', $req['document_id'], "Rejected download for {$req['requester_name']}: {$req['original_name']}");
echo json_encode(['success'=>true,'message'=>'Download request rejected.']);