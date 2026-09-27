<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAuth();
$activePage = 'documents';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document Library — KnowledgeBank</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="app-shell">
  <!-- Employee Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-employee.php'; ?>

  <div class="main-area">
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="topbar-title">Document Library</h1>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary" onclick="openUploadModal()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          <span>Upload File</span>
        </button>
      </div>
    </header>

    <!-- Content Body -->
    <div class="content-body">

      <!-- Toolbar Row -->
      <div class="toolbar-row">
        <div class="search-input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="searchInput" placeholder="Search shared documents..." oninput="filterDocuments()" />
        </div>
      </div>

      <!-- Documents Table Card -->
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Document Name</th>
                  <th>Uploaded By</th>
                  <th>Size</th>
                  <th>Date</th>
                  <th style="text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody id="documentsTbody">
                <tr>
                  <td colspan="5" style="text-align:center; padding:40px; color:var(--text-subtle);">Loading document repository...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal-overlay">
  <div class="modal-container" style="max-width:540px;">
    <div class="modal-header">
      <div class="modal-title">Upload New Document</div>
      <button class="modal-close" onclick="closeUploadModal()">✕</button>
    </div>
    <div class="modal-body">
      <form id="uploadForm" onsubmit="handleUploadSubmit(event)">
        <div class="upload-dropzone" onclick="document.getElementById('fileInput').click()">
          <div class="dropzone-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
          </div>
          <div style="font-size:0.9rem; font-weight:600; color:var(--text-main); margin-bottom:4px;" id="selectedFileName">Choose a file or drag & drop</div>
          <div style="font-size:0.75rem; color:var(--text-subtle);">Admin review required before publishing</div>
          <input type="file" id="fileInput" required style="display:none;" onchange="updateSelectedFileName(this.files[0])" />
        </div>

        <div style="margin-top:20px; text-align:right; display:flex; gap:10px; justify-content:flex-end;">
          <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Cancel</button>
          <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">Upload Document</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer-employee.php'; ?>

<script>
let allDocuments = [];

document.addEventListener('DOMContentLoaded', () => {
  fetchDocuments();
});

async function fetchDocuments() {
  try {
    const res = await fetch('/api/documents/list.php');
    const data = await res.json();
    if (data.success && data.documents) {
      allDocuments = data.documents.filter(d => d.status === 'approved');
      renderDocuments(allDocuments);
    }
  } catch (err) {
    showToast('Failed to load documents', 'error');
  }
}

function renderDocuments(docs) {
  const tbody = document.getElementById('documentsTbody');

  if (!docs || docs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-subtle);">No documents found in knowledge library.</td></tr>`;
    return;
  }

  tbody.innerHTML = docs.map(doc => {
    const isLocked = doc.is_locked;
    const reqStatus = doc.download_request_status;

    let downloadActionBtn = '';
    if (isLocked) {
      if (reqStatus === 'approved') {
        downloadActionBtn = `<a href="/api/documents/download.php?id=${doc.id}" class="btn btn-success btn-sm">Download Authorized</a>`;
      } else if (reqStatus === 'pending') {
        downloadActionBtn = `<span class="badge badge-pending">Request Pending</span>`;
      } else {
        downloadActionBtn = `<button class="btn btn-secondary btn-sm" onclick="requestDownload(${doc.id})">Request Download</button>`;
      }
    } else {
      downloadActionBtn = `<a href="/api/documents/download.php?id=${doc.id}" class="btn btn-primary btn-sm" download>Download</a>`;
    }

    return `
      <tr>
        <td style="font-weight:600; color:var(--text-main);">${escapeHtml(doc.original_name)}</td>
        <td>${escapeHtml(doc.uploaded_by_name || 'Admin')}</td>
        <td style="font-size:0.8rem;">${doc.file_size_formatted || ''}</td>
        <td style="font-size:0.78rem; color:var(--text-light);">${doc.upload_time}</td>
        <td style="text-align:right; display:flex; gap:8px; justify-content:flex-end; align-items:center;">
          <button class="btn btn-secondary btn-sm" onclick="openDocumentPreview(${doc.id}, '${escapeHtml(doc.original_name)}', '${doc.file_type}')">Preview</button>
          ${downloadActionBtn}
        </td>
      </tr>
    `;
  }).join('');
}

function filterDocuments() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const filtered = allDocuments.filter(doc => doc.original_name.toLowerCase().includes(query));
  renderDocuments(filtered);
}

async function requestDownload(docId) {
  try {
    const res = await fetch('/api/download-requests/request.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ document_id: docId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Download request submitted to admin.', 'success');
      fetchDocuments();
    } else {
      showToast(data.message || 'Request failed.', 'error');
    }
  } catch (err) {
    showToast('Error sending request.', 'error');
  }
}

function openUploadModal() {
  document.getElementById('uploadModal').classList.add('show');
}

function closeUploadModal() {
  document.getElementById('uploadModal').classList.remove('show');
  document.getElementById('uploadForm').reset();
  document.getElementById('selectedFileName').textContent = 'Choose a file or drag & drop';
}

function updateSelectedFileName(file) {
  if (file) {
    document.getElementById('selectedFileName').textContent = file.name;
  }
}

async function handleUploadSubmit(e) {
  e.preventDefault();
  const fileInput = document.getElementById('fileInput');
  if (!fileInput.files[0]) return;

  const btn = document.getElementById('uploadSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Uploading...';

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);

  try {
    const res = await fetch('/api/documents/upload.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message || 'Upload submitted for admin approval.', 'success');
      closeUploadModal();
      fetchDocuments();
    } else {
      showToast(data.message || 'Upload failed.', 'error');
    }
  } catch (err) {
    showToast('Upload error.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Upload Document';
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>