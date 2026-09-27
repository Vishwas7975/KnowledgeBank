<?php
// config/auth.php — session validation helpers

require_once __DIR__ . '/db.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Detect HTTPS: true on production (Hostinger), false on localhost
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                 || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
                 || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $isSecure,   
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function requireAuth(): array {
    startSession();

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorised. Please log in.']);
        exit;
    }

    $timeout = (int) getSystemSetting(
        $_SESSION['role'] === 'admin' ? 'admin_session_timeout' : 'employee_session_timeout',
        1800
    );

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
        exit;
    }

    $_SESSION['last_activity'] = time();

    return [
        'id'   => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'],
    ];
}

function requireAdmin(): array {
    $user = requireAuth();

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Forbidden. Admin access required.']);
        exit;
    }

    return $user;
}

function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrf(): void {
    startSession();
    $token = $_POST['csrf_token']
          ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (empty($token)) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input) && !empty($input['csrf_token'])) {
            $token = (string)$input['csrf_token'];
        }
    }

    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token.']);
        exit;
    }
}

function getSystemSetting(string $key, $default = null) {
    $db   = getDB();
    $stmt = $db->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function getClientIp(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';
}

function parseUserAgent(): array {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $browser = 'Unknown';
    $os      = 'Unknown';
    $device  = 'Desktop';

    if (preg_match('/Edg\//i',      $ua)) $browser = 'Edge';
    elseif (preg_match('/OPR\//i',  $ua)) $browser = 'Opera';
    elseif (preg_match('/Chrome/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/Firefox/i',$ua)) $browser = 'Firefox';
    elseif (preg_match('/Safari/i', $ua)) $browser = 'Safari';

    if (preg_match('/Windows/i',    $ua)) $os = 'Windows';
    elseif (preg_match('/Mac OS/i', $ua)) $os = 'macOS';
    elseif (preg_match('/Linux/i',  $ua)) $os = 'Linux';
    elseif (preg_match('/Android/i',$ua)) $os = 'Android';
    elseif (preg_match('/iOS|iPhone|iPad/i', $ua)) $os = 'iOS';

    if (preg_match('/Mobi|Android/i',  $ua)) $device = 'Mobile';
    elseif (preg_match('/Tablet|iPad/i',$ua)) $device = 'Tablet';

    return compact('browser', 'os', 'device');
}

function logActivity(int $userId, string $actionType, ?int $documentId, string $description): void {
    $db   = getDB();
    $stmt = $db->prepare(
        'INSERT INTO activity_logs (user_id, action_type, document_id, description, ip_address)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $actionType, $documentId, $description, getClientIp()]);
}