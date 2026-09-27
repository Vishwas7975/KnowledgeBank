<?php
// api/folders/list.php — GET: list all active folders

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAuth();
$db = getDB();

$stmt = $db->prepare("
    SELECT
        f.id,
        f.name,
        f.created_at,
        f.is_confidential,
        u.name  AS created_by_name,
        COUNT(d.id) AS document_count
    FROM folders f
    LEFT JOIN users u ON u.id = f.created_by
    LEFT JOIN documents d ON d.folder_id = f.id AND d.is_deleted = 0
    WHERE f.is_deleted = 0
    GROUP BY f.id, f.name, f.created_at, f.is_confidential, u.name
    ORDER BY f.created_at DESC
");
$stmt->execute();
$folders = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'folders' => $folders,
]);