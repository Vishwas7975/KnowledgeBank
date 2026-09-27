<?php
// api/settings/get.php — GET: return all system settings (Admin only)
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

requireAdmin();

$db   = getDB();
$stmt = $db->query('SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key');
$rows = $stmt->fetchAll();

$settings = [];
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

echo json_encode(['success' => true, 'settings' => $settings]);