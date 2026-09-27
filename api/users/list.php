<?php
// api/users/list.php — GET: list all employee accounts (Admin only)

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

$stmt = $db->prepare("
    SELECT
        id, name, employee_id, email, role,
        department, status, created_at, last_login
    FROM users
    ORDER BY created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'count'   => count($users),
    'users'   => $users,
]);