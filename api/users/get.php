<?php
// api/users/get.php — GET: get single user details
// Admins can fetch any user. Employees can only fetch their own profile.

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth(); // employees can access now

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

// Employees can only fetch their own profile
if ($authUser['role'] !== 'admin' && $authUser['id'] !== $id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden. You can only view your own profile.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, name, employee_id, email, role,
           department, status, created_at, last_login
    FROM users WHERE id = ? LIMIT 1
");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

echo json_encode(['success' => true, 'user' => $user]);