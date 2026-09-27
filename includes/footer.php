<!-- Toast Container -->
<div id="toast-container"></div>

<!-- Global Document Preview Modal -->
<div id="doc-preview-modal" class="modal-overlay">
  <div class="modal-container" style="max-width: 500px; border-radius: 20px;">
    <div class="modal-header" style="padding: 14px 20px;">
      <div class="modal-title" id="preview-modal-title" style="font-size: 0.95rem; font-weight: 700; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Document Preview</div>
      <button class="modal-close" onclick="closeDocumentPreview()" title="Close Modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body" style="padding:0; min-height: 280px; display: flex; justify-content: center; align-items: center; background: #f8fafc; position:relative;">
      <iframe id="preview-iframe" style="width:100%; height:500px; border:none; display:none;"></iframe>
      <img id="preview-img" style="max-width:100%; max-height:500px; object-fit:contain; padding:16px; display:none;" />
      
      <div id="preview-fallback" style="text-align:center; padding:28px 24px; width:100%; display:none;">
        <div style="width:52px; height:52px; border-radius:14px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; box-shadow:0 2px 8px rgba(37,99,235,0.12);">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>

        <div style="font-size:0.98rem; font-weight:700; color:#0f172a; margin-bottom:4px; word-break:break-word;" id="preview-fallback-filename">Document Name</div>
        
        <div style="display:inline-block; font-size:0.68rem; font-weight:700; color:#2563eb; background:#dbeafe; padding:3px 12px; border-radius:99px; margin-bottom:16px; text-transform:uppercase;" id="preview-ext-badge">
          OFFICE DOCUMENT
        </div>

        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; max-width:440px; margin:0 auto 18px; text-align:left; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
          <div style="display:flex; align-items:center; gap:6px; font-size:0.78rem; font-weight:600; color:#1e293b; margin-bottom:3px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>ClamAV Antivirus & MIME Verified</span>
          </div>
          <p style="font-size:0.76rem; color:#64748b; margin:0; line-height:1.4;">
            Direct browser preview is not supported for proprietary Office formats. Download to view in Microsoft Office or system viewer.
          </p>
        </div>

        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeDocumentPreview()">Close</button>
          <a id="preview-download-link" href="#" class="btn btn-primary btn-sm" download style="display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span>Download Document</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Global Toast System ─────────────────────────────────────
function showToast(message, type = 'success', title = '') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;

  const iconMap = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
  };

  const titleText = title || (type.charAt(0).toUpperCase() + type.slice(1));

  toast.innerHTML = `
    <div class="toast-icon">${iconMap[type] || 'ℹ'}</div>
    <div class="toast-body">
      <div class="toast-title">${titleText}</div>
      <div class="toast-message">${message}</div>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(40px)';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// ── Global Document Preview Modal ───────────────────────────
function openDocumentPreview(docId, docName, fileType) {
  const modal = document.getElementById('doc-preview-modal');
  const title = document.getElementById('preview-modal-title');
  const iframe = document.getElementById('preview-iframe');
  const img = document.getElementById('preview-img');
  const fallback = document.getElementById('preview-fallback');
  const downloadLink = document.getElementById('preview-download-link');
  const fallbackName = document.getElementById('preview-fallback-filename');
  const extBadge = document.getElementById('preview-ext-badge');

  title.textContent = docName;
  fallbackName.textContent = docName;

  const ext = (docName.split('.').pop() || 'file').toUpperCase();
  if (extBadge) {
    extBadge.textContent = `${ext} DOCUMENT`;
  }

  iframe.style.display = 'none';
  img.style.display = 'none';
  fallback.style.display = 'none';

  const viewUrl = `/api/documents/view.php?id=${docId}`;
  const downloadUrl = `/api/documents/download.php?id=${docId}`;

  if (fileType && fileType.startsWith('image/')) {
    img.src = viewUrl;
    img.style.display = 'block';
  } else if (fileType === 'application/pdf' || fileType === 'text/plain') {
    iframe.src = viewUrl;
    iframe.style.display = 'block';
  } else {
    downloadLink.href = downloadUrl;
    fallback.style.display = 'block';
  }

  modal.classList.add('show');
}

function closeDocumentPreview() {
  const modal = document.getElementById('doc-preview-modal');
  const iframe = document.getElementById('preview-iframe');
  const img = document.getElementById('preview-img');

  iframe.src = '';
  img.src = '';
  modal.classList.remove('show');
}

// ── Handle Logout ───────────────────────────────────────────
async function handleLogout() {
  try {
    const res = await fetch('/api/auth/logout.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      window.location.href = '/login.php';
    } else {
      showToast(data.message || 'Logout failed', 'error');
    }
  } catch (err) {
    window.location.href = '/login.php';
  }
}
</script>