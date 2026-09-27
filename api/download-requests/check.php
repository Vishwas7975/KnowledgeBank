<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

$authUser = requireAuth();
$docId = isset($_GET['document_id']) ? (int)$_GET['document_id'] : 0;
if (!$docId) { echo json_encode(['success'=>true,'status'=>'none']); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT status, expires_at FROM download_requests WHERE document_id=? AND requested_by=? ORDER BY requested_at DESC LIMIT 1");
$stmt->execute([$docId, $authUser['id']]);
$row = $stmt->fetch();

if (!$row) { echo json_encode(['success'=>true,'status'=>'none']); exit; }
if ($row['status'] === 'approved' && $row['expires_at'] && strtotime($row['expires_at']) < time()) {
    echo json_encode(['success'=>true,'status'=>'expired']); exit;
}
echo json_encode(['success'=>true,'status'=>$row['status'],'expires_at'=>$row['expires_at']]);