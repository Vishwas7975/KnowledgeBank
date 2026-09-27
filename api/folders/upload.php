<?php
// api/folders/upload.php — POST: upload file(s) into a specific folder

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$authUser = requireAuth();
validateCsrf();
$db       = getDB();

// ── Validate folder ────────────────────────────────────────────
$folderId = (int) ($_POST['folder_id'] ?? 0);
if ($folderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'folder_id is required.']);
    exit;
}

$folderStmt = $db->prepare("SELECT id, name FROM folders WHERE id = ? AND is_deleted = 0");
$folderStmt->execute([$folderId]);
$folder = $folderStmt->fetch();

if (!$folder) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Folder not found.']);
    exit;
}

// ── Check file was sent ────────────────────────────────────────
if (empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
    ];
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $errors[$file['error']] ?? 'Unknown upload error.'
    ]);
    exit;
}

// ── File size check ────────────────────────────────────────────
$maxMb    = (int) getSystemSetting('max_upload_size_mb', 50);
$maxBytes = $maxMb * 1024 * 1024;

if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "File too large. Maximum allowed size is {$maxMb}MB."
    ]);
    exit;
}

// ── Extension check ────────────────────────────────────────────
$originalName = $file['name'];
$ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "File type '.{$ext}' is not allowed."
    ]);
    exit;
}

// ── MIME type check ────────────────────────────────────────────
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!array_key_exists($mimeType, ALLOWED_MIMES)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "Invalid file content type: {$mimeType}."
    ]);
    exit;
}

// ── Malware scan (ClamAV with graceful fallback) ───────────────
$scanResult = scanFile($file['tmp_name']);
if (!$scanResult['clean']) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => $scanResult['unavailable']
            ? 'Malware scanning is currently unavailable. Upload blocked for safety. Contact administrator.'
            : 'File failed malware scan and was rejected.',
        'detail'  => $scanResult['reason'] ?? ''
    ]);
    exit;
}

// ── Save file ──────────────────────────────────────────────────
$storedName  = sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(8)), $ext);
$storagePath = STORAGE_PATH;

if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
}

$destination = $storagePath . $storedName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save file on server.']);
    exit;
}

// ── Insert DB record with folder_id ───────────────────────────
$stmt = $db->prepare("
    INSERT INTO documents
        (original_name, stored_name, file_path, file_type, file_size, uploaded_by, folder_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $originalName,
    $storedName,
    $storedName,
    $mimeType,
    $file['size'],
    $authUser['id'],
    $folderId,
]);

$docId = (int) $db->lastInsertId();

if (function_exists('logActivity')) {
    logActivity($authUser['id'], 'upload', $docId, "Uploaded file to folder \"{$folder['name']}\": {$originalName}");
}

echo json_encode([
    'success'  => true,
    'message'  => 'File uploaded successfully.',
    'document' => [
        'id'            => $docId,
        'original_name' => $originalName,
        'file_type'     => $mimeType,
        'file_size'     => $file['size'],
        'folder_id'     => $folderId,
        'folder_name'   => $folder['name'],
        'upload_time'   => date('Y-m-d H:i:s'),
    ]
]);