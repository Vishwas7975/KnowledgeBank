<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAuth();
$activePage = 'dashboard';
$userName   = $_SESSION['name'] ?? 'Employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>Employee Knowledge Portal — KnowledgeBank</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .welcome-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    color: #ffffff;
    margin-bottom: 28px;
    box-shadow: var(--shadow-royal);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .welcome-title { font-size: 1.45rem; font-weight: 700; margin-bottom: 6px; }
  .welcome-sub { font-size: 0.84rem; color: #93c5fd; }

  .employee-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
  }

  @media (max-width: 1024px) {
    .employee-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Employee Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-employee.php'; ?>

  <!-- Main Area -->
  <div class="main-area">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="topbar-title">Employee Portal</h1>
      </div>
      <div class="topbar-right">
        <div class="system-status-pill">
          <div class="pulse-dot"></div>
          <span>Online</span>
        </div>
      </div>
    </header>

    <div class="content-body">
      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div>
          <div class="welcome-title">Welcome back, <?= htmlspecialchars(explode(' ', $userName)[0]) ?> 👋</div>
          <div class="welcome-sub">Access enterprise documents, company policies, and submit download requests.</div>
        </div>
        <a href="/employee/documents.php" class="btn btn-primary" style="background:#ffffff; color:#1e40af;">Browse Library</a>
      </div>

      <!-- Main Grid -->
      <div class="employee-grid">

        <!-- Document Library Card -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              </svg>
              <span>Recent Shared Documents</span>
            </div>
            <a href="/employee/documents.php" class="btn btn-secondary btn-sm">View All Library</a>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Document</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                  </tr>
                </thead>
                <tbody id="empDocsTbody">
                  <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:var(--text-subtle);">Loading documents...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Right Side Panel: Quick Upload Dropzone -->
        <div style="display:flex; flex-direction:column; gap:24px;">
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                </svg>
                <span>Quick Upload</span>
              </div>
            </div>
            <div class="card-body">
              <div class="upload-dropzone" onclick="triggerQuickUpload()">
                <div class="dropzone-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                  </svg>
                </div>
                <div style="font-size:0.84rem; font-weight:600; color:var(--text-main); margin-bottom:4px;">Click to select file</div>
                <div style="font-size:0.72rem; color:var(--text-light);">Admin review required before publishing</div>
                <input type="file" id="quickFileInput" style="display:none;" onchange="handleQuickUpload(this.files[0])" />
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer-employee.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  loadEmployeeDocs();
});

async function loadEmployeeDocs() {
  try {
    const res = await fetch('/api/documents/list.php');
    const data = await res.json();
    const tbody = document.getElementById('empDocsTbody');

    if (!data.success || !data.documents || data.documents.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding:30px; color:var(--text-subtle);">No documents found in knowledge bank.</td></tr>`;
      return;
    }

    const approvedDocs = data.documents.filter(d => d.status === 'approved').slice(0, 6);

    tbody.innerHTML = approvedDocs.map(doc => `
      <tr>
        <td style="font-weight:600; color:var(--text-main);">${escapeHtml(doc.original_name)}</td>
        <td>${escapeHtml(doc.uploaded_by_name || 'Admin')}</td>
        <td style="font-size:0.78rem; color:var(--text-light);">${doc.upload_time}</td>
        <td style="text-align:right;">
          <button class="btn btn-secondary btn-sm" onclick="openDocumentPreview(${doc.id}, '${escapeHtml(doc.original_name)}', '${doc.file_type}')">Preview</button>
        </td>
      </tr>
    `).join('');
  } catch (err) {
    console.error('Failed to load documents', err);
  }
}

function triggerQuickUpload() {
  document.getElementById('quickFileInput').click();
}

async function handleQuickUpload(file) {
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  try {
    showToast('Uploading file...', 'info');
    const res = await fetchWithCsrf('/api/documents/upload.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message || 'Upload submitted for admin approval.', 'success');
    } else {
      showToast(data.message || 'Upload failed.', 'error');
    }
  } catch (err) {
    showToast('Upload error. Try again.', 'error');
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>