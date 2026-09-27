<?php
// api/users/change-password.php — POST: Change Password for Authenticated User

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();
validateCsrf();

$data            = json_decode(file_get_contents('php://input'), true);
$currentPassword = $data['current_password'] ?? '';
$newPassword     = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All password fields are required.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
    exit;
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
    exit;
}

$db = getDB();

// Fetch current user details including password hash
$stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$authUser['id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

// Hash and update new password
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $authUser['id']]);

logActivity($authUser['id'], 'password_change', null, "Changed password for user account ID: {$authUser['id']}");

echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
