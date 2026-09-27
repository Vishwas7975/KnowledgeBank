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
