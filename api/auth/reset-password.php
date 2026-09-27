<?php
// api/auth/reset-password.php — POST: verify OTP and set new password

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data        = json_decode(file_get_contents('php://input'), true);
$email       = strtolower(trim($data['email']           ?? ''));
$otp         = trim($data['otp']                        ?? '');
$newPassword = $data['new_password']                    ?? ($data['password'] ?? '');
$confirmPass = $data['confirm_password']                ?? $newPassword;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid email address is required.']);
    exit;
}

if (!preg_match('/^\d{6}$/', $otp)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'OTP must be a 6-digit number.']);
    exit;
}

if (!$newPassword || !$confirmPass) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirm password are required.']);
    exit;
}

if ($newPassword !== $confirmPass) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password and confirm password do not match.']);
    exit;
}

// KnowledgeBank password policy: min 8 chars + number + special character
if (!preg_match('/^(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,}$/', $newPassword)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters and include a number and a special character.'
    ]);
    exit;
}

$db = getDB();

$stmt = $db->prepare(
    'SELECT id, otp, expires_at, COALESCE(attempts, 0) AS attempts FROM password_resets
     WHERE email = ? ORDER BY created_at DESC LIMIT 1'
);
$stmt->execute([$email]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No reset request found for this email. Please request a new code.']);
    exit;
}

// Check attempt counter lockout (5 max attempts)
if ((int)$record['attempts'] >= 5) {
    $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many failed attempts. This OTP code has been locked. Please request a new one.']);
    exit;
}

if (new DateTime() > new DateTime($record['expires_at'])) {
    $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Your reset code has expired. Please request a new one.']);
    exit;
}

if (!hash_equals($record['otp'], $otp)) {
    try {
        $db->prepare('UPDATE password_resets SET attempts = COALESCE(attempts, 0) + 1 WHERE id = ?')->execute([$record['id']]);
    } catch (\Throwable $e) {
        // Fallback if attempts column not present
    }

    $currentAttempts = (int)$record['attempts'] + 1;
    $remainingAttempts = 5 - $currentAttempts;

    if ($remainingAttempts <= 0) {
        $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many failed attempts. This OTP code has been locked. Please request a new one.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Invalid OTP. You have {$remainingAttempts} attempt(s) remaining."]);
    exit;
}

$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
$db->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([$hash, $email]);

$db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

echo json_encode(['success' => true, 'message' => 'Password reset successfully. You can now log in with your new password.']);