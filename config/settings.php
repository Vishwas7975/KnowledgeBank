<?php
// config/settings.php — upload rules, allowed types, paths

define('STORAGE_PATH', __DIR__ . '/../storage/documents/');

define('ALLOWED_EXTENSIONS', [
    'pdf', 'docx', 'doc', 'txt', 'odt',
    'xlsx', 'xls', 'csv',
    'pptx', 'ppt',
    'jpg', 'jpeg', 'png', 'gif', 'webp',
    'zip', 'rar',
]);

define('ALLOWED_MIMES', [
    'application/pdf'                                                             => 'pdf',
    'application/msword'                                                          => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'    => 'docx',
    'text/plain'                                                                  => 'txt',
    'application/vnd.oasis.opendocument.text'                                    => 'odt',
    'application/vnd.ms-excel'                                                    => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'          => 'xlsx',
    'text/csv'                                                                    => 'csv',
    'application/vnd.ms-powerpoint'                                               => 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'  => 'pptx',
    'image/jpeg'                                                                  => 'jpg',
    'image/png'                                                                   => 'png',
    'image/gif'                                                                   => 'gif',
    'image/webp'                                                                  => 'webp',
    'application/zip'                                                             => 'zip',
    'application/x-zip-compressed'                                                => 'zip',
    'application/vnd.rar'                                                         => 'rar',
    'application/x-rar-compressed'                                                => 'rar',
]);

define('DEFAULT_MAX_UPLOAD_BYTES', 50 * 1024 * 1024); // 50 MB

// ── ClamAV — BUG FIX: graceful fallback if clamscan not installed ─────────────
// Hostinger shared hosting does NOT have ClamAV. Set to false if unavailable.
// To check: run `which clamscan` in SSH. If no output → set false below.
// Set to true only if ClamAV (clamscan) is installed on the server.
// Hostinger shared hosting does NOT have it — keep false. On a VPS, install ClamAV and set to true.
define('CLAMAV_ENABLED', false);

function scanFile(string $filePath): array {
    if (!CLAMAV_ENABLED) {
        // ClamAV disabled — treat all files as clean (log a warning in production)
        return ['clean' => true];
    }

    // BUG FIX: Check if clamscan binary actually exists before calling exec()
    $clamscan = trim(shell_exec('which clamscan 2>/dev/null') ?? '');
    if (empty($clamscan)) {
        // clamscan not found on this server (common on shared hosting)
        // Return unavailable flag so upload.php can show a clear error
        return [
            'clean'       => false,
            'unavailable' => true,
            'reason'      => 'ClamAV (clamscan) is not installed on this server. Set CLAMAV_ENABLED = false in config/settings.php or install ClamAV.'
        ];
    }

    $output     = [];
    $returnCode = 0;
    exec('clamscan --no-summary ' . escapeshellarg($filePath), $output, $returnCode);

    if ($returnCode === 0) {
        return ['clean' => true];
    }

    return ['clean' => false, 'unavailable' => false, 'reason' => implode(' ', $output)];
}

// ── Mail / SMTP settings ──────────────────────────────────────────────────────
// BUG FIX: Credentials now read from environment variables (secure).
// On Hostinger: set these in hPanel → PHP Config → Environment Variables
// On localhost: create a .env file OR set them in your system environment.
//
// Required env vars:
//   KNOWLEDGEBANK_MAIL_HOST      (default: smtp.gmail.com)
//   KNOWLEDGEBANK_MAIL_PORT      (default: 465)
//   KNOWLEDGEBANK_MAIL_USERNAME  (your Gmail address)
//   KNOWLEDGEBANK_MAIL_PASSWORD  (your Gmail app password)
//   KNOWLEDGEBANK_MAIL_FROM_NAME (default: KnowledgeBank)
//
// No hardcoded fallback credentials — set the env vars above (via .env
// locally, or hPanel → PHP Config → Environment Variables on Hostinger).

define('MAIL_HOST',         getenv('KNOWLEDGEBANK_MAIL_HOST')      ?: 'smtp.gmail.com');
define('MAIL_PORT',   (int)(getenv('KNOWLEDGEBANK_MAIL_PORT')      ?: 465));
define('MAIL_USERNAME',     getenv('KNOWLEDGEBANK_MAIL_USERNAME')   ?: '');
define('MAIL_PASSWORD',     getenv('KNOWLEDGEBANK_MAIL_PASSWORD')   ?: '');
define('MAIL_FROM_ADDRESS', getenv('KNOWLEDGEBANK_MAIL_USERNAME')   ?: '');
define('MAIL_FROM_NAME',    getenv('KNOWLEDGEBANK_MAIL_FROM_NAME')  ?: 'KnowledgeBank');