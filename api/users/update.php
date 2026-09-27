<?php
// api/users/update.php — POST: update employee name, email, department (Admin only)

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAdmin();
validateCsrf();

$data       = json_decode(file_get_contents('php://input'), true);
$id         = isset($data['id']) ? (int)$data['id'] : 0;
$name       = trim($data['name']       ?? '');
$email      = trim($data['email']      ?? '');
$department = trim($data['department'] ?? '');

if (!$id || !$name || !$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID, name and email are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$db = getDB();

// Check user exists
$check = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
$check->execute([$id]);
if (!$check->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Check email not taken by another user
$emailCheck = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
$emailCheck->execute([$email, $id]);
if ($emailCheck->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already in use by another account.']);
    exit;
}

$db->prepare("UPDATE users SET name = ?, email = ?, department = ? WHERE id = ?")
   ->execute([$name, $email, $department, $id]);

logActivity($authUser['id'], 'user_update', null, "Updated user account ID: {$id}");

echo json_encode(['success' => true, 'message' => 'User updated successfully.']);