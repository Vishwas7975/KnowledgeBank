<?php
require_once __DIR__ . '/config/auth.php';
startSession();

// If user is already logged in, redirect to their portal
if (!empty($_SESSION['user_id'])) {
    $dest = ($_SESSION['role'] === 'admin') ? '/admin/dashboard.php' : '/employee/dashboard.php';
    header("Location: $dest");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KnowledgeBank — Enterprise Knowledge & Document Management</title>
<link rel="icon" type="image/png" href="/assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<style>
  body {
    background: radial-gradient(circle at 10% 10%, rgba(191, 219, 254, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 90% 90%, rgba(224, 242, 254, 0.4) 0%, transparent 50%),
                #f0f6ff;
    color: var(--text-main);
    overflow-x: hidden;
  }

  /* Header Navbar */
  .landing-nav {
    height: 68px;
    padding: 0 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border-light);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .nav-logo-img {
    height: 36px;
    width: auto;
    object-fit: contain;
  }

  .nav-menu {
    display: flex;
    align-items: center;
    gap: 24px;
    list-style: none;
  }

  .nav-link {
    text-decoration: none;
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--text-muted);
    transition: color 0.2s;
  }

  .nav-link:hover {
    color: var(--primary-blue);
  }

  /* Hero Container */
  .hero-section {
    max-width: 1080px;
    margin: 0 auto;
    padding: 48px 20px 32px;
    text-align: center;
  }

  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    border-radius: 99px;
    background: var(--blue-bg);
    border: 1px solid var(--blue-border);
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--primary-blue);
    margin-bottom: 16px;
  }

  .hero-title {
    font-size: 2.6rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.8px;
    color: var(--text-main);
    max-width: 820px;
    margin: 0 auto 16px;
  }

  .hero-title span {
    background: var(--royal-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .hero-subtitle {
    font-size: 1.02rem;
    color: var(--text-light);
    max-width: 680px;
    margin: 0 auto 24px;
    line-height: 1.55;
  }

  .hero-cta-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    margin-bottom: 24px;
  }

  .btn-lg {
    padding: 11px 24px;
    font-size: 0.9rem;
    border-radius: var(--radius);
  }

  /* Feature Grid Section */
  .features-section {
    max-width: 1080px;
    margin: 0 auto;
    padding: 32px 20px 48px;
  }

  .section-heading {
    text-align: center;
    margin-bottom: 32px;
  }

  .section-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.4px;
    margin-bottom: 8px;
  }

  .section-sub {
    font-size: 0.9rem;
    color: var(--text-light);
  }

  .feature-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
  }

  .feature-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    padding: 24px 20px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
  }

  .feature-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    border-color: var(--blue-border);
  }

  .f-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--blue-bg);
    color: var(--primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
  }

  .f-title {
    font-size: 1.02rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 6px;
  }

  .f-desc {
    font-size: 0.83rem;
    color: var(--text-muted);
    line-height: 1.6;
  }

  /* Custom Corporate Footer Styling */
  .custom-site-footer {
    background: #e6f0fd;
    border-top: 1px solid #bfdbfe;
    padding: 56px 0 32px;
    margin-top: 60px;
    color: var(--text-main);
  }

  .footer-container {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 24px;
  }

  .footer-grid {
    display: grid;
    grid-template-columns: 1.8fr 1fr 1fr 1.3fr;
    gap: 40px;
    margin-bottom: 40px;
  }

  .f-brand-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
  }

  .f-logo-img {
    height: 32px;
    width: auto;
    object-fit: contain;
  }

  .f-brand-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.4px;
  }

  .f-brand-desc {
    font-size: 0.84rem;
    color: #475569;
    line-height: 1.6;
    max-width: 320px;
    margin-bottom: 24px;
  }

  .f-social-links {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .social-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #ffffff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08);
    transition: all 0.2s;
    text-decoration: none;
  }

  .social-icon-box:hover {
    background: #dbeafe;
    color: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
  }

  .f-col-title {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #0f172a;
    margin-bottom: 20px;
  }

  .f-links-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .f-links-list a {
    font-size: 0.86rem;
    color: #475569;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
  }

  .f-links-list a:hover {
    color: #2563eb;
  }

  .footer-bottom-bar {
    border-top: 1px solid #bfdbfe;
    padding-top: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    color: #64748b;
    flex-wrap: wrap;
    gap: 16px;
  }

  .f-legal-pills {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .pill-btn {
    padding: 7px 18px;
    border-radius: 99px;
    background: #ffffff;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
    transition: all 0.2s;
  }

  .pill-btn:hover {
    background: #dbeafe;
    color: #1d4ed8;
  }

  /* Enterprise Trust Badges Grid */
  .trust-badges-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    margin-top: 24px;
    flex-wrap: wrap;
  }

  .trust-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.76rem;
    font-weight: 600;
    color: #475569;
  }

  .trust-pill svg { color: #2563eb; }

  @media (max-width: 900px) {
    .footer-grid {
      grid-template-columns: 1fr 1fr;
      gap: 32px;
    }
  }

  @media (max-width: 580px) {
    .footer-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>

<!-- Header Navigation -->
<nav class="landing-nav">
  <div style="display:flex; align-items:center; gap:12px;">
    <img src="/assets/images/logo.png" alt="KnowledgeBank Logo" class="nav-logo-img" />
    <span style="font-size:1.35rem; font-weight:800; color:#0f172a; letter-spacing:-0.5px;">KnowledgeBank <span style="color:#2563eb;">AI</span></span>
  </div>

  <ul class="nav-menu">
    <li><a href="#features" class="nav-link">Platform Features</a></li>
    <li><a href="#security" class="nav-link">Security & Compliance</a></li>
    <li><a href="#workflow" class="nav-link">Workflow</a></li>
  </ul>

  <div>
    <!-- Top Right Action: Sign In Button -->
    <a href="/login.php" class="btn btn-primary">
      <span>Portal Sign In</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="5" y1="12" x2="19" y2="12"/>
        <polyline points="12 5 19 12 12 19"/>
      </svg>
    </a>
  </div>
</nav>

<!-- Hero Banner -->
<section class="hero-section">
  <div class="hero-badge">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    <span>Enterprise Knowledge Governance</span>
  </div>

  <h1 class="hero-title">Centralized <span>Knowledge Intelligence</span> & Secure Vault</h1>

  <p class="hero-subtitle">
    Unify organizational SOPs, engineering documentation, compliance repositories, and strategic assets into an intelligent, zero-trust knowledge core with instant retrieval and multi-layer clearance.
  </p>

  <div class="hero-cta-group">
    <a href="/login.php" class="btn btn-primary btn-lg">Access Knowledge Portal</a>
    <a href="#features" class="btn btn-secondary btn-lg">Explore Features</a>
  </div>

  <div class="trust-badges-row">
    <div class="trust-pill">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span>ISO 27001 & SOC 2 Compliant Standards</span>
    </div>
    <div class="trust-pill">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span>Real-Time ClamAV Malware Defense</span>
    </div>
    <div class="trust-pill">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <span>Zero-Trust Access Controls</span>
    </div>
  </div>
</section>

<!-- Platform Features Section -->
<section class="features-section" id="features">
  <div class="section-heading">
    <h2 class="section-title">Engineered for Global Enterprise Standards</h2>
    <p class="section-sub">Comprehensive document governance designed for multi-tier organizational compliance, speed, and absolute confidentiality.</p>
  </div>

  <div class="feature-cards-grid">
    <div class="feature-card">
      <div class="f-icon-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
        </svg>
      </div>
      <div class="f-title">Governed Download Gateways</div>
      <div class="f-desc">
        Proprietary Office documents (.docx, .xlsx, .pptx) are protected by executive download approval gateways, preventing unauthorized data exfiltration.
      </div>
    </div>

    <div class="feature-card">
      <div class="f-icon-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <div class="f-title">Confidential Vault Lockers</div>
      <div class="f-desc">
        Isolate sensitive M&A, HR, or financial directories behind bcrypt-hashed folder passkeys. Users must authenticate before accessing restricted repositories.
      </div>
    </div>

    <div class="feature-card">
      <div class="f-icon-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div class="f-title">Real-Time Threat Inspection</div>
      <div class="f-desc">
        Every document upload passes deep binary signature checks and ClamAV antivirus analysis to guarantee zero malicious payloads enter disk storage.
      </div>
    </div>

    <div class="feature-card">
      <div class="f-icon-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 20h9"/>
          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
        </svg>
      </div>
      <div class="f-title">Forensic Audit & Event Trail</div>
      <div class="f-desc">
        Immutable activity logs track logins, document view durations, upload timestamps, and IP addresses with instant CSV compliance exports.
      </div>
    </div>
  </div>
</section>

<!-- Security & Compliance Section -->
<section class="features-section" id="security" style="background:#ffffff; border-radius:24px; padding:48px 36px; margin-top:20px; border:1px solid var(--border-light);">
  <div class="section-heading">
    <h2 class="section-title">Enterprise Security & Compliance Architecture</h2>
    <p class="section-sub">Bank-grade cryptographic safeguards, zero-trust binary validation, and continuous risk mitigation.</p>
  </div>

  <div class="feature-cards-grid">
    <div class="feature-card" style="background:#f8fafc;">
      <div class="f-icon-box" style="background:#eff6ff; color:#2563eb;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <div class="f-title">TLS 1.3 & Storage AES Encryption</div>
      <div class="f-desc">Data in transit is protected via TLS 1.3 protocols, while persistent files reside within isolated disk vaults backed by strict access policies.</div>
    </div>

    <div class="feature-card" style="background:#f8fafc;">
      <div class="f-icon-box" style="background:#f0fdf4; color:#10b981;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div class="f-title">Binary MIME Signature Verification</div>
      <div class="f-desc">File extensions alone are never trusted. Internal binary headers are inspected using PHP finfo to eliminate script execution risks.</div>
    </div>

    <div class="feature-card" style="background:#f8fafc;">
      <div class="f-icon-box" style="background:#fffbeb; color:#d97706;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        </svg>
      </div>
      <div class="f-title">Role-Based Access Control (RBAC)</div>
      <div class="f-desc">Strict authorization barriers ensure employees only access files allocated to their specific clearance level or department.</div>
    </div>
  </div>
</section>

<!-- Workflow Section -->
<section class="features-section" id="workflow" style="margin-top:20px;">
  <div class="section-heading">
    <h2 class="section-title">Enterprise Knowledge Life-Cycle Governance</h2>
    <p class="section-sub">Four structured stages ensuring complete security from initial ingestion to controlled access.</p>
  </div>

  <div class="feature-cards-grid">
    <div class="feature-card">
      <div style="font-size:0.75rem; font-weight:800; color:#2563eb; letter-spacing:1px; margin-bottom:8px;">STAGE 01</div>
      <div class="f-title">Ingestion & Threat Analysis</div>
      <div class="f-desc">Document uploads undergo MIME binary signature inspection and real-time ClamAV malware screening before disk storage.</div>
    </div>

    <div class="feature-card">
      <div style="font-size:0.75rem; font-weight:800; color:#2563eb; letter-spacing:1px; margin-bottom:8px;">STAGE 02</div>
      <div class="f-title">Classification & Vault Security</div>
      <div class="f-desc">Knowledge assets are indexed into public or confidential passkey-locked folders with granular department tags.</div>
    </div>

    <div class="feature-card">
      <div style="font-size:0.75rem; font-weight:800; color:#2563eb; letter-spacing:1px; margin-bottom:8px;">STAGE 03</div>
      <div class="f-title">Governed Approval Gateway</div>
      <div class="f-desc">Restricted document downloads trigger compliance review requests routed directly to the Executive Admin Panel.</div>
    </div>

    <div class="feature-card">
      <div style="font-size:0.75rem; font-weight:800; color:#2563eb; letter-spacing:1px; margin-bottom:8px;">STAGE 04</div>
      <div class="f-title">Zero-Footprint In-App Preview</div>
      <div class="f-desc">Authorized personnel view documents directly inside a secure lightbox viewer without saving binary files to unencrypted local drives.</div>
    </div>
  </div>
</section>

<!-- Custom Corporate Footer -->
<footer class="custom-site-footer">
  <div class="footer-container">
    <div class="footer-grid">
      <!-- Col 1: Brand Info -->
      <div class="footer-brand-col">
        <div class="f-brand-header">
          <img src="/assets/images/logo.png" alt="KnowledgeBank Logo" class="f-logo-img" />
          <span class="f-brand-title">KnowledgeBank<span style="color:#2563eb;">AI</span></span>
        </div>
        <p class="f-brand-desc">
          Enterprise AI & automation agency building the platforms that power next-generation businesses.
        </p>
        <div class="f-social-links">
          <a href="https://www.linkedin.com/company/99024024/" target="_blank" class="social-icon-box" title="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.25V10.9H6.46M7.86 6.78a1.62 1.62 0 1 0 0 3.24 1.62 1.62 0 0 0 0-3.24z"/></svg>
          </a>
          <a href="#" target="_blank" class="social-icon-box" title="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="#" target="_blank" class="social-icon-box" title="YouTube">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
          </a>
        </div>
      </div>

      <!-- Col 2: Platform Links -->
      <div class="footer-col">
        <h4 class="f-col-title">COMPANY</h4>
        <ul class="f-links-list">
          <li><a href="/">Home</a></li>
          <li><a href="#features">Platform Features</a></li>
          <li><a href="#security">Security & Compliance</a></li>
          <li><a href="#workflow">Workflow</a></li>
          <li><a href="/login.php">Portal Sign In</a></li>
        </ul>
      </div>

      <!-- Col 3: System Features -->
      <div class="footer-col">
        <h4 class="f-col-title">SYSTEM</h4>
        <ul class="f-links-list">
          <li><a href="#security">ClamAV Virus Scan</a></li>
          <li><a href="#features">Confidential Folder Passwords</a></li>
          <li><a href="#workflow">Download Approvals</a></li>
          <li><a href="#security">Audit Trail Logging</a></li>
        </ul>
      </div>

      <!-- Col 4: Contact Info -->
      <div class="footer-col">
        <h4 class="f-col-title">CONTACT</h4>
        <ul class="f-links-list f-contact-list">
          <li><a href="mailto:hr@example.com">hr@example.com</a></li>
          <li><a href="mailto:support@example.com">support@example.com</a></li>
          <li><a href="tel:+918106975810">+91 81069 75810</a></li>
          <li><a href="tel:+919618013827">+91 96180 13827</a></li>
        </ul>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-bar">
      <div class="f-copyright">&copy; 2026 <strong style="color:#0f172a;">KnowledgeBank</strong>. All rights reserved.</div>
      <div class="f-legal-pills">
        <a href="#" class="pill-btn">Privacy Policy</a>
        <a href="#" class="pill-btn">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>