<?php
require_once __DIR__ . '/config/auth.php';
startSession();

if (!empty($_SESSION['user_id'])) {
    $dest = $_SESSION['role'] === 'admin' ? '/admin/dashboard.php' : '/employee/dashboard.php';
    header("Location: $dest");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — KnowledgeBank</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 10% 10%, rgba(191, 219, 254, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 90% 90%, rgba(224, 242, 254, 0.4) 0%, transparent 50%),
                #f0f6ff;
    position: relative;
    overflow: hidden;
  }

  .card-container {
    width: 100%;
    max-width: 440px;
    padding: 20px;
    position: relative;
    z-index: 10;
  }

  .reset-card {
    background: #ffffff;
    border: 1px solid var(--border-accent);
    border-radius: 24px;
    padding: 40px 36px;
    box-shadow: var(--shadow-xl);
    color: var(--text-main);
  }

  .brand-header {
    text-align: center;
    margin-bottom: 32px;
  }

  .brand-logo-img {
    height: 48px;
    width: auto;
    object-fit: contain;
    margin-bottom: 12px;
  }

  .brand-sub {
    font-size: 0.78rem;
    color: var(--text-light);
    margin-top: 2px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 8px;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-wrapper .input-icon-left {
    position: absolute;
    left: 14px;
    color: var(--text-subtle);
    pointer-events: none;
  }

  .password-eye-toggle {
    position: absolute;
    right: 14px;
    background: transparent;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .form-input {
    width: 100%;
    padding: 12px 44px 12px 44px;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: var(--radius);
    font-family: inherit;
    font-size: 0.88rem;
    color: var(--text-main);
    outline: none;
    transition: all 0.2s;
  }

  .form-input:hover {
    border-color: #94a3b8;
    background: #ffffff;
  }

  .form-input:focus {
    border-color: var(--primary-blue);
    background: #ffffff;
    box-shadow: 0 0 0 4px var(--blue-glow);
  }

  .btn-submit {
    width: 100%;
    padding: 13px;
    border-radius: var(--radius);
    background: var(--royal-gradient);
    border: none;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.92rem;
    cursor: pointer;
    box-shadow: var(--shadow-royal);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.4);
  }

  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary-blue);
    text-decoration: none;
  }

  .back-link:hover { text-decoration: underline; }

  .step-panel { display: none; }
  .step-panel.active { display: block; }
</style>
</head>
<body>

<div class="card-container">
  <div class="reset-card">
    <div class="brand-header">
      <div style="display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:8px;">
        <img src="/assets/images/logo.png" alt="KnowledgeBank Logo" class="brand-logo-img" style="margin-bottom:0;" />
        <span style="font-size:1.45rem; font-weight:800; color:#0f172a; letter-spacing:-0.5px;">KnowledgeBank <span style="color:#2563eb;">AI</span></span>
      </div>
      <div class="brand-sub">Password Reset & Account Recovery</div>
    </div>

    <!-- Step 1: Request OTP -->
    <div id="step-1" class="step-panel active">
      <form onsubmit="handleRequestOtp(event)">
        <div class="form-group">
          <label class="form-label">Registered Email</label>
          <div class="input-wrapper">
            <input type="email" id="email" class="form-input" style="padding-right:16px;" placeholder="user@knowledgebank.com" required />
            <svg class="input-icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
        </div>
        <button type="submit" class="btn-submit" id="btn-step-1">Send Verification Code</button>
      </form>
    </div>

    <!-- Step 2 & 3: Reset Password with OTP -->
    <div id="step-2" class="step-panel">
      <form onsubmit="handleResetPassword(event)">
        <div class="form-group">
          <label class="form-label">6-Digit OTP Code</label>
          <input type="text" id="otp" class="form-input" style="padding-left:16px; font-family:'JetBrains Mono', monospace; text-align:center; letter-spacing:4px;" placeholder="123456" maxlength="6" required />
        </div>

        <div class="form-group">
          <label class="form-label">New Password</label>
          <div class="input-wrapper">
            <input type="password" id="new_password" class="form-input" placeholder="••••••••••••" required />
            <svg class="input-icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <button type="button" class="password-eye-toggle" onclick="togglePasswordVisibility('new_password', this)" title="Show/Hide Password">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="btn-step-2">Reset Password</button>
      </form>
    </div>

    <a href="/login.php" class="back-link">Back to Sign In</a>
  </div>
</div>

<div id="toast-container"></div>

<script>
let resetEmail = '';

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
  } else {
    input.type = 'password';
    btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
  }
}

function showToast(message, type = 'success', title = '') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  const iconMap = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
  const titleText = title || (type.charAt(0).toUpperCase() + type.slice(1));
  toast.innerHTML = `<div class="toast-icon">${iconMap[type] || 'ℹ'}</div><div class="toast-body"><div class="toast-title">${titleText}</div><div class="toast-message">${message}</div></div>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(40px)';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

async function handleRequestOtp(e) {
  e.preventDefault();
  resetEmail = document.getElementById('email').value.trim();
  const btn = document.getElementById('btn-step-1');
  btn.disabled = true;
  btn.textContent = 'Sending Code...';

  try {
    const res = await fetch('/api/auth/forgot-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: resetEmail })
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message || 'OTP code sent to email.', 'success');
      document.getElementById('step-1').classList.remove('active');
      document.getElementById('step-2').classList.add('active');
    } else {
      showToast(data.message || 'Failed to send OTP.', 'error');
    }
  } catch (err) {
    showToast('Network error.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Send Verification Code';
  }
}

async function handleResetPassword(e) {
  e.preventDefault();
  const otp = document.getElementById('otp').value.trim();
  const new_password = document.getElementById('new_password').value.trim();
  const btn = document.getElementById('btn-step-2');
  btn.disabled = true;
  btn.textContent = 'Resetting...';

  try {
    const res = await fetch('/api/auth/reset-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: resetEmail, otp, password: new_password })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Password reset successful! Redirecting to login...', 'success');
      setTimeout(() => window.location.href = '/login.php', 1200);
    } else {
      showToast(data.message || 'Reset failed.', 'error');
    }
  } catch (err) {
    showToast('Network error.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Reset Password';
  }
}
</script>

</body>
</html>