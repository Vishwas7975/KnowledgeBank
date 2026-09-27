# 📚 KnowledgeBank

**A role-based PHP document management system** — folders, uploads, download-request approvals, full audit logging and admin/employee portals, ready to drop into any project.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-PDO-4479A1?logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-ready-885630?logo=composer&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)
![PRs](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)

KnowledgeBank is a framework-free PHP backend + server-rendered frontend for internal document/knowledge management: employees upload documents into folders, admins approve or reject them, sensitive downloads go through a request/approval flow, and every action is written to an audit trail.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Roles](#roles)
- [Security & Privacy](#security--privacy)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [Author](#author)
- [License](#license)

---

## Features

- ✅ **Role-based access** — separate admin and employee portals, enforced server-side via session + `requireAdmin()` / `requireAuth()`
- ✅ **Folders & documents** — upload, organize into folders, optionally mark a folder confidential (password-protected)
- ✅ **Approval workflow** — uploads go to `pending` until an admin approves or rejects them
- ✅ **Download requests** — sensitive documents can require an admin-approved download request before they're released
- ✅ **Full audit trail** — every login, upload, download, delete and user-management action is logged with IP, browser and OS
- ✅ **OTP-based password reset** — 6-digit code emailed via SMTP, constant-time comparison
- ✅ **CSRF protection & session hardening** — HttpOnly + SameSite cookies, per-role session timeouts, brute-force lockout after repeated failed logins
- ✅ **Env-driven config** — nothing sensitive is hardcoded; everything sensitive lives in `.env`

---

## Tech Stack

| Layer          | Technology                                                    |
|----------------|-----------------------------------------------------------------|
| Language       | PHP 8.0+                                                         |
| Database       | MySQL (via PDO, prepared statements throughout)                 |
| Email          | PHPMailer + SMTP                                                 |
| Dependency Mgr | Composer                                                          |
| Web server     | Apache (`.htaccess` included) — also runs on Nginx or the PHP built-in server |

---

## Project Structure

```
knowledgebank/
├── admin/                 ← admin-only pages (dashboard, users, documents, audit logs, settings)
├── employee/               ← employee pages (dashboard, documents, my uploads)
├── api/                     ← JSON endpoints, grouped by resource (auth, documents, folders, users, audit, ...)
├── assets/                   ← css / js / images
├── config/
│   ├── env.php               ← tiny dependency-free .env loader
│   ├── db.php                  ← PDO connection singleton
│   ├── auth.php                 ← session helpers, requireAuth()/requireAdmin(), CSRF, activity logging
│   └── settings.php              ← upload rules, allowed file types, mail settings
├── helpers/
│   ├── Mailer.php                 ← PHPMailer SMTP wrapper + OTP email template
│   └── Response.php                ← standardized JSON response helper
├── includes/                        ← shared sidebar/topbar/footer partials
├── database/
│   └── schema.sql                    ← full schema + a bootstrap admin account (no other data)
├── storage/documents/                 ← uploaded files land here (empty in this repo — see below)
├── vendor/                             ← Composer packages (regenerate with `composer install`)
├── index.php, login.php, forgot-password.php
├── .htaccess
├── .env.example
└── composer.json
```

---

## Getting Started

### Prerequisites

- PHP 8.0+
- MySQL (or MariaDB)
- [Composer](https://getcomposer.org/)

### 1. Install dependencies

```bash
composer install
```

### 2. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE knowledgebank"
mysql -u root -p knowledgebank < database/schema.sql
```

This creates all tables and one bootstrap admin account:

| Email | Password |
|---|---|
| `admin@example.com` | `ChangeMe123!` |

> ⚠️ **Log in and change this password immediately** — it's a placeholder, not a real credential.

### 3. Configure environment variables

```bash
cp .env.example .env
```

Then edit `.env` with your own DB and SMTP credentials. On shared hosting (e.g. Hostinger), set these instead via your control panel's PHP environment variables — the `.env` file is only needed for local development.

### 4. Run it

```bash
php -S localhost:8000
```

Or point Apache/Nginx at the project root — `.htaccess` handles routing and blocks direct access to `config/`, `helpers/`, `vendor/` and `storage/`.

---

## Roles

| Role | Access |
|---|---|
| **Admin** | Everything — user management, folder/document approval, download-request review, audit logs, system settings |
| **Employee** | Upload documents (pending admin approval), browse approved documents, request downloads for restricted files, manage their own uploads |

---

## Security & Privacy

- **`.env` is gitignored.** All secrets (DB credentials, SMTP credentials) live only in your local `.env`, never in source control. `.env.example` ships with placeholders only.
- **`/vendor/`, `/storage/documents/`, `*.sql`, `*.log` and `composer.lock` are gitignored.** Dependencies are restored with `composer install`; uploaded files, dumps and logs are never committed.
- **No hardcoded credentials anywhere in the codebase** — `config/db.php` and `config/settings.php` read exclusively from environment variables, with empty-string defaults rather than real fallback values.
- **`database/schema.sql` ships with structure only** — no real users, logs or documents. It contains just the schema and one placeholder bootstrap admin.
- **Enforced CSRF Protection** — `validateCsrf()` is wired up on every POST-handling API endpoint (`/api/...`), checking `X-CSRF-Token` headers or JSON body tokens.
- **Rate Limiting & Anti-Abuse** —
  - `login.php`: Per-email lockout after 5 failed attempts + IP-level rate limiting across all emails.
  - `forgot-password.php`: 1 OTP request per email per 60 seconds + IP-level rate limiting (max 5 requests per 15 mins).
- **OTP Brute-Force Throttling** — `reset-password.php` locks and invalidates OTP reset codes after 5 wrong attempts.
- **Constant-Time Comparison** — OTP and token checks use `hash_equals()` to prevent timing attacks.
- **Secure File Preview** — Inline rendering excludes executable types (like SVG).
- **ClamAV Malware Protection Warning** — Admin dashboard & settings display a clear warning banner when malware scanning is inactive.

Before deploying: change the bootstrap admin password, set your own SMTP credentials and review `ALLOWED_EXTENSIONS` / `DEFAULT_MAX_UPLOAD_BYTES` in `config/settings.php` for your use case.

---

## Roadmap

- [ ] Automated tests for the approval and download-request workflows
- [x] Rate limiting on `forgot-password` and IP-based rate limiting on auth endpoints
- [x] CSRF protection wired into all state-changing API POST handlers
- [x] OTP guess-throttling & lockout after 5 failed attempts
- [ ] Optional virus scanning on upload (ClamAV hook already stubbed in `config/settings.php`, disabled by default for shared hosting)
- [ ] Bulk document actions (approve/reject/delete multiple at once)

---

## Contributing

This project is meant to be a solid starting structure — fork it, extend it or open a PR.

1. Fork the repo and create a feature branch.
2. Keep the existing style: PDO + prepared statements, env-driven config, no framework.
3. New API endpoints go under `api/<resource>/`; new admin/employee pages go under `admin/` or `employee/` and get an entry in the matching `includes/sidebar-*.php`.
4. Open a PR describing what changed and why.

Ideas if you're looking for something to build: see the [Roadmap](#roadmap) above.

---

## Author

**Vishwas S** — Full-Stack Developer | Data Analyst
- GitHub: [github.com/Vishwas7975](https://github.com/Vishwas7975)
- LinkedIn: [linkedin.com/in/vishwas-s-48205327a](https://www.linkedin.com/in/vishwas-s-48205327a)

---

## License

MIT — see [LICENSE](LICENSE).
