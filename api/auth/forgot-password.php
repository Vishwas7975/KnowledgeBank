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
$ip = getClientIp();

// ── 1. Per-IP Rate Limit: Max 5 password reset requests per IP per 15 minutes ──
try {
    $ipCheck = $db->prepare(
        'SELECT COUNT(*) AS total FROM password_resets
         WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
    );
    $ipCheck->execute([$ip]);
    $ipRow = $ipCheck->fetch();
    if ($ipRow && (int)$ipRow['total'] >= 5) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many password reset requests from your IP address. Please wait 15 minutes.'
        ]);
        exit;
    }
} catch (\Throwable $e) {
    // If ip_address column not present yet
}

// ── 2. Per-Email Rate Limit: 1 OTP request per email per 60 seconds ─────────
$rateCheck = $db->prepare(
    'SELECT created_at FROM password_resets
     WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)
     ORDER BY created_at DESC LIMIT 1'
);
$rateCheck->execute([$email]);
$recentRequest = $rateCheck->fetch();

if ($recentRequest) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Please wait 60 seconds before requesting another password reset code.'
    ]);
    exit;
}

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

try {
    $stmt = $db->prepare('INSERT INTO password_resets (email, otp, expires_at, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([$email, $otp, $expiresAt, $ip]);
} catch (\Throwable $e) {
    $stmt = $db->prepare('INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$email, $otp, $expiresAt]);
}

require_once __DIR__ . '/../../helpers/Mailer.php';
$sent = Mailer::sendOtp($email, $user['name'], $otp);

if (!$sent) {
    $db->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send reset email. Please try again.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'If that email is registered, a reset code has been sent.']);