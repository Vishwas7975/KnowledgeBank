<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'my-uploads';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Uploads — KnowledgeBank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .page-header { margin-bottom: 20px; }
  .page-header h1 { font-size: 1.4rem; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
  .page-header p  { font-size: 0.8rem; color: var(--slate); }

  .stats-row { display: flex; gap: 14px; margin-bottom: 20px; }
  .mini-stat {
    display: flex; align-items: center; gap: 12px;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 14px 18px;
    box-shadow: var(--shadow-sm); flex: 1;
  }
  .mini-stat-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; }
  .mini-stat-val { font-size: 1.3rem; font-weight: 700; color: var(--navy); line-height: 1; }
  .mini-stat-lbl { font-size: 0.7rem; color: var(--slate-lt); margin-top: 2px; }

  .controls-row {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 20px; border-bottom: 1px solid var(--border); flex-wrap: wrap;
  }
  .search-inline {
    display: flex; align-items: center; gap: 7px; padding: 7px 12px;
    border: 1.5px solid var(--border); border-radius: 8px; background: var(--bg);
    flex: 1; min-width: 200px; max-width: 320px; transition: border-color .2s;
  }
  .search-inline:focus-within { border-color: var(--royal-mid); background: #fff; }
  .search-inline svg { color: var(--slate-lt); flex-shrink: 0; }
  .search-inline input { border: none; outline: none; background: transparent; font-family: inherit; font-size: 0.8rem; color: var(--text); width: 100%; }
  .search-inline input::placeholder { color: var(--slate-lt); }
  .controls-spacer { flex: 1; }
  .doc-name-cell { display: flex; align-items: center; gap: 8px; }
  .action-cell { display: flex; align-items: center; gap: 6px; }
  .loading-row td { padding: 36px 16px; text-align: center; color: var(--slate-lt); font-size: 0.82rem; }

  /* Upload modal */
  .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,27,76,.45); z-index: 100; align-items: center; justify-content: center; }
  .modal-backdrop.open { display: flex; }
  .modal { background: #fff; border-radius: var(--radius); box-shadow: 0 8px 40px rgba(15,27,76,.18); width: 100%; max-width: 480px; padding: 28px; position: relative; animation: slideUp .2s ease; }
  @keyframes slideUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  .modal-title { font-size: 1.05rem; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
  .modal-sub   { font-size: 0.78rem; color: var(--slate); margin-bottom: 22px; }
  .modal-close { position: absolute; top: 18px; right: 20px; width: 28px; height: 28px; border-radius: 6px; border: none; background: var(--bg); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--slate); }
  .modal-close:hover { background: var(--border); }
  .drop-zone { border: 2px dashed var(--border-mid); border-radius: 10px; padding: 32px 20px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; margin-bottom: 16px; }
  .drop-zone:hover, .drop-zone.drag-over { border-color: var(--royal); background: var(--royal-lt); }
  #fileInput { display: none; }
  .selected-file { display: none; align-items: center; gap: 10px; padding: 10px 14px; background: var(--blue-bg); border: 1px solid var(--blue-brd); border-radius: 8px; margin-bottom: 16px; font-size: .82rem; }
  .selected-file.show { display: flex; }
  .sel-file-view { display:none;align-items:center;justify-content:center;width:26px;height:26px;border:none;border-radius:6px;background:var(--royal-light,#eff6ff);color:var(--royal,#2563eb);cursor:pointer;flex-shrink:0;transition:background .15s; }
  .sel-file-view:hover { background:var(--royal,#2563eb);color:#fff; }
  .sel-file-view.show { display:flex; }
  .upload-progress { display: none; height: 4px; border-radius: 2px; background: var(--border); overflow: hidden; margin-bottom: 14px; }
  .upload-progress.show { display: block; }
  .upload-progress-bar { height: 100%; background: var(--royal-btn); width: 0%; transition: width .3s; border-radius: 2px; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

  /* Delete confirm */
  .confirm-modal { background: #fff; border-radius: var(--radius); box-shadow: 0 8px 40px rgba(15,27,76,.18); width: 100%; max-width: 400px; padding: 28px; position: relative; animation: slideUp .2s ease; }
  .confirm-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--red-bg); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }

  /* Toast */
  .toast { position: fixed; bottom: 24px; right: 24px; padding: 12px 18px; border-radius: 9px; font-size: .82rem; font-weight: 600; box-shadow: 0 4px 20px rgba(0,0,0,.12); z-index: 200; display: none; align-items: center; gap: 9px; }
  .toast.show { display: flex; }
  .toast.success { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-brd); }
  .toast.error   { background: var(--red-bg);   color: var(--red);   border: 1px solid var(--red-brd); }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

    <main class="page-content">

      <div class="page-header">
        <h1>My Uploads</h1>
        <p>Documents you've personally uploaded to the vault.</p>
      </div>

      <div class="stats-row">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:var(--blue-bg);color:var(--royal)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
          </div>
          <div><div class="mini-stat-val" id="statTotal">—</div><div class="mini-stat-lbl">Total Uploads</div></div>
        </div>
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:var(--green-bg);color:var(--green)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <ellipse cx="12" cy="12" rx="10" ry="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div><div class="mini-stat-val" id="statSize">—</div><div class="mini-stat-lbl">Storage Used</div></div>
        </div>
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:var(--amber-bg);color:var(--amber)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            </svg>
          </div>
          <div><div class="mini-stat-val" id="statRecent">—</div><div class="mini-stat-lbl">This Week</div></div>
        </div>
      </div>

      <div class="card">
        <div class="controls-row">
          <div class="search-inline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Search by filename…" oninput="filterDocs()">
          </div>
          <div class="controls-spacer"></div>
          <button class="btn-upload" onclick="openUploadModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
              <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>
            </svg>
            Upload New
          </button>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>File Name</th><th>Date Uploaded</th><th>Size</th><th>Type</th><th></th></tr>
            </thead>
            <tbody id="docsBody">
              <tr class="loading-row"><td colspan="5">Loading…</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- Upload Modal -->
<div class="modal-backdrop" id="uploadModal" onclick="if(event.target.id==='uploadModal')closeUploadModal()">
  <div class="modal">
    <button class="modal-close" onclick="closeUploadModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-title">Upload Document</div>
    <div class="modal-sub">Max 50 MB. PDF, DOCX, XLSX, images, ZIP and more.</div>
    <div class="drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()"
         ondragover="event.preventDefault();document.getElementById('dropZone').classList.add('drag-over')"
         ondragleave="document.getElementById('dropZone').classList.remove('drag-over')"
         ondrop="event.preventDefault();document.getElementById('dropZone').classList.remove('drag-over');handleFileSelect(event.dataTransfer.files[0])">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--royal)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px">
        <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
        <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/>
      </svg>
      <div style="font-size:.85rem;font-weight:600;color:var(--navy)">Drop file here or click to browse</div>
      <div style="font-size:.72rem;color:var(--slate-lt);margin-top:4px">Supported: PDF, DOCX, XLSX, PPTX, images, ZIP</div>
    </div>
    <input type="file" id="fileInput" onchange="handleFileSelect(this.files[0])">
    <div class="selected-file" id="selectedFileBox">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--royal)" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      <span style="flex:1;font-weight:600;color:var(--navy)" id="selFileName"></span>
      <span style="color:var(--slate)" id="selFileSize"></span>
      <button class="sel-file-view" id="selFileViewBtn" title="Preview file" onclick="previewLocalFile()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
    <div class="upload-progress" id="uploadProgress"><div class="upload-progress-bar" id="progressBar"></div></div>
    <div id="uploadErr" style="display:none;background:var(--red-bg);border:1px solid var(--red-brd);border-radius:8px;padding:10px 14px;font-size:.8rem;color:var(--red);margin-bottom:12px"></div>
    <div id="uploadSuccess" style="display:none;align-items:center;gap:10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;font-size:.82rem;color:#16a34a;margin-bottom:12px">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      <span style="flex:1">Uploaded successfully!</span>
      <a id="viewUploadedBtn" href="#" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:var(--royal,#2563eb);color:#fff;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        View File
      </a>
    </div>
    <div class="modal-actions">
      <button class="btn" onclick="closeUploadModal()">Cancel</button>
      <button class="btn btn-primary" id="uploadBtn" onclick="doUpload()" disabled>Upload</button>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-backdrop" id="deleteModal" onclick="if(event.target.id==='deleteModal')closeDeleteModal()">
  <div class="confirm-modal">
    <button class="modal-close" onclick="closeDeleteModal()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="confirm-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
    </div>
    <div style="font-size:1rem;font-weight:700;color:var(--navy);margin-bottom:8px">Delete Document</div>
    <div style="font-size:.82rem;color:var(--slate);line-height:1.5;margin-bottom:22px">
      Delete <strong id="delFileName"></strong>? This cannot be undone.
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button class="btn" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn btn-danger" onclick="doDelete()">Delete</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
let allDocs = [], filtered = [], selFile = null, delId = null;

async function loadDocs() {
  try {
    const res  = await fetch('/api/documents/my-uploads.php');
    const data = await res.json();
    if (!data.success) return;
    allDocs = data.documents || [];
    // Stats
    document.getElementById('statTotal').textContent = allDocs.length;
    const totalBytes = allDocs.reduce((s,d) => s + parseInt(d.file_size||0), 0);
    document.getElementById('statSize').textContent = formatBytes(totalBytes);
    const week = allDocs.filter(d => {
      const diff = (Date.now() - parseLocalDate(d.upload_time)) / 86400000;
      return diff <= 7;
    }).length;
    document.getElementById('statRecent').textContent = week;
    filterDocs();
  } catch(e) {}
}

function filterDocs() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  filtered = allDocs.filter(d => !q || d.original_name.toLowerCase().includes(q));
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('docsBody');
  if (!filtered.length) {
    tbody.innerHTML = '<tr class="loading-row"><td colspan="5">No documents found.</td></tr>';
    return;
  }
  tbody.innerHTML = filtered.map(doc => {
    const ext = (doc.original_name||'').split('.').pop().toLowerCase();
    return `
    <tr>
      <td>
        <div class="doc-name-cell">
          ${getFileIcon(ext)}
          <span style="font-size:.82rem;font-weight:500;color:var(--navy)" class="truncate" style="max-width:280px" title="${escHtml(doc.original_name)}">${escHtml(doc.original_name)}</span>
        </div>
      </td>
      <td style="color:var(--slate);font-size:.78rem;white-space:nowrap">${formatDate(doc.upload_time)}</td>
      <td style="color:var(--slate);font-size:.78rem">${escHtml(doc.file_size_formatted||formatBytes(doc.file_size))}</td>
      <td><span style="font-family:'JetBrains Mono',monospace;font-size:.68rem;padding:2px 7px;border-radius:4px;background:var(--bg);border:1px solid var(--border);color:var(--slate)">.${ext}</span></td>
      <td>
        <div class="action-cell">
          <a href="/api/documents/download.php?id=${doc.id}" class="btn btn-sm">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg>
          </a>
          <button class="btn btn-sm btn-danger" onclick="openDeleteModal(${doc.id},'${escHtml(doc.original_name).replace(/'/g,"\\'")}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
          </button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// Upload
function openUploadModal() { document.getElementById('uploadModal').classList.add('open'); resetUpload(); }
function closeUploadModal() { document.getElementById('uploadModal').classList.remove('open'); resetUpload(); }
function resetUpload() {
  selFile = null;
  if (window._previewObjUrl) { URL.revokeObjectURL(window._previewObjUrl); window._previewObjUrl = null; }
  document.getElementById('fileInput').value = '';
  document.getElementById('selectedFileBox').classList.remove('show');
  document.getElementById('uploadBtn').disabled = true;
  document.getElementById('uploadBtn').textContent = 'Upload';
  document.getElementById('uploadBtn').onclick = doUpload;
  document.getElementById('uploadProgress').classList.remove('show');
  document.getElementById('progressBar').style.width = '0%';
  document.getElementById('uploadErr').style.display = 'none';
  document.getElementById('uploadSuccess').style.display = 'none';
  document.getElementById('selFileViewBtn').classList.remove('show');
}
function handleFileSelect(file) {
  if (!file) return;
  selFile = file;
  document.getElementById('selFileName').textContent = file.name;
  document.getElementById('selFileSize').textContent = formatBytes(file.size);
  document.getElementById('selectedFileBox').classList.add('show');
  document.getElementById('uploadBtn').disabled = false;
  // Show eye icon only for browser-viewable types
  const viewable = /\.(pdf|jpg|jpeg|png|gif|webp)$/i.test(file.name);
  const vBtn = document.getElementById('selFileViewBtn');
  if (viewable) {
    if (window._previewObjUrl) URL.revokeObjectURL(window._previewObjUrl);
    window._previewObjUrl = URL.createObjectURL(file);
    vBtn.classList.add('show');
  } else {
    vBtn.classList.remove('show');
  }
}
function previewLocalFile() {
  if (window._previewObjUrl) window.open(window._previewObjUrl, '_blank');
}
async function doUpload() {
  if (!selFile) return;
  const btn = document.getElementById('uploadBtn');
  btn.disabled = true; btn.textContent = 'Uploading…';
  document.getElementById('uploadProgress').classList.add('show');
  document.getElementById('uploadErr').style.display = 'none';
  const fd = new FormData();
  fd.append('file', selFile);
  let prog = 0;
  const iv = setInterval(() => { prog = Math.min(prog+8, 88); document.getElementById('progressBar').style.width = prog+'%'; }, 120);
  try {
    const res  = await fetch('/api/documents/upload.php', { method:'POST', body:fd });
    const data = await res.json();
    clearInterval(iv);
    document.getElementById('progressBar').style.width = '100%';
    if (data.success) {
      const docId = data.document ? data.document.id : (data.id || null);
      const successEl = document.getElementById('uploadSuccess');
      successEl.style.display = 'flex';
      if (docId) {
        document.getElementById('viewUploadedBtn').href = '/api/documents/view.php?id=' + docId;
      }
      document.getElementById('uploadBtn').textContent = 'Done';
      document.getElementById('uploadBtn').disabled = false;
      document.getElementById('uploadBtn').onclick = function() { closeUploadModal(); showToast('Document uploaded.', 'success'); loadDocs(); };
      setTimeout(() => { closeUploadModal(); showToast('Document uploaded.', 'success'); loadDocs(); }, 3000);
    } else {
      document.getElementById('uploadErr').textContent = data.message || 'Upload failed.';
      document.getElementById('uploadErr').style.display = 'block';
      document.getElementById('uploadProgress').classList.remove('show');
      btn.disabled = false; btn.textContent = 'Upload';
    }
  } catch(e) {
    clearInterval(iv);
    document.getElementById('uploadErr').textContent = 'Network error.';
    document.getElementById('uploadErr').style.display = 'block';
    btn.disabled = false; btn.textContent = 'Upload';
  }
}

// Delete
function openDeleteModal(id, name) {
  delId = id;
  document.getElementById('delFileName').textContent = name;
  document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal() { delId = null; document.getElementById('deleteModal').classList.remove('open'); }
async function doDelete() {
  if (!delId) return;
  try {
    const res  = await fetch('/api/documents/delete.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:delId}) });
    const data = await res.json();
    closeDeleteModal();
    showToast(data.message || 'Done.', data.success ? 'success' : 'error');
    if (data.success) loadDocs();
  } catch(e) { closeDeleteModal(); showToast('Network error.', 'error'); }
}

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.innerHTML = escHtml(msg);
  t.className = `toast show ${type}`;
  setTimeout(() => t.classList.remove('show'), 3000);
}
function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

if (new URLSearchParams(location.search).get('action') === 'upload') {
  window.addEventListener('DOMContentLoaded', openUploadModal);
}
loadDocs();
</script>
</body>
</html>