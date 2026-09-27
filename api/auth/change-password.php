<?php
// api/auth/change-password.php — POST: change own password (requires current password)

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

$data        = json_decode(file_get_contents('php://input'), true);
$currentPass = trim($data['current_password'] ?? '');
$newPass     = trim($data['new_password'] ?? '');
$confirmPass = trim($data['confirm_password'] ?? '');

// ── Validate inputs ────────────────────────────────────────────
if (!$currentPass || !$newPass || !$confirmPass) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if ($newPass !== $confirmPass) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match.']);
    exit;
}

// Password strength: min 8 chars, at least one number, one special char
if (!preg_match('/^(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,}$/', $newPass)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters and include a number and a special character.'
    ]);
    exit;
}

$db = getDB();

// ── Verify current password ────────────────────────────────────
$stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$authUser['id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($currentPass, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

// ── Update password ────────────────────────────────────────────
$newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
$db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
   ->execute([$newHash, $authUser['id']]);

logActivity($authUser['id'], 'user_update', null, 'User changed their password.');

echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);