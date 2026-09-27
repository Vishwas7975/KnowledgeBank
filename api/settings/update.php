<?php
// api/settings/update.php — POST: update system settings (Admin only)
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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$allowed = ['app_name', 'admin_session_timeout', 'employee_session_timeout', 'max_failed_login_attempts', 'max_upload_size_mb'];

// Validate each key
$errors = [];
foreach ($allowed as $key) {
    if (!isset($data[$key])) continue;
    $val = trim((string) $data[$key]);

    switch ($key) {
        case 'app_name':
            if (empty($val) || strlen($val) > 100) {
                $errors[] = 'App name must be between 1 and 100 characters.';
            }
            break;
        case 'admin_session_timeout':
        case 'employee_session_timeout':
            $n = (int) $val;
            if ($n < 60 || $n > 86400) {
                $errors[] = ucwords(str_replace('_', ' ', $key)) . ' must be between 60 and 86400 seconds.';
            }
            break;
        case 'max_failed_login_attempts':
            $n = (int) $val;
            if ($n < 3 || $n > 10) {
                $errors[] = 'Max failed login attempts must be between 3 and 10.';
            }
            break;
        case 'max_upload_size_mb':
            $n = (int) $val;
            if ($n < 1 || $n > 500) {
                $errors[] = 'Max upload size must be between 1 and 500 MB.';
            }
            break;
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$db   = getDB();
$stmt = $db->prepare(
    'INSERT INTO system_settings (setting_key, setting_value, updated_by)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)'
);

foreach ($allowed as $key) {
    if (!isset($data[$key])) continue;
    $val = trim((string) $data[$key]);
    $stmt->execute([$key, $val, $authUser['id']]);
}

logActivity($authUser['id'], 'user_update', null, 'Admin updated system settings.');

echo json_encode(['success' => true, 'message' => 'Settings saved successfully.']);