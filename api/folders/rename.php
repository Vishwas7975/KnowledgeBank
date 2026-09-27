<?php
// api/folders/rename.php — POST: rename an existing folder

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAdmin(); // only admins can rename folders
validateCsrf();
$db       = getDB();

$input = json_decode(file_get_contents('php://input'), true);
$id    = isset($input['id'])   ? (int)trim($input['id'])       : 0;
$name  = isset($input['name']) ? trim($input['name'])          : '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder ID is required.']);
    exit;
}

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

// Confirm folder exists
$exists = $db->prepare("SELECT id, name FROM folders WHERE id = ? AND is_deleted = 0");
$exists->execute([$id]);
$folder = $exists->fetch();
if (!$folder) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Folder not found.']);
    exit;
}

// Check for duplicate name (case-insensitive), excluding current folder
$check = $db->prepare("SELECT id FROM folders WHERE LOWER(name) = LOWER(?) AND is_deleted = 0 AND id != ?");
$check->execute([$name, $id]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => "A folder named \"{$name}\" already exists."]);
    exit;
}

$stmt = $db->prepare("UPDATE folders SET name = ? WHERE id = ?");
$stmt->execute([$name, $id]);

if (function_exists('logActivity')) {
    try {
        logActivity($authUser['id'], 'rename_folder', $id, "Renamed folder from \"{$folder['name']}\" to \"{$name}\"");
    } catch (\Throwable $e) {
        error_log('logActivity failed in folders/rename.php: ' . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Folder renamed successfully.',
    'folder'  => ['id' => $id, 'name' => $name],
]);