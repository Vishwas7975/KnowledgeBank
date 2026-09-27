<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>Admin Dashboard — KnowledgeBank</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
  }

  .metric-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    padding: 22px 24px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }

  .metric-info .m-label {
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .metric-info .m-value {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--text-main);
    margin: 4px 0 2px;
    letter-spacing: -0.5px;
  }

  .metric-info .m-sub {
    font-size: 0.72rem;
    color: var(--text-subtle);
  }

  .metric-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .metric-icon.blue   { background: var(--blue-bg); color: var(--royal); }
  .metric-icon.amber  { background: var(--amber-bg); color: var(--amber); }
  .metric-icon.purple { background: var(--purple-bg); color: var(--accent-purple); }
  .metric-icon.green  { background: var(--green-bg); color: var(--green); }

  .dashboard-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 340px);
    gap: 24px;
  }

  @media (max-width: 1100px) {
    .dashboard-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 992px) {
    .metrics-grid {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 12px !important;
      margin-bottom: 20px !important;
    }

    .metric-card {
      padding: 16px 14px !important;
      box-sizing: border-box !important;
      width: 100% !important;
      max-width: 100% !important;
    }

    .metric-info .m-label { font-size: 0.68rem !important; }
    .metric-info .m-value { font-size: 1.35rem !important; }
    .metric-icon { width: 40px !important; height: 40px !important; }
  }

  @media (max-width: 600px) {
    .metrics-grid {
      grid-template-columns: 1fr !important;
      gap: 10px !important;
    }
  }

  .user-activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
  }

  .user-activity-item:last-child { border-bottom: none; }
  .user-activity-item:hover { background: var(--bg-subtle); }

  .user-activity-item .user-avatar {
    width: 34px !important;
    height: 34px !important;
    min-width: 34px !important;
    min-height: 34px !important;
    border-radius: 50% !important;
    flex-shrink: 0 !important;
  }

  .user-activity-item > div:nth-child(2) {
    flex: 1;
    min-width: 0;
  }

  .user-activity-item .u-name { font-size: 0.84rem; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .user-activity-item .u-dept { font-size: 0.72rem; color: var(--text-light); word-break: break-word; line-height: 1.35; }
  .user-activity-item .u-time { font-size: 0.72rem; color: var(--green-text); font-weight: 600; margin-left: auto; flex-shrink: 0; }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <!-- Main Content Area -->
  <div class="main-area">
    <!-- Top Bar -->
    <?php
    $topbarTitle = 'Admin Dashboard';
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

      <!-- Metrics Row -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-info">
            <div class="m-label">Total Documents</div>
            <div class="m-value" id="statDocs">—</div>
            <div class="m-sub">In knowledge bank</div>
          </div>
          <div class="metric-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <div class="m-label">Pending Approval</div>
            <div class="m-value" id="statPending">—</div>
            <div class="m-sub">Requires admin review</div>
          </div>
          <div class="metric-icon amber">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <div class="m-label">Storage Used</div>
            <div class="m-value" id="statStorage">—</div>
            <div class="m-sub">Of total space</div>
          </div>
          <div class="metric-icon purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-info">
            <div class="m-label">Active Users</div>
            <div class="m-value" id="statUsers">—</div>
            <div class="m-sub">Registered accounts</div>
          </div>
          <div class="metric-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Dashboard Grid -->
      <div class="dashboard-grid">

        <!-- Left: Recent Documents Table -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              </svg>
              <span>Recent Document Uploads</span>
            </div>
            <a href="/admin/documents.php" class="btn btn-secondary btn-sm">View All</a>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-responsive" style="width:100%; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
              <table class="table" style="width:100%;">
                <thead class="desktop-row">
                  <tr>
                    <th style="min-width:160px;">Document Name</th>
                    <th style="min-width:110px;">Uploaded By</th>
                    <th style="min-width:110px;">Date</th>
                    <th style="min-width:90px;">Status</th>
                    <th style="min-width:90px; text-align:right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="recentDocsTbody">
                  <tr>
                    <td colspan="5" style="text-align:center; padding:30px; color:var(--text-subtle);">Loading recent uploads...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right: Recent Activity / Online Users -->
        <div style="display:flex; flex-direction:column; gap:24px;">
          <div class="card" style="width:100%; max-width:100%; overflow:hidden;">
            <div class="card-header">
              <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                </svg>
                <span>Active Users</span>
              </div>
            </div>
            <div class="card-body" style="padding:0;" id="activeUsersList">
              <div style="padding:20px; text-align:center; color:var(--text-subtle); font-size:0.8rem;">Loading active users...</div>
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
  loadDashboardStats();
  loadRecentUploads();
  loadActiveUsers();
});

async function loadDashboardStats() {
  try {
    const res = await fetch('/api/dashboard/stats.php');
    const data = await res.json();
    if (data.success && data.stats) {
      document.getElementById('statDocs').textContent = data.stats.total_documents || 0;
      document.getElementById('statPending').textContent = data.stats.pending_approvals || 0;
      document.getElementById('statStorage').textContent = data.stats.total_storage || '0 B';
      document.getElementById('statUsers').textContent = data.stats.total_users || 0;
    }
  } catch (err) {
    console.error('Failed to load stats', err);
  }
}

async function loadRecentUploads() {
  try {
    const res = await fetch('/api/dashboard/recent-uploads.php');
    const data = await res.json();
    const tbody = document.getElementById('recentDocsTbody');

    if (!data.success || !data.documents || data.documents.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-subtle);">No recent document uploads found.</td></tr>`;
      return;
    }

    tbody.innerHTML = data.documents.map(doc => {
      const statusBadge = doc.status === 'approved' 
        ? `<span class="badge badge-approved">Approved</span>`
        : (doc.status === 'rejected' ? `<span class="badge badge-rejected">Rejected</span>` : `<span class="badge badge-pending">Pending</span>`);

      return `
        <tr class="desktop-row">
          <td style="font-weight:600; color:var(--text-main); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(doc.original_name)}">${escapeHtml(doc.original_name)}</td>
          <td style="white-space:nowrap; padding:12px 14px;">${escapeHtml(doc.uploaded_by_name || 'System')}</td>
          <td style="font-size:0.78rem; color:var(--text-light); white-space:nowrap; padding:12px 14px;">${doc.upload_time}</td>
          <td style="white-space:nowrap; padding:12px 14px;">${statusBadge}</td>
          <td style="text-align:right; white-space:nowrap; padding:12px 14px;">
            <button class="btn btn-secondary btn-sm" onclick="handleDashboardPreviewDoc(${doc.id})">Preview</button>
          </td>
        </tr>
        <tr class="mobile-row" style="display:none;">
          <td colspan="5" style="padding:14px; border-bottom:1px solid #e2e8f0; background:#ffffff;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;">
              <div style="font-size:0.86rem; font-weight:700; color:#0f172a; word-break:break-all;">${escapeHtml(doc.original_name)}</div>
              <div style="flex-shrink:0;">${statusBadge}</div>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
              <div style="font-size:0.75rem; color:#64748b;">${escapeHtml(doc.uploaded_by_name || 'System')} • ${doc.upload_time}</div>
              <button class="btn btn-secondary btn-sm" onclick="handleDashboardPreviewDoc(${doc.id})" style="padding:5px 12px; font-size:0.75rem;">Preview</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
    window.recentDashboardDocs = data.documents;
  } catch (err) {
    console.error('Failed to load recent uploads', err);
  }
}

function handleDashboardPreviewDoc(docId) {
  const docs = window.recentDashboardDocs || [];
  const doc = docs.find(d => Number(d.id) === Number(docId));
  if (doc) {
    openDocumentPreview(doc.id, doc.original_name, doc.file_type || '');
  }
}

async function loadActiveUsers() {
  try {
    const res = await fetch('/api/dashboard/recent-activity.php');
    const data = await res.json();
    const container = document.getElementById('activeUsersList');

    if (!data.success || !data.activity || data.activity.length === 0) {
      container.innerHTML = `<div style="padding:20px; text-align:center; color:var(--text-subtle); font-size:0.8rem;">No recent user activity.</div>`;
      return;
    }

    container.innerHTML = data.activity.slice(0, 5).map(item => `
      <div class="user-activity-item" style="display:flex; align-items:flex-start; gap:12px; padding:14px; width:100%; box-sizing:border-box; border-bottom:1px solid #f1f5f9;">
        <div class="user-avatar" style="width:36px; height:36px; min-width:36px; min-height:36px; border-radius:50%; flex-shrink:0;">${(item.user_name || 'U').charAt(0).toUpperCase()}</div>
        <div style="flex:1; min-width:0;">
          <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:4px;">
            <div class="u-name" style="font-size:0.86rem; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(item.user_name || 'User')}</div>
            <div class="u-time" style="font-size:0.74rem; color:#10b981; font-weight:600; flex-shrink:0;">${item.time_ago || ''}</div>
          </div>
          <div class="u-dept" style="font-size:0.75rem; color:#64748b; word-break:break-all; line-height:1.4;">${escapeHtml(item.description || 'Logged in')}</div>
        </div>
      </div>
    `).join('');
  } catch (err) {
    console.error('Failed to load activity', err);
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>