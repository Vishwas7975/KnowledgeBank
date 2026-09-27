<?php
// includes/topbar-admin.php — Reusable Topbar Header Component for Admin Portal
if (!isset($authUser)) {
    require_once __DIR__ . '/../config/auth.php';
    $authUser = getAuthUser();
}

$userName  = htmlspecialchars($authUser['name'] ?? 'Admin User');
$userEmail = htmlspecialchars($authUser['email'] ?? 'admin@knowledgebank.com');
$userRole  = ucfirst(htmlspecialchars($authUser['role'] ?? 'Admin'));
$userInit  = strtoupper(substr($userName, 0, 1));
$title     = htmlspecialchars($topbarTitle ?? 'Admin Dashboard');
?>
<header class="topbar">
  <div class="topbar-left">
    <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()" title="Toggle Sidebar">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    <h1 class="topbar-title"><?= $title ?></h1>
  </div>

  <div class="topbar-right">
    <?php if (isset($topbarActions)): ?>
      <div class="topbar-actions-group">
        <?= $topbarActions ?>
      </div>
    <?php endif; ?>

    <div class="system-status-pill">
      <div class="pulse-dot"></div>
      <span>System Active</span>
    </div>

    <!-- Topbar Circular Profile Avatar Dropdown -->
    <div class="topbar-profile-wrapper">
      <button class="topbar-avatar-btn" onclick="toggleProfileDropdown(event)" id="topbarAvatarBtn" title="<?= $userName ?>">
        <span class="avatar-circle"><?= $userInit ?></span>
      </button>

      <div class="topbar-profile-dropdown" id="topbarProfileDropdown">
        <div class="dropdown-header">
          <div class="dropdown-avatar-circle"><?= $userInit ?></div>
          <div class="dropdown-user-info">
            <div class="d-name"><?= $userName ?></div>
            <div class="d-email"><?= $userEmail ?></div>
            <div class="d-badge"><?= $userRole ?> Access</div>
          </div>
        </div>

        <div class="dropdown-divider"></div>

        <a href="/admin/profile.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Profile & Account</span>
        </a>

        <a href="/admin/settings.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
          </svg>
          <span>System Settings</span>
        </a>

        <div class="dropdown-divider"></div>

        <button class="dropdown-item danger" onclick="handleLogout()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Sign Out</span>
        </button>
      </div>
    </div>
  </div>
</header>

<script>
function toggleMobileSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (sidebar) sidebar.classList.toggle('mobile-open');
  if (overlay) overlay.classList.toggle('active');
}

function toggleProfileDropdown(e) {
  if (e) e.stopPropagation();
  const dropdown = document.getElementById('topbarProfileDropdown');
  if (dropdown) dropdown.classList.toggle('show');
}

document.addEventListener('click', function(e) {
  const dropdown = document.getElementById('topbarProfileDropdown');
  const avatarBtn = document.getElementById('topbarAvatarBtn');
  if (dropdown && dropdown.classList.contains('show')) {
    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  }
});
</script>
