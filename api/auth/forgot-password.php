<?php
// api/auth/forgot-password.php — POST: send 6-digit OTP to registered email

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($data['email'] ?? ''));

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A valid email address is required.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare('SELECT id, name FROM users WHERE email = ? AND status = ? LIMIT 1');
$stmt->execute([$email, 'active']);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => true, 'message' => 'If that email is registered, a reset code has been sent.']);
    exit;
}

// Delete any old OTP for this email
$db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

// Generate 6-digit OTP, expires in 10 minutes
$otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', time() + 600);

$stmt = $db->prepare('INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)');
$stmt->execute([$email, $otp, $expiresAt]);

require_once __DIR__ . '/../../helpers/Mailer.php';
$sent = Mailer::sendOtp($email, $user['name'], $otp);

if (!$sent) {
    $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send reset email. Please try again.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'If that email is registered, a reset code has been sent.']);