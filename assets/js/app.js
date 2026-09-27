// KnowledgeBank — Global Application JavaScript Helpers

// Toggle Mobile Sidebar Drawer
function toggleMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar) sidebar.classList.toggle('mobile-open');
  if (overlay) overlay.classList.toggle('active');
}

// Toggle Topbar Circular Profile Dropdown
function toggleProfileDropdown(e) {
  if (e) e.stopPropagation();
  const dropdown = document.getElementById('topbarProfileDropdown');
  if (dropdown) {
    dropdown.classList.toggle('show');
  }
}

// Retrieve CSRF Token from meta tag or sessionStorage
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta && meta.content) return meta.content;
  return sessionStorage.getItem('csrf_token') || '';
}

// Wrapper for fetch() that automatically attaches X-CSRF-Token on POST/PUT/DELETE requests
async function fetchWithCsrf(url, options = {}) {
  options.headers = options.headers || {};
  const method = (options.method || 'GET').toUpperCase();
  if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
    const token = getCsrfToken();
    if (token) {
      if (options.headers instanceof Headers) {
        options.headers.set('X-CSRF-Token', token);
      } else {
        options.headers['X-CSRF-Token'] = token;
      }
    }
  }
  return fetch(url, options);
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
  const dropdown = document.getElementById('topbarProfileDropdown');
  const avatarBtn = document.getElementById('topbarAvatarBtn');
  if (dropdown && dropdown.classList.contains('show')) {
    if (avatarBtn && !avatarBtn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  }
});
