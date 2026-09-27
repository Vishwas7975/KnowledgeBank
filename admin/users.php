<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management — KnowledgeBank Admin</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="app-shell">
  <!-- Admin Sidebar -->
  <?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

  <div class="main-area">
    <!-- Topbar -->
    <?php
    $topbarTitle = 'User Account Management';
    $topbarActions = '
      <button class="btn btn-primary btn-sm" onclick="openCreateUserModal()" style="margin-right:12px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="20" y1="8" x2="20" y2="14"/>
          <line x1="17" y1="11" x2="23" y2="11"/>
        </svg>
        <span>Add Employee Account</span>
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
          <input type="text" id="userSearchInput" placeholder="Search by name, email, or Employee ID..." oninput="filterUsers()" />
        </div>

        <div style="display:flex; gap:12px;">
          <select id="roleFilter" class="custom-select" onchange="filterUsers()">
            <option value="all">All Roles</option>
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
          </select>
        </div>
      </div>

      <!-- Users Table Card -->
      <div class="card">
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Emp ID</th>
                  <th>Department</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th style="text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody">
                <tr>
                  <td colspan="7" style="text-align:center; padding:40px; color:var(--text-subtle);">Loading user accounts...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal-overlay">
  <div class="modal-container" style="max-width: 500px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);">
    <div class="modal-header" style="padding: 18px 24px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
      <div class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a;">Create Employee Account</div>
      <button class="modal-close" onclick="closeCreateUserModal()" title="Close Modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #ffffff;">
      <form id="createUserForm" onsubmit="handleCreateUser(event)">
        <div style="display:flex; flex-direction:column; gap:16px;">
          <div>
            <label class="form-label" for="newName">Full Name</label>
            <input type="text" id="newName" class="form-input" required placeholder="e.g. Rahul Sharma" />
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div>
              <label class="form-label" for="newEmpId">Employee ID</label>
              <input type="text" id="newEmpId" class="form-input" required placeholder="VAI-26-0120" />
            </div>
            <div>
              <label class="form-label" for="newDept">Department</label>
              <input type="text" id="newDept" class="form-input" placeholder="e.g. IT, HR" />
            </div>
          </div>

          <div>
            <label class="form-label" for="newEmail">Email Address</label>
            <input type="email" id="newEmail" class="form-input" required placeholder="user@example.com" />
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <div>
              <label class="form-label" for="newPassword">Temporary Password</label>
              <input type="password" id="newPassword" class="form-input" required placeholder="Min 8 chars" />
            </div>
            <div>
              <label class="form-label" for="newRole">System Role</label>
              <select id="newRole" class="custom-select" style="width:100%; height:44px; padding:0 14px; border:1.5px solid #cbd5e1; border-radius:12px; font-size:0.88rem; color:#0f172a; background-color:#ffffff;">
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateUserModal()" style="padding:9px 18px; border-radius:10px;">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm" id="createUserSubmitBtn" style="padding:9px 20px; border-radius:10px; font-weight:600;">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
let allUsers = [];

document.addEventListener('DOMContentLoaded', () => {
  fetchUsers();
});

async function fetchUsers() {
  try {
    const res = await fetch('/api/users/list.php');
    const data = await res.json();
    if (data.success && data.users) {
      allUsers = data.users;
      renderUsers(allUsers);
    }
  } catch (err) {
    showToast('Failed to load users', 'error');
  }
}

function renderUsers(users) {
  const tbody = document.getElementById('usersTbody');

  if (!users || users.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-subtle);">No user accounts found.</td></tr>`;
    return;
  }

  tbody.innerHTML = users.map(user => {
    const isDeactivated = user.status === 'inactive';
    const statusBadge = isDeactivated 
      ? `<span class="badge badge-rejected">Inactive</span>`
      : `<span class="badge badge-approved">Active</span>`;

    const roleBadge = user.role === 'admin'
      ? `<span class="badge badge-purple">Admin</span>`
      : `<span class="badge badge-blue">Employee</span>`;

    const toggleBtn = isDeactivated
      ? `<button class="btn btn-success btn-sm" onclick="reactivateUser(${user.id})">Activate</button>`
      : `<button class="btn btn-danger btn-sm" onclick="deactivateUser(${user.id})">Deactivate</button>`;

    return `
      <tr>
        <td>
          <div style="font-weight:600; color:var(--text-main);">${escapeHtml(user.name)}</div>
          <div style="font-size:0.75rem; color:var(--text-subtle);">${escapeHtml(user.email)}</div>
        </td>
        <td style="font-family:'JetBrains Mono', monospace; font-size:0.8rem;">${escapeHtml(user.employee_id || '')}</td>
        <td>${escapeHtml(user.department || 'General')}</td>
        <td>${roleBadge}</td>
        <td>${statusBadge}</td>
        <td style="font-size:0.78rem; color:var(--text-light);">${user.last_login || 'Never'}</td>
        <td style="text-align:right;">${toggleBtn}</td>
      </tr>
    `;
  }).join('');
}

function filterUsers() {
  const query = document.getElementById('userSearchInput').value.toLowerCase();
  const role = document.getElementById('roleFilter').value;

  const filtered = allUsers.filter(u => {
    const matchesQuery = u.name.toLowerCase().includes(query) || u.email.toLowerCase().includes(query) || (u.employee_id && u.employee_id.toLowerCase().includes(query));
    const matchesRole = role === 'all' || u.role === role;
    return matchesQuery && matchesRole;
  });

  renderUsers(filtered);
}

async function deactivateUser(id) {
  if (!confirm('Deactivate this user account?')) return;
  try {
    const res = await fetch('/api/users/deactivate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('User account deactivated.', 'success');
      fetchUsers();
    } else {
      showToast(data.message || 'Deactivation failed.', 'error');
    }
  } catch (err) {
    showToast('Error deactivating user.', 'error');
  }
}

async function reactivateUser(id) {
  try {
    const res = await fetch('/api/users/reactivate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('User account reactivated.', 'success');
      fetchUsers();
    } else {
      showToast(data.message || 'Reactivation failed.', 'error');
    }
  } catch (err) {
    showToast('Error reactivating user.', 'error');
  }
}

function openCreateUserModal() {
  document.getElementById('createUserModal').classList.add('show');
}

function closeCreateUserModal() {
  document.getElementById('createUserModal').classList.remove('show');
  document.getElementById('createUserForm').reset();
}

async function handleCreateUser(e) {
  e.preventDefault();
  const name = document.getElementById('newName').value.trim();
  const employee_id = document.getElementById('newEmpId').value.trim();
  const department = document.getElementById('newDept').value.trim();
  const email = document.getElementById('newEmail').value.trim();
  const password = document.getElementById('newPassword').value.trim();
  const role = document.getElementById('newRole').value;

  const btn = document.getElementById('createUserSubmitBtn');
  btn.disabled = true;
  btn.textContent = 'Creating...';

  try {
    const res = await fetch('/api/users/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, employee_id, department, email, password, role })
    });
    const data = await res.json();
    if (data.success) {
      showToast('User account created successfully!', 'success');
      closeCreateUserModal();
      fetchUsers();
    } else {
      showToast(data.message || 'Failed to create user.', 'error');
    }
  } catch (err) {
    showToast('Error creating user.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Create Account';
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>