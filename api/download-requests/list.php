<?php
// api/download-requests/list.php
// GET: Admin fetches all download requests

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAdmin();
$db = getDB();

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$where  = in_array($status, ['pending','approved','rejected']) ? "WHERE dr.status = '$status'" : '';

$stmt = $db->prepare("
    SELECT dr.id, dr.status, dr.requested_at, dr.reviewed_at, dr.expires_at,
           d.id AS document_id, d.original_name, d.file_type,
           u.id AS requester_id, u.name AS requester_name,
           u.employee_id AS requester_emp_id,
           rv.name AS reviewed_by_name
    FROM download_requests dr
    JOIN documents d ON d.id = dr.document_id
    JOIN users u     ON u.id = dr.requested_by
    LEFT JOIN users rv ON rv.id = dr.reviewed_by
    $where
    ORDER BY CASE dr.status WHEN 'pending' THEN 0 ELSE 1 END, dr.requested_at DESC
    LIMIT 200
");
$stmt->execute();
$requests = $stmt->fetchAll();

echo json_encode(['success' => true, 'requests' => $requests]);