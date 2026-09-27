<?php
// api/folders/create.php — POST: create a new folder

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();
$db       = getDB();

$input = json_decode(file_get_contents('php://input'), true);
$name  = trim($input['name'] ?? '');
$isConfidential = !empty($input['is_confidential']) ? 1 : 0;
$folderPassword = $input['folder_password'] ?? '';

if ($isConfidential && strlen($folderPassword) < 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Confidential folders require a password of at least 4 characters.']);
    exit;
}
$passwordHash = $isConfidential ? password_hash($folderPassword, PASSWORD_BCRYPT) : null;

if ($name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder name is required.']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9 _\-().]+$/', $name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder name contains invalid characters. Use letters, numbers, spaces, hyphens, underscores, or parentheses.']);
    exit;
}

if (strlen($name) > 120) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder name must be 120 characters or fewer.']);
    exit;
}

// Check for duplicate name (case-insensitive), excluding soft-deleted folders
$check = $db->prepare("SELECT id FROM folders WHERE LOWER(name) = LOWER(?) AND is_deleted = 0");
$check->execute([$name]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => "A folder named \"{$name}\" already exists. Please choose a different name."]);
    exit;
}

$stmt = $db->prepare("INSERT INTO folders (name, created_by, created_at, is_confidential, folder_password_hash) VALUES (?, ?, NOW(), ?, ?)");
$stmt->execute([$name, $authUser['id'], $isConfidential, $passwordHash]);
$folderId = (int) $db->lastInsertId();

// Log activity — wrapped in try/catch so a schema mismatch never breaks the response
if (function_exists('logActivity')) {
    try {
        logActivity($authUser['id'], 'create_folder', $folderId, "Created folder: {$name}");
    } catch (\Throwable $e) {
        // Activity log failed (e.g. enum not yet migrated) — folder was still created successfully
        error_log('logActivity failed in folders/create.php: ' . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Folder created successfully.',
    'folder'  => [
        'id'         => $folderId,
        'name'       => $name,
        'created_at' => date('Y-m-d H:i:s'),
    ]
]);