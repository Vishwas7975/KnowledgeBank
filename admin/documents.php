<?php
// admin/documents.php — Folder-Based Document Management Portal
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'documents';
$topbarTitle = 'Document & Folder Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>Documents & Folders — KnowledgeBank Admin</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .bulk-bar {
    display: none;
    align-items: center;
    justify-content: space-between;
    background: var(--blue-bg);
    border: 1px solid var(--blue-border);
    padding: 12px 20px;
    border-radius: var(--radius);
    margin-bottom: 20px;
  }
  .bulk-bar.show { display: flex; }
  .bulk-title { font-size: 0.84rem; font-weight: 600; color: var(--blue-text); }
  .bulk-actions { display: flex; gap: 8px; }
  
  .section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
  }
  .section-title-sm {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
  }
</style>
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <div class="main-area">
    <!-- Topbar Header -->
    <?php
    $topbarActions = '
      <button class="btn btn-secondary btn-sm" onclick="openCreateFolderModal()" style="margin-right:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          <line x1="12" y1="11" x2="12" y2="17"/>
          <line x1="9" y1="14" x2="15" y2="14"/>
        </svg>
        <span>New Folder</span>
      </button>
      <button class="btn btn-primary btn-sm" onclick="openUploadModal()" style="margin-right:12px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <span>Upload File</span>
      </button>';
    include __DIR__ . '/../includes/topbar-admin.php';
    ?>

    <!-- Content Body -->
    <div class="content-body">

      <!-- Section: Folders Overview Grid -->
      <div class="section-header-row">
        <div class="section-title-sm">Knowledge Folders</div>
      </div>

      <div class="folder-card-grid" id="foldersGrid">
        <div class="folder-card active-folder" onclick="selectFolder(null, 'All Documents')">
          <div class="folder-card-top">
            <div class="folder-icon-wrapper">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
          </div>
          <div class="folder-card-title">All Documents</div>
          <div class="folder-card-count" id="allFolderCount">Loading...</div>
        </div>
      </div>

      <!-- Breadcrumb Navigation -->
      <div class="breadcrumb-nav" id="breadcrumbNav">
        <span class="breadcrumb-item" onclick="selectFolder(null, 'All Documents')">Folders</span>
        <span>/</span>
        <span class="breadcrumb-active" id="activeFolderBreadcrumb">All Documents</span>
      </div>

      <!-- Bulk Selection Toolbar -->
      <div class="bulk-bar" id="bulkBar">
        <div class="bulk-title" id="bulkCountText">0 documents selected</div>
        <div class="bulk-actions">
          <button class="btn btn-success btn-sm" onclick="handleBulkApprove()">Bulk Approve</button>
          <button class="btn btn-danger btn-sm" onclick="handleBulkDelete()">Bulk Delete</button>
        </div>
      </div>

      <!-- Toolbar Row -->
      <div class="toolbar-row">
        <div class="search-input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="searchInput" placeholder="Search documents by name or uploader..." oninput="filterDocuments()" />
        </div>

        <div style="display:flex; gap:12px;">
          <select id="statusFilter" class="custom-select" onchange="filterDocuments()">
            <option value="all">All Statuses</option>
            <option value="pending">Pending Approval</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>

      <!-- Documents Table Card -->
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table" style="width:100%; table-layout:auto;">
              <thead>
                <tr>
                  <th style="width:36px;"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" /></th>
                  <th style="min-width:200px;">Document Name</th>
                  <th style="min-width:120px;">Folder</th>
                  <th style="min-width:120px;">Uploaded By</th>
                  <th style="min-width:80px;">Size</th>
                  <th style="min-width:140px;">Upload Time</th>
                  <th style="min-width:100px;">Status</th>
                  <th style="min-width:160px; text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody id="documentsTbody">
                <tr>
                  <td colspan="8" style="text-align:center; padding:40px; color:var(--text-subtle);">Loading document repository...</td>
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
  <div class="modal-container" style="max-width: 480px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);">
    <div class="modal-header" style="padding: 18px 24px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
      <div class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Upload New Document</div>
      <button class="modal-close" onclick="closeUploadModal()" title="Close Modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #ffffff;">
      <form id="uploadForm" onsubmit="handleUploadSubmit(event)">
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label" for="targetFolderSelect" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 8px;">Target Folder</label>
          <select id="targetFolderSelect" class="custom-select" style="width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 0.88rem; color: #0f172a; background-color: #ffffff;">
            <option value="">General (No Folder)</option>
          </select>
        </div>

        <div class="upload-dropzone" onclick="document.getElementById('fileInput').click()" style="padding: 28px 20px; border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 14px; text-align: center; cursor: pointer; transition: all 0.2s ease;">
          <div class="dropzone-icon" style="width: 48px; height: 48px; border-radius: 14px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; box-shadow: 0 2px 8px rgba(37,99,235,0.12);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
          </div>
          <div style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 4px;" id="selectedFileName">Choose a file or drag & drop</div>
          <div style="font-size: 0.75rem; color: #64748b;">PDF, DOCX, XLSX, PNG, JPG (Max 50MB)</div>
          <input type="file" id="fileInput" required style="display: none;" onchange="updateSelectedFileName(this.files[0])" />
        </div>

        <div style="margin-top: 24px; text-align: right; display: flex; gap: 12px; justify-content: flex-end;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeUploadModal()" style="padding: 9px 18px; border-radius: 10px;">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm" id="uploadSubmitBtn" style="padding: 9px 20px; border-radius: 10px; font-weight: 600;">Upload Document</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="modal-overlay">
  <div class="modal-container" style="max-width: 440px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);">
    <div class="modal-header" style="padding: 18px 24px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
      <div class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Create New Folder</div>
      <button class="modal-close" onclick="closeCreateFolderModal()" title="Close Modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #ffffff;">
      <form id="createFolderForm" onsubmit="handleCreateFolderSubmit(event)">
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label" for="folderNameInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 8px;">Folder Name</label>
          <input type="text" id="folderNameInput" required placeholder="e.g. Syllabus, Question Papers, Circulars" class="form-input" style="width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid #cbd5e1; border-radius: 12px; font-size: 0.88rem; color: #0f172a; outline: none; transition: border-color 0.2s;" />
        </div>

        <div style="margin-top: 24px; text-align: right; display: flex; gap: 12px; justify-content: flex-end;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateFolderModal()" style="padding: 9px 18px; border-radius: 10px;">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm" id="createFolderSubmitBtn" style="padding: 9px 20px; border-radius: 10px; font-weight: 600;">Create Folder</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script src="/assets/js/app.js"></script>
<script>
let allDocuments = [];
let allFolders = [];
let activeFolderId = null;
let activeFolderName = 'All Documents';
let selectedDocIds = new Set();

document.addEventListener('DOMContentLoaded', () => {
  fetchFolders();
  fetchDocuments();
});

async function fetchFolders() {
  try {
    const res = await fetch('/api/folders/list.php');
    const data = await res.json();
    if (data.success && data.folders) {
      allFolders = data.folders;
      renderFolders();
      populateFolderDropdown();
    }
  } catch (err) {
    showToast('Failed to load folders', 'error');
  }
}

function renderFolders() {
  const grid = document.getElementById('foldersGrid');
  const totalDocCount = allDocuments.length;
  
  let html = `
    <div class="folder-card ${activeFolderId === null ? 'active-folder' : ''}" onclick="selectFolder(null, 'All Documents')">
      <div class="folder-card-top">
        <div class="folder-icon-wrapper">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
      </div>
      <div class="folder-card-title">All Documents</div>
      <div class="folder-card-count">${totalDocCount} File(s)</div>
    </div>
  `;

  allFolders.forEach(folder => {
    const isActive = (activeFolderId === folder.id);
    const confBadge = folder.is_confidential ? `<span class="folder-badge confidential">Locked</span>` : '';
    html += `
      <div class="folder-card ${isActive ? 'active-folder' : ''}" onclick="selectFolder(${folder.id}, '${escapeHtml(folder.name)}')">
        <div class="folder-card-top">
          <div class="folder-icon-wrapper">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          ${confBadge}
        </div>
        <div class="folder-card-title">${escapeHtml(folder.name)}</div>
        <div class="folder-card-count">${folder.document_count || 0} File(s)</div>
      </div>
    `;
  });

  grid.innerHTML = html;
}

function populateFolderDropdown() {
  const select = document.getElementById('targetFolderSelect');
  if (!select) return;
  let html = '<option value="">General (No Folder)</option>';
  allFolders.forEach(f => {
    html += `<option value="${f.id}">${escapeHtml(f.name)}</option>`;
  });
  select.innerHTML = html;
}

function selectFolder(id, name) {
  activeFolderId = id;
  activeFolderName = name;
  document.getElementById('activeFolderBreadcrumb').textContent = name;
  renderFolders();
  filterDocuments();
}

async function fetchDocuments() {
  try {
    const res = await fetch('/api/documents/list.php');
    const data = await res.json();
    if (data.success && data.documents) {
      allDocuments = data.documents;
      renderFolders();
      filterDocuments();
    }
  } catch (err) {
    showToast('Failed to load documents', 'error');
  }
}

function renderDocuments(docs) {
  const tbody = document.getElementById('documentsTbody');
  selectedDocIds.clear();
  updateBulkBar();

  if (!docs || docs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:40px; color:var(--text-subtle);">No documents found in this view.</td></tr>`;
    return;
  }

  tbody.innerHTML = docs.map(doc => {
    const statusBadge = doc.status === 'approved' 
      ? `<span class="badge badge-approved">Approved</span>`
      : (doc.status === 'rejected' ? `<span class="badge badge-rejected">Rejected</span>` : `<span class="badge badge-pending">Pending</span>`);

    const folderLabel = doc.folder_name ? escapeHtml(doc.folder_name) : '<span style="color:#94a3b8;">General</span>';

    return `
      <tr>
        <td><input type="checkbox" class="doc-checkbox" data-id="${doc.id}" onchange="toggleSelectDoc(${doc.id}, this.checked)" /></td>
        <td style="font-weight:600; color:var(--text-main); max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${escapeHtml(doc.original_name)}">${escapeHtml(doc.original_name)}</td>
        <td><span class="badge badge-blue">${folderLabel}</span></td>
        <td>${escapeHtml(doc.uploaded_by_name || 'Admin')}</td>
        <td style="font-size:0.8rem; white-space:nowrap;">${doc.file_size_formatted || ''}</td>
        <td style="font-size:0.78rem; color:var(--text-light); white-space:nowrap;">${doc.upload_time}</td>
        <td>${statusBadge}</td>
        <td style="text-align:right;">
          <div style="display:inline-flex; align-items:center; justify-content:flex-end; gap:6px; white-space:nowrap;">
            <button class="btn btn-secondary btn-sm" onclick="handlePreviewDoc(${doc.id})">Preview</button>
            ${doc.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="approveDocument(${doc.id})">Approve</button>` : ''}
            <button class="btn btn-danger btn-sm" onclick="deleteDocument(${doc.id})">Delete</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function filterDocuments() {
  const query = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;

  const filtered = allDocuments.filter(doc => {
    const matchesFolder = (activeFolderId === null) || (doc.folder_id == activeFolderId);
    const matchesQuery  = doc.original_name.toLowerCase().includes(query) || (doc.uploaded_by_name && doc.uploaded_by_name.toLowerCase().includes(query));
    const matchesStatus = (status === 'all') || (doc.status === status);
    return matchesFolder && matchesQuery && matchesStatus;
  });

  renderDocuments(filtered);
}

function toggleSelectDoc(id, isChecked) {
  if (isChecked) selectedDocIds.add(id);
  else selectedDocIds.delete(id);
  updateBulkBar();
}

function toggleSelectAll(isChecked) {
  const checkboxes = document.querySelectorAll('.doc-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = isChecked;
    const id = parseInt(cb.getAttribute('data-id'));
    if (isChecked) selectedDocIds.add(id);
    else selectedDocIds.delete(id);
  });
  updateBulkBar();
}

function updateBulkBar() {
  const bulkBar = document.getElementById('bulkBar');
  const countText = document.getElementById('bulkCountText');

  if (selectedDocIds.size > 0) {
    bulkBar.classList.add('show');
    countText.textContent = `${selectedDocIds.size} document(s) selected`;
  } else {
    bulkBar.classList.remove('show');
  }
}

async function approveDocument(id) {
  try {
    const res = await fetchWithCsrf('/api/documents/approve.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Document approved successfully!', 'success');
      fetchDocuments();
    } else {
      showToast(data.message || 'Approval failed.', 'error');
    }
  } catch (err) {
    showToast('Error approving document.', 'error');
  }
}

async function deleteDocument(id) {
  if (!confirm('Are you sure you want to delete this document?')) return;
  try {
    const res = await fetchWithCsrf('/api/documents/delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Document deleted.', 'success');
      fetchDocuments();
      fetchFolders();
    } else {
      showToast(data.message || 'Delete failed.', 'error');
    }
  } catch (err) {
    showToast('Error deleting document.', 'error');
  }
}

async function handleBulkApprove() {
  for (const id of selectedDocIds) {
    await approveDocument(id);
  }
}

async function handleBulkDelete() {
  if (!confirm(`Are you sure you want to delete ${selectedDocIds.size} selected document(s)?`)) return;
  for (const id of selectedDocIds) {
    await deleteDocument(id);
  }
}

function openUploadModal() {
  if (activeFolderId) {
    document.getElementById('targetFolderSelect').value = activeFolderId;
  }
  document.getElementById('uploadModal').classList.add('show');
}

function closeUploadModal() {
  document.getElementById('uploadModal').classList.remove('show');
  document.getElementById('uploadForm').reset();
  document.getElementById('selectedFileName').textContent = 'Choose a file or drag & drop';
}

function openCreateFolderModal() {
  document.getElementById('createFolderModal').classList.add('show');
}

function closeCreateFolderModal() {
  document.getElementById('createFolderModal').classList.remove('show');
  document.getElementById('createFolderForm').reset();
}

function updateSelectedFileName(file) {
  if (file) {
    document.getElementById('selectedFileName').textContent = file.name;
  }
}

async function handleUploadSubmit(e) {
  e.preventDefault();
  const fileInput = document.getElementById('fileInput');
  const targetFolderId = document.getElementById('targetFolderSelect').value;
  if (!fileInput.files[0]) return;

  const btn = document.getElementById('uploadSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Uploading...';

  const formData = new FormData();
  formData.append('file', fileInput.files[0]);
  if (targetFolderId) {
    formData.append('folder_id', targetFolderId);
  }

  try {
    const res = await fetchWithCsrf('/api/documents/upload.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      showToast('Upload successful!', 'success');
      closeUploadModal();
      fetchDocuments();
      fetchFolders();
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

async function handleCreateFolderSubmit(e) {
  e.preventDefault();
  const name = document.getElementById('folderNameInput').value.trim();
  const btn = document.getElementById('createFolderSubmitBtn');

  if (!name) return;
  btn.disabled = true;
  btn.textContent = 'Creating...';

  try {
    const res = await fetchWithCsrf('/api/folders/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Folder created successfully!', 'success');
      closeCreateFolderModal();
      fetchFolders();
    } else {
      showToast(data.message || 'Folder creation failed.', 'error');
    }
  } catch (err) {
    showToast('Network error creating folder.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Create Folder';
  }
}

function handlePreviewDoc(docId) {
  const doc = allDocuments.find(d => Number(d.id) === Number(docId));
  if (doc) {
    openDocumentPreview(doc.id, doc.original_name, doc.file_type || '');
  } else {
    showToast('Document not found', 'warning');
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function handleLogout() {
  window.location.href = '/api/auth/logout.php';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>