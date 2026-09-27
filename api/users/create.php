<?php
// api/users/create.php — POST: create a new employee account (Admin only)

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

$data       = json_decode(file_get_contents('php://input'), true);
$name       = trim($data['name']        ?? '');
$employeeId = trim($data['employee_id'] ?? '');
$email      = trim($data['email']       ?? '');
$password   = trim($data['password']    ?? '');
$department = trim($data['department']  ?? '');
$role       = trim($data['role']        ?? 'employee');

// ── Validate required fields ───────────────────────────────────
if (!$name || !$employeeId || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, employee ID, email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (!preg_match('/^(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters with a number and a special character.'
    ]);
    exit;
}

if (!in_array($role, ['admin', 'employee'], true)) {
    $role = 'employee';
}

$db = getDB();

// ── Check duplicates ───────────────────────────────────────────
$check = $db->prepare("SELECT id FROM users WHERE email = ? OR employee_id = ? LIMIT 1");
$check->execute([$email, $employeeId]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email or Employee ID already exists.']);
    exit;
}

// ── Insert ─────────────────────────────────────────────────────
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

$stmt = $db->prepare("
    INSERT INTO users (name, employee_id, email, password_hash, role, department, status)
    VALUES (?, ?, ?, ?, ?, ?, 'active')
");
$stmt->execute([$name, $employeeId, $email, $hash, $role, $department]);
$newId = (int) $db->lastInsertId();

logActivity($authUser['id'], 'user_create', null, "Created user account: {$email}");

echo json_encode([
    'success' => true,
    'message' => 'Employee account created successfully.',
    'user'    => [
        'id'          => $newId,
        'name'        => $name,
        'employee_id' => $employeeId,
        'email'       => $email,
        'role'        => $role,
        'department'  => $department,
        'status'      => 'active',
    ]
]);