<?php
// api/users/deactivate.php — POST: deactivate employee account (Admin only)

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);
$id   = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

// Prevent admin from deactivating themselves
if ($id === (int)$authUser['id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, name, status FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

if ($user['status'] === 'inactive') {
    echo json_encode(['success' => false, 'message' => 'User is already deactivated.']);
    exit;
}

$db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?")->execute([$id]);

logActivity($authUser['id'], 'user_deactivate', null, "Deactivated user account: {$user['name']} (ID: {$id})");

echo json_encode(['success' => true, 'message' => "Account '{$user['name']}' has been deactivated."]);