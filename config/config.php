<?php
/**
 * ============================================================
 *  POSUNG HRIS – Configuration File
 *  Công ty TNHH Cơ khí Kỹ thuật Xây dựng Po Sung
 * ============================================================
 *  Compatible with XAMPP (Apache + PHP 8.x + MySQL/MariaDB)
 * ============================================================
 */

// ── Application ─────────────────────────────────────────────
define('APP_NAME',    'POSUNG HRIS');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    'http://localhost/posung_hris/public');
define('APP_ROOT',    dirname(__DIR__) . '/app');
define('ROOT_PATH',   dirname(__DIR__));

// ── Database (XAMPP defaults) ───────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'posung_hris');
define('DB_USER',    'root');
define('DB_PASS',    '');              // XAMPP default: no password
define('DB_CHARSET', 'utf8mb4');

// ── Session ─────────────────────────────────────────────────
define('SESSION_NAME',     'POSUNG_HRIS_SID');
define('SESSION_LIFETIME', 7200);      // 2 hours

// ── Upload ──────────────────────────────────────────────────
define('UPLOAD_DIR',       ROOT_PATH . '/public/uploads');
define('MAX_UPLOAD_SIZE',  10 * 1024 * 1024);  // 10 MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']);

// ── Timezone ────────────────────────────────────────────────
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ── Error Reporting (Development) ───────────────────────────
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
