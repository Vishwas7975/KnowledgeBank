<?php
// includes/sidebar-employee.php — Employee Sidebar Navigation Component
if (!isset($activePage)) {
    $activePage = 'dashboard';
}

$userName  = htmlspecialchars($authUser['name'] ?? 'Employee');
$userRole  = htmlspecialchars($authUser['department'] ?? 'Employee');
$userInit  = strtoupper(substr($userName, 0, 1));
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="/assets/images/logo.png" alt="KnowledgeBank Logo" class="brand-company-logo" />
    <span style="font-size:1.15rem; font-weight:800; color:#0f172a; letter-spacing:-0.4px;">KnowledgeBank <span style="color:#2563eb;">AI</span></span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Workspace</div>

    <a href="/employee/dashboard.php" class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="7" height="9"/>
        <rect x="14" y="3" width="7" height="5"/>
        <rect x="14" y="12" width="7" height="9"/>
        <rect x="3" y="16" width="7" height="5"/>
      </svg>
      <span>Dashboard</span>
    </a>

    <a href="/employee/documents.php" class="nav-item <?= ($activePage === 'documents') ? 'active' : '' ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
      </svg>
      <span>Document Library</span>
    </a>

    <a href="/employee/my-uploads.php" class="nav-item <?= ($activePage === 'my-uploads') ? 'active' : '' ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/>
        <line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      <span>My Uploads</span>
    </a>

    <div class="nav-section-title" style="margin-top:12px;">Account</div>

    <a href="/employee/change-password.php" class="nav-item <?= ($activePage === 'change-password') ? 'active' : '' ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      <span>Security</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-profile-card">
      <div class="user-avatar"><?= $userInit ?></div>
      <div class="user-details">
        <div class="u-name"><?= $userName ?></div>
        <div class="u-role"><?= $userRole ?></div>
      </div>
      <button class="logout-btn-icon" title="Logout" onclick="handleLogout()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>
    </div>
  </div>
</aside>