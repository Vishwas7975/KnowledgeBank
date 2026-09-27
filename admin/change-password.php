<?php
require_once __DIR__ . '/../config/auth.php';
$authUser   = requireAdmin();
$activePage = 'settings';
$userName   = $_SESSION['name'] ?? 'Employee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
<title>Change Password — KnowledgeBank (Admin)</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  .page-header { margin-bottom: 24px; }
  .page-header h1 { font-size: 1.4rem; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
  .page-header p  { font-size: 0.8rem; color: var(--slate); }

  .settings-layout {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 20px;
    align-items: start;
  }

  /* Left nav card */
  .settings-nav { display: flex; flex-direction: column; gap: 2px; padding: 12px; }
  .settings-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 8px;
    font-size: 0.82rem; font-weight: 500; color: var(--slate);
    text-decoration: none; cursor: pointer;
    transition: background .15s, color .15s;
  }
  .settings-nav-item:hover { background: var(--bg); color: var(--navy); }
  .settings-nav-item.active { background: var(--blue-bg); color: var(--royal); font-weight: 600; }
  .settings-nav-item svg { flex-shrink: 0; opacity: .7; }
  .settings-nav-item.active svg { opacity: 1; }
  .nav-divider { height: 1px; background: var(--border); margin: 8px 0; }

  /* Form card */
  .form-card { padding: 28px 32px; }
  .form-section-title {
    font-size: 0.95rem; font-weight: 700; color: var(--navy);
    margin-bottom: 4px;
  }
  .form-section-sub {
    font-size: 0.78rem; color: var(--slate); margin-bottom: 24px;
  }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .form-row.single { grid-template-columns: 1fr; max-width: 400px; }
  .form-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
  .form-label { font-size: 0.8rem; font-weight: 600; color: var(--text-mid); }

  .input-wrap { position: relative; display: flex; align-items: center; }
  .input-icon { position: absolute; left: 12px; color: var(--slate-lt); display: flex; align-items: center; pointer-events: none; }
  .form-input {
    width: 100%; padding: 10px 12px 10px 38px;
    border: 1.5px solid var(--border); border-radius: 9px;
    font-family: inherit; font-size: 0.83rem; color: var(--text);
    background: var(--bg); outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-input:focus { border-color: var(--royal-mid); box-shadow: 0 0 0 3px rgba(33,72,232,.09); background: #fff; }
  .form-input::placeholder { color: var(--slate-lt); }
  .form-input.err { border-color: var(--red); box-shadow: 0 0 0 3px rgba(220,38,38,.08); }

  .toggle-pass {
    position: absolute; right: 10px;
    background: none; border: none; cursor: pointer;
    color: var(--slate-lt); display: flex; align-items: center;
    padding: 2px; transition: color .2s;
  }
  .toggle-pass:hover { color: var(--royal); }

  /* Strength bar */
  .strength-wrap { margin-top: 6px; }
  .strength-bar { display: flex; gap: 4px; margin-bottom: 4px; }
  .strength-seg { flex: 1; height: 3px; border-radius: 2px; background: var(--border); transition: background .3s; }
  .strength-label { font-size: 0.7rem; color: var(--slate-lt); }

  .form-divider { height: 1px; background: var(--border); margin: 4px 0 24px; }

  .alert-box {
    display: none; align-items: flex-start; gap: 10px;
    border-radius: 9px; padding: 11px 14px; margin-bottom: 20px;
    font-size: 0.8rem; line-height: 1.5;
  }
  .alert-box.show { display: flex; }
  .alert-box.success { background: var(--green-bg); border: 1px solid var(--green-brd); color: #15803d; }
  .alert-box.error   { background: var(--red-bg);   border: 1px solid var(--red-brd);   color: #b91c1c; }

  .form-actions { display: flex; gap: 10px; align-items: center; margin-top: 8px; }
  .btn-save {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px;
    background: var(--royal-btn); color: #fff;
    border: none; border-radius: 8px;
    font-family: inherit; font-size: 0.85rem; font-weight: 600;
    cursor: pointer;
    transition: background .2s, transform .1s, box-shadow .2s;
    box-shadow: 0 2px 8px rgba(30,63,194,.25);
  }
  .btn-save:hover { background: var(--royal-hover); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,63,194,.35); }
  .btn-save:disabled { opacity: .6; pointer-events: none; }

  .spinner-sm { display: none; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; border-radius: 50%; animation: spin .6s linear infinite; }
  .btn-save.loading .spinner-sm { display: block; }
  .btn-save.loading .btn-label  { display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Policy hints */
  .policy-hints {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 9px; padding: 14px 16px; margin-top: 6px;
  }
  .policy-title { font-size: 0.72rem; font-weight: 700; color: var(--slate); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
  .policy-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.77rem; color: var(--slate); margin-bottom: 6px;
  }
  .policy-item:last-child { margin-bottom: 0; }
  .policy-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--border); flex-shrink: 0; transition: background .3s; }
  .policy-item.met .policy-dot { background: var(--green); }
  .policy-item.met { color: var(--green); }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar-admin.php'; ?>

    <main class="page-content">

      <div class="page-header">
        <h1>Account Settings</h1>
        <p>Manage your profile and security preferences.</p>
      </div>

      <div class="settings-layout">

        <!-- Left nav -->
        <div class="card">
          <div class="settings-nav">
            <a class="settings-nav-item active">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
              Change Password
            </a>
          </div>
        </div>

        <!-- Form -->
        <div class="card form-card">
          <div class="form-section-title">Change Password</div>
          <div class="form-section-sub">Update your account password. You will need your current password to make changes.</div>

          <div class="alert-box" id="alertBox">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="alertIcon"></svg>
            <span id="alertMsg"></span>
          </div>

          <div class="form-row single">
            <div class="form-field">
              <label class="form-label">Current Password</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input type="password" class="form-input" id="currentPass" placeholder="Your current password" autocomplete="current-password">
                <button type="button" class="toggle-pass" onclick="toggleVis('currentPass', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>
          </div>

          <div class="form-divider"></div>

          <div class="form-row single">
            <div class="form-field">
              <label class="form-label">New Password</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input type="password" class="form-input" id="newPass" placeholder="Minimum 8 characters" autocomplete="new-password" oninput="onNewPassChange(this.value)">
                <button type="button" class="toggle-pass" onclick="toggleVis('newPass', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="strength-wrap">
                <div class="strength-bar">
                  <div class="strength-seg" id="seg1"></div>
                  <div class="strength-seg" id="seg2"></div>
                  <div class="strength-seg" id="seg3"></div>
                  <div class="strength-seg" id="seg4"></div>
                </div>
                <div class="strength-label" id="strengthLabel">Enter a new password</div>
              </div>
            </div>

            <div class="form-field">
              <label class="form-label">Confirm New Password</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input type="password" class="form-input" id="confirmPass" placeholder="Repeat new password" autocomplete="new-password">
                <button type="button" class="toggle-pass" onclick="toggleVis('confirmPass', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Policy hints -->
          <div class="policy-hints" style="max-width:400px;margin-bottom:24px">
            <div class="policy-title">Password Requirements</div>
            <div class="policy-item" id="req-len">
              <div class="policy-dot"></div>At least 8 characters
            </div>
            <div class="policy-item" id="req-num">
              <div class="policy-dot"></div>Contains a number (0–9)
            </div>
            <div class="policy-item" id="req-special">
              <div class="policy-dot"></div>Contains a special character (!@#$…)
            </div>
          </div>

          <div class="form-actions">
            <button class="btn-save" id="saveBtn" onclick="changePassword()">
              <div class="spinner-sm"></div>
              <span class="btn-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Update Password
              </span>
            </button>
            <button class="btn" onclick="clearForm()">Cancel</button>
          </div>
        </div>

      </div>
    </main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const eyeOpen = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
const eyeOff  = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;

function toggleVis(inputId, btn) {
  const inp = document.getElementById(inputId);
  const svg = btn.querySelector('svg');
  const isPass = inp.type === 'password';
  inp.type = isPass ? 'text' : 'password';
  svg.innerHTML = isPass ? eyeOff : eyeOpen;
}

function onNewPassChange(val) {
  const segs  = [1,2,3,4].map(i => document.getElementById('seg'+i));
  const label = document.getElementById('strengthLabel');
  const reqLen     = document.getElementById('req-len');
  const reqNum     = document.getElementById('req-num');
  const reqSpecial = document.getElementById('req-special');

  segs.forEach(s => s.style.background = 'var(--border)');
  reqLen.classList.toggle('met',     val.length >= 8);
  reqNum.classList.toggle('met',     /[0-9]/.test(val));
  reqSpecial.classList.toggle('met', /[^a-zA-Z0-9]/.test(val));

  if (!val) { label.textContent = 'Enter a new password'; label.style.color = 'var(--slate-lt)'; return; }
  let score = 0;
  if (val.length >= 8)          score++;
  if (/[0-9]/.test(val))        score++;
  if (/[^a-zA-Z0-9]/.test(val)) score++;
  if (val.length >= 12 && score >= 3) score++;
  const colors = ['','#dc2626','#d97706','#2563eb','#16a34a'];
  const labels = ['','Too short','Fair','Good','Strong'];
  for (let i = 0; i < score; i++) segs[i].style.background = colors[score];
  label.textContent = labels[score] || '';
  label.style.color = colors[score] || 'var(--slate-lt)';
}

async function changePassword() {
  const current  = document.getElementById('currentPass').value;
  const newPass  = document.getElementById('newPass').value;
  const confirm  = document.getElementById('confirmPass').value;
  hideAlert();

  ['currentPass','newPass','confirmPass'].forEach(id => document.getElementById(id).classList.remove('err'));

  if (!current) { document.getElementById('currentPass').classList.add('err'); showAlert('error', 'Please enter your current password.'); return; }
  if (!newPass)  { document.getElementById('newPass').classList.add('err'); showAlert('error', 'Please enter a new password.'); return; }
  if (!confirm)  { document.getElementById('confirmPass').classList.add('err'); showAlert('error', 'Please confirm your new password.'); return; }
  if (newPass !== confirm) { document.getElementById('confirmPass').classList.add('err'); showAlert('error', 'New passwords do not match.'); return; }

  const btn = document.getElementById('saveBtn');
  btn.classList.add('loading');
  try {
    const res  = await fetchWithCsrf('/api/auth/change-password.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ current_password: current, new_password: newPass, confirm_password: confirm }),
    });
    const data = await res.json();
    btn.classList.remove('loading');
    if (data.success) {
      showAlert('success', 'Password updated successfully.');
      clearForm();
    } else {
      showAlert('error', data.message || 'Failed to update password.');
      if (data.message && data.message.toLowerCase().includes('current')) {
        document.getElementById('currentPass').classList.add('err');
      }
    }
  } catch(e) {
    btn.classList.remove('loading');
    showAlert('error', 'Connection error. Please try again.');
  }
}

function showAlert(type, msg) {
  const box  = document.getElementById('alertBox');
  const icon = document.getElementById('alertIcon');
  const text = document.getElementById('alertMsg');
  box.className = `alert-box show ${type}`;
  icon.innerHTML = type === 'success'
    ? '<polyline points="20 6 9 17 4 12"/>'
    : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';
  text.textContent = msg;
}
function hideAlert() { document.getElementById('alertBox').classList.remove('show'); }

function clearForm() {
  ['currentPass','newPass','confirmPass'].forEach(id => {
    const el = document.getElementById(id);
    el.value = '';
    el.classList.remove('err');
    if (el.type === 'text') el.type = 'password';
  });
  document.querySelectorAll('.toggle-pass svg').forEach(svg => svg.innerHTML = eyeOpen);
  onNewPassChange('');
  hideAlert();
}
</script>
</body>
</html>