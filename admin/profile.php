<?php
// admin/profile.php — Admin Profile & Account Settings Page
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';

$authUser   = requireAdmin();
$activePage = 'profile';
$topbarTitle = 'Admin Profile & Account';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$authUser['id']]);
$user = $stmt->fetch();

$userName     = htmlspecialchars($user['name'] ?? 'Admin User');
$userEmail    = htmlspecialchars($user['email'] ?? 'admin@example.com');
$userEmpId    = htmlspecialchars($user['employee_id'] ?? 'VAI-AI-0002');
$userRole     = ucfirst(htmlspecialchars($user['role'] ?? 'admin'));
$userDept     = htmlspecialchars($user['department'] ?? 'Admin');
$userCreatedAt= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A';
$userLastLogin= !empty($user['last_login']) ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Currently Active';
$userInit     = strtoupper(substr($userName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>Admin Profile — KnowledgeBank Admin</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .profile-grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 24px;
  }

  @media (max-width: 900px) {
    .profile-grid {
      grid-template-columns: 1fr;
    }
  }

  .profile-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
    padding: 28px 24px;
    text-align: center;
  }

  .profile-avatar-large {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: 2.2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
  }

  .profile-name { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
  .profile-role-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #2563eb;
    background: #dbeafe;
    padding: 4px 12px;
    border-radius: 99px;
    margin-bottom: 20px;
    text-transform: uppercase;
  }

  .info-list {
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 14px;
    border-top: 1px solid var(--border-light);
    padding-top: 20px;
  }

  .info-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.84rem;
  }

  .info-label { color: #64748b; font-weight: 500; }
  .info-val { color: #0f172a; font-weight: 600; text-align: right; }

  .password-form-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-sm);
    padding: 28px 28px;
  }

  .card-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
  }

  .card-section-sub {
    font-size: 0.82rem;
    color: #64748b;
    margin-bottom: 24px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
  }

  .input-password-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-password-wrap input {
    width: 100%;
    padding: 10px 40px 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: var(--radius);
    font-size: 0.88rem;
    transition: all 0.2s;
  }

  .input-password-wrap input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    outline: none;
  }

  .eye-toggle-btn {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
  }

  .eye-toggle-btn:hover {
    color: #2563eb;
  }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <!-- Main Content Area -->
  <div class="main-area">
    <!-- Top Bar -->
    <?php include __DIR__ . '/../includes/topbar-admin.php'; ?>

    <!-- Content Body -->
    <div class="content-body">
      <div class="profile-grid">
        
        <!-- Left: Profile Summary Card -->
        <div class="profile-card">
          <div class="profile-avatar-large"><?= $userInit ?></div>
          <div class="profile-name"><?= $userName ?></div>
          <div class="profile-role-badge">Executive Administrator</div>

          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Employee ID</span>
              <span class="info-val"><?= $userEmpId ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Email Address</span>
              <span class="info-val"><?= $userEmail ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Department</span>
              <span class="info-val"><?= $userDept ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Role Access</span>
              <span class="info-val"><?= $userRole ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Member Since</span>
              <span class="info-val"><?= $userCreatedAt ?></span>
            </div>
            <div class="info-item">
              <span class="info-label">Last Login</span>
              <span class="info-val"><?= $userLastLogin ?></span>
            </div>
          </div>
        </div>

        <!-- Right: Change Password Form -->
        <div class="password-form-card">
          <div class="card-section-title">Change Account Password</div>
          <div class="card-section-sub">Update your security credentials to maintain enterprise access protection.</div>

          <form id="changePasswordForm" onsubmit="handleChangePassword(event)">
            <div class="form-group">
              <label class="form-label" for="current_password">Current Password</label>
              <div class="input-password-wrap">
                <input type="password" id="current_password" required placeholder="Enter current password" />
                <button type="button" class="eye-toggle-btn" onclick="togglePasswordVisibility('current_password', this)">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="new_password">New Password</label>
              <div class="input-password-wrap">
                <input type="password" id="new_password" required placeholder="Enter new password (min. 6 characters)" />
                <button type="button" class="eye-toggle-btn" onclick="togglePasswordVisibility('new_password', this)">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="confirm_password">Confirm New Password</label>
              <div class="input-password-wrap">
                <input type="password" id="confirm_password" required placeholder="Re-enter new password" />
                <button type="button" class="eye-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <div style="margin-top:28px; display:flex; justify-content:flex-end;">
              <button type="submit" class="btn btn-primary" id="savePasswordBtn">
                <span>Update Password</span>
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script src="/assets/js/app.js"></script>
<script>
function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
  } else {
    input.type = 'password';
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
  }
}

async function handleChangePassword(e) {
  e.preventDefault();
  const current_password = document.getElementById('current_password').value.trim();
  const new_password     = document.getElementById('new_password').value.trim();
  const confirm_password = document.getElementById('confirm_password').value.trim();
  const btn = document.getElementById('savePasswordBtn');

  if (new_password !== confirm_password) {
    showToast('New passwords do not match.', 'error');
    return;
  }

  if (new_password.length < 6) {
    showToast('New password must be at least 6 characters.', 'error');
    return;
  }

  btn.disabled = true;
  btn.innerText = 'Updating...';

  try {
    const res = await fetchWithCsrf('/api/users/change-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ current_password, new_password, confirm_password })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || 'Password updated successfully!', 'success');
      document.getElementById('changePasswordForm').reset();
    } else {
      showToast(data.message || 'Failed to update password.', 'error');
    }
  } catch (err) {
    showToast('Network error occurred.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<span>Update Password</span>';
  }
}

function handleLogout() {
  window.location.href = '/api/auth/logout.php';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
