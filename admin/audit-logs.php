<?php
// admin/audit-logs.php — Audit Logs & Security Event Monitoring
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'audit-logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Audit Logs — KnowledgeBank Admin</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .log-table {
    width: 100%;
    table-layout: auto;
  }
  .log-table th, .log-table td {
    vertical-align: middle;
  }
  .log-desc-col {
    word-break: break-word;
    max-width: 420px;
    line-height: 1.4;
  }
  .ip-address-col {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.76rem;
    color: #475569;
    word-break: break-all;
    white-space: normal;
  }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <div class="main-area">
    <!-- Topbar -->
    <?php
    $topbarTitle = 'Audit & System Activity Logs';
    $topbarActions = '
      <button class="btn btn-secondary btn-sm" onclick="exportLogsToCSV()" style="margin-right:12px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        <span>Export CSV</span>
      </button>';
    include __DIR__ . '/../includes/topbar-admin.php';
    ?>

    <!-- Content Body -->
    <div class="content-body">

      <!-- Toolbar Row -->
      <div class="toolbar-row">
        <div class="search-input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="logSearchInput" placeholder="Filter logs by description, user, or IP..." oninput="filterLogs()" />
        </div>

        <div style="display:flex; gap:12px;">
          <select id="actionTypeFilter" class="custom-select" onchange="filterLogs()">
            <option value="all">All Event Types</option>
            <option value="login">Logins</option>
            <option value="upload">Uploads</option>
            <option value="download">Downloads</option>
            <option value="delete">Deletions</option>
            <option value="user_create">User Accounts</option>
          </select>
        </div>
      </div>

      <!-- Logs Table Card -->
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table log-table">
              <thead>
                <tr>
                  <th style="min-width:140px;">TIMESTAMP</th>
                  <th style="min-width:140px;">USER</th>
                  <th style="min-width:110px;">ACTION TYPE</th>
                  <th>DESCRIPTION</th>
                  <th style="min-width:200px; text-align:right;">IP ADDRESS</th>
                </tr>
              </thead>
              <tbody id="logsTbody">
                <tr>
                  <td colspan="5" style="text-align:center; padding:40px; color:var(--text-subtle);">Loading system audit trail...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div id="toast-container"></div>

<script src="/assets/js/app.js"></script>
<script>
let allLogs = [];

document.addEventListener('DOMContentLoaded', () => {
  fetchLogs();
});

async function fetchLogs() {
  try {
    const res = await fetch('/api/audit/logs.php');
    const data = await res.json();
    if (data.success && data.logs) {
      allLogs = data.logs;
      renderLogs(allLogs);
    }
  } catch (err) {
    showToast('Failed to load audit logs', 'error');
  }
}

function renderLogs(logs) {
  const tbody = document.getElementById('logsTbody');

  if (!logs || logs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-subtle);">No activity logs recorded.</td></tr>`;
    return;
  }

  tbody.innerHTML = logs.map(log => {
    let actionBadge = `<span class="badge badge-blue">${log.action_type || 'system'}</span>`;
    if (log.action_type === 'upload') actionBadge = `<span class="badge badge-approved">Upload</span>`;
    if (log.action_type === 'delete') actionBadge = `<span class="badge badge-rejected">Delete</span>`;
    if (log.action_type === 'download') actionBadge = `<span class="badge badge-purple">Download</span>`;
    if (log.action_type === 'login') actionBadge = `<span class="badge badge-blue">Login</span>`;

    return `
      <tr>
        <td style="font-size:0.78rem; color:var(--text-light); font-weight:500; white-space:nowrap;">${log.created_at || ''}</td>
        <td style="font-weight:600; color:var(--text-main);">${escapeHtml(log.user_name || 'System')}</td>
        <td>${actionBadge}</td>
        <td class="log-desc-col">${escapeHtml(log.description || '')}</td>
        <td class="ip-address-col" style="text-align:right;">${escapeHtml(log.ip_address || '—')}</td>
      </tr>
    `;
  }).join('');
}

function filterLogs() {
  const query = document.getElementById('logSearchInput').value.toLowerCase();
  const actionType = document.getElementById('actionTypeFilter').value;

  const filtered = allLogs.filter(log => {
    const matchesQuery = (log.description && log.description.toLowerCase().includes(query)) ||
                         (log.user_name && log.user_name.toLowerCase().includes(query)) ||
                         (log.ip_address && log.ip_address.toLowerCase().includes(query));
    const matchesAction = actionType === 'all' || log.action_type === actionType;
    return matchesQuery && matchesAction;
  });

  renderLogs(filtered);
}

function exportLogsToCSV() {
  if (!allLogs || allLogs.length === 0) {
    showToast('No logs to export', 'warning');
    return;
  }

  const headers = ['ID', 'User', 'Action', 'Description', 'IP Address', 'Timestamp'];
  const rows = allLogs.map(l => [
    l.id,
    `"${(l.user_name || '').replace(/"/g, '""')}"`,
    `"${(l.action_type || '').replace(/"/g, '""')}"`,
    `"${(l.description || '').replace(/"/g, '""')}"`,
    `"${(l.ip_address || '').replace(/"/g, '""')}"`,
    `"${(l.created_at || '').replace(/"/g, '""')}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `audit_logs_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function handleLogout() {
  window.location.href = '/api/auth/logout.php';
}
</script>
</body>
</html>