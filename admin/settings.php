<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>System Settings — KnowledgeBank Admin</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 24px;
  }

  .setting-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
  }

  .setting-item:last-child { border-bottom: none; }

  .setting-title { font-size: 0.84rem; font-weight: 600; color: var(--text-main); }
  .setting-sub   { font-size: 0.72rem; color: var(--text-subtle); margin-top: 2px; }

  .setting-field {
    width: 140px;
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.84rem;
    color: var(--text-main);
    outline: none;
    text-align: right;
  }

  .setting-field:focus { border-color: var(--royal); }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <div class="main-area">
    <!-- Topbar -->
    <?php
    $topbarTitle = 'System Settings & Configuration';
    $topbarActions = '
      <button class="btn btn-primary btn-sm" onclick="saveSettings()" style="margin-right:12px;">Save Changes</button>';
    include __DIR__ . '/../includes/topbar-admin.php';
    ?>

    <!-- Content Body -->
    <div class="content-body">

      <?php
      require_once __DIR__ . '/../config/settings.php';
      if (!defined('CLAMAV_ENABLED') || !CLAMAV_ENABLED):
      ?>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-left:4px solid #d97706; border-radius:12px; padding:14px 18px; margin-bottom:24px; color:#92400e; font-size:0.84rem; display:flex; align-items:center; gap:12px; box-shadow:var(--shadow-sm);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink:0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <div>
            <strong>Malware Scanning (ClamAV) is Disabled</strong> — <code>CLAMAV_ENABLED</code> is set to <code>false</code> in <code>config/settings.php</code>. Uploaded documents will not be scanned for viruses. Enable ClamAV if hosting on a VPS.
          </div>
        </div>
      <?php endif; ?>

      <div class="settings-grid">

        <!-- Storage & Upload Rules Card -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
              </svg>
              <span>Storage & File Rules</span>
            </div>
          </div>
          <div class="card-body">
            <div class="setting-item">
              <div>
                <div class="setting-title">Max Upload File Size</div>
                <div class="setting-sub">Maximum file size allowed per document (MB)</div>
              </div>
              <input type="number" id="max_upload_size_mb" class="setting-field" value="50" />
            </div>
          </div>
        </div>

        <!-- Session & Security Timeouts -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <span>Session & Rate Limits</span>
            </div>
          </div>
          <div class="card-body">
            <div class="setting-item">
              <div>
                <div class="setting-title">Employee Session Timeout</div>
                <div class="setting-sub">Inactivity timeout in seconds</div>
              </div>
              <input type="number" id="employee_session_timeout" class="setting-field" value="600" />
            </div>

            <div class="setting-item">
              <div>
                <div class="setting-title">Admin Session Timeout</div>
                <div class="setting-sub">Inactivity timeout in seconds</div>
              </div>
              <input type="number" id="admin_session_timeout" class="setting-field" value="1800" />
            </div>

            <div class="setting-item">
              <div>
                <div class="setting-title">Max Failed Login Attempts</div>
                <div class="setting-sub">Lock account after N failures</div>
              </div>
              <input type="number" id="max_failed_login_attempts" class="setting-field" value="5" />
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  loadSettings();
});

async function loadSettings() {
  try {
    const res = await fetch('/api/settings/get.php');
    const data = await res.json();
    if (data.success && data.settings) {
      if (data.settings.max_upload_size_mb) document.getElementById('max_upload_size_mb').value = data.settings.max_upload_size_mb;
      if (data.settings.employee_session_timeout) document.getElementById('employee_session_timeout').value = data.settings.employee_session_timeout;
      if (data.settings.admin_session_timeout) document.getElementById('admin_session_timeout').value = data.settings.admin_session_timeout;
      if (data.settings.max_failed_login_attempts) document.getElementById('max_failed_login_attempts').value = data.settings.max_failed_login_attempts;
    }
  } catch (err) {
    showToast('Failed to fetch settings', 'error');
  }
}

async function saveSettings() {
  const settings = {
    max_upload_size_mb: document.getElementById('max_upload_size_mb').value,
    employee_session_timeout: document.getElementById('employee_session_timeout').value,
    admin_session_timeout: document.getElementById('admin_session_timeout').value,
    max_failed_login_attempts: document.getElementById('max_failed_login_attempts').value
  };

  try {
    const res = await fetchWithCsrf('/api/settings/update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ settings })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Settings saved successfully!', 'success');
    } else {
      showToast(data.message || 'Failed to update settings.', 'error');
    }
  } catch (err) {
    showToast('Error saving settings.', 'error');
  }
}
</script>

</body>
</html>