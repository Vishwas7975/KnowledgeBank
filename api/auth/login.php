<?php
// api/auth/login.php — POST: validate credentials, create session

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$pass  = trim($data['password'] ?? '');

if (!$email || !$pass) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

$db = getDB();
$ua = parseUserAgent();
$ip = getClientIp();

// ── Check account lockout ──────────────────────────────────────
$maxAttempts = (int) getSystemSetting('max_failed_login_attempts', 5);

$lockStmt = $db->prepare(
    'SELECT attempt_count FROM failed_logins
     WHERE email_attempted = ? AND ip_address = ?
     ORDER BY attempt_time DESC LIMIT 1'
);
$lockStmt->execute([$email, $ip]);
$lockRow = $lockStmt->fetch();

if ($lockRow && $lockRow['attempt_count'] >= $maxAttempts) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Account locked due to too many failed attempts. Contact your administrator.'
    ]);
    exit;
}

// ── Fetch user ─────────────────────────────────────────────────
$stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// ── Verify password ────────────────────────────────────────────
if (!$user || !password_verify($pass, $user['password_hash'])) {

    // Log failed attempt
    $db->prepare(
        'INSERT INTO login_logs (user_id, ip_address, browser, device_type, os, status)
         VALUES (?, ?, ?, ?, ?, "failed")'
    )->execute([
        $user['id'] ?? null,
        $ip,
        $ua['browser'],
        $ua['device'],
        $ua['os']
    ]);

    // Upsert failed_logins counter
    $existing = $db->prepare(
        'SELECT id, attempt_count FROM failed_logins
         WHERE email_attempted = ? AND ip_address = ? ORDER BY attempt_time DESC LIMIT 1'
    );
    $existing->execute([$email, $ip]);
    $row = $existing->fetch();

    if ($row) {
        $db->prepare(
            'UPDATE failed_logins SET attempt_count = attempt_count + 1, attempt_time = NOW()
             WHERE id = ?'
        )->execute([$row['id']]);
    } else {
        $db->prepare(
            'INSERT INTO failed_logins (email_attempted, ip_address, browser, attempt_count)
             VALUES (?, ?, ?, 1)'
        )->execute([$email, $ip, $ua['browser']]);
    }

    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

// ── Check account status ───────────────────────────────────────
if ($user['status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Contact admin.']);
    exit;
}

// ── Create session ─────────────────────────────────────────────
startSession();
session_regenerate_id(true);

$_SESSION['user_id']       = $user['id'];
$_SESSION['role']          = $user['role'];
$_SESSION['name']          = $user['name'];
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token']    = bin2hex(random_bytes(32));

// ── Update last_login ──────────────────────────────────────────
$db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// ── Log success ────────────────────────────────────────────────
$db->prepare(
    'INSERT INTO login_logs (user_id, ip_address, browser, device_type, os, status)
     VALUES (?, ?, ?, ?, ?, "success")'
)->execute([$user['id'], $ip, $ua['browser'], $ua['device'], $ua['os']]);

logActivity($user['id'], 'login', null, 'User logged in from ' . $ip);

// ── Clear failed login counter on success ─────────────────────
$db->prepare(
    'DELETE FROM failed_logins WHERE email_attempted = ? AND ip_address = ?'
)->execute([$email, $ip]);

echo json_encode([
    'success'    => true,
    'message'    => 'Login successful.',
    'csrf_token' => $_SESSION['csrf_token'],
    'user' => [
        'id'         => $user['id'],
        'name'       => $user['name'],
        'email'      => $user['email'],
        'role'       => $user['role'],
        'department' => $user['department'],
    ]
]);