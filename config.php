<?php
/**
 * TATVAM - Configuration Settings
 * Contains Site Constants and Database configurations.
 * All sensitive credentials are loaded from config.secret.php (gitignored).
 */

// Debugging (Set to false in production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Site Configurations
define('SITE_URL', 'https://tatvam.shop'); // Always use tatvam.shop
define('SITE_NAME', 'TATVAM');
define('SUPPORT_EMAIL', 'support@tatvam.shop');

// Database Setup Selector
define('DB_DRIVE', 'sqlite'); // 'sqlite' or 'mysql'

// SQLite configuration
define('SQLITE_DB_PATH', __DIR__ . '/tatvam.db');

// MySQL configuration (Only active if DB_DRIVE is 'mysql')
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_PORT', '3306');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_DB', 'tatvam');

// Load secret credentials from gitignored file
$secret_file = __DIR__ . '/config.secret.php';
if (file_exists($secret_file)) {
    require_once $secret_file;
} else {
    // Fallback placeholders if secret file missing (will disable payment/email/CAPI)
    define('CASHFREE_APP_ID', '');
    define('CASHFREE_SECRET_KEY', '');
    define('CASHFREE_ENV', 'TEST');
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 587);
    define('SMTP_SECURE', 'tls');
    define('SMTP_USER', '');
    define('SMTP_PASS', '');
    define('SMTP_FROM_NAME', 'TATVAM Support Desk');
    define('META_PIXEL_ID', '');
    define('META_CAPI_ACCESS_TOKEN', '');
    define('META_CAPI_TEST_CODE', '');
}
