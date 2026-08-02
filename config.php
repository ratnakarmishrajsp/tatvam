<?php
/**
 * TATVAM - Configuration Settings
 * Contains API Credentials, Site Constants, and Database configurations
 */

if (file_exists(__DIR__ . '/config.secret.php')) {
    require_once __DIR__ . '/config.secret.php';
}

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

// Cashfree Payment Gateway Credentials
if (!defined('CASHFREE_APP_ID')) define('CASHFREE_APP_ID', '13547397d3e30d5de7d3d1a22179374531');
if (!defined('CASHFREE_SECRET_KEY')) define('CASHFREE_SECRET_KEY', 'cfsk_ma_prod_403c547114acb5ce4db767d374712a00_36752f6a');
if (!defined('CASHFREE_ENV')) define('CASHFREE_ENV', 'PRODUCTION'); // 'TEST' for sandbox, 'PRODUCTION' for live

// SMTP Credentials (For sending ebook delivery emails)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', 'tls');
if (!defined('SMTP_USER')) define('SMTP_USER', 'your-email@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'your-app-password');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'TATVAM Support Desk');

// Meta Conversion API (CAPI) & Pixel Configurations
if (!defined('META_PIXEL_ID')) define('META_PIXEL_ID', '1300320535510896');
if (!defined('META_CAPI_ACCESS_TOKEN')) define('META_CAPI_ACCESS_TOKEN', 'EAAkpjFZAtNEEBSJj3fz2I0ytF4BROgqpU2iXo0A1DeyBXGNBRHZCUeij2X68X4ZAUfyTnqV1vXixY4AztrOwkDRFToPaMpc1p0vNg1fIDVll8rh5j61h1hGmipAJJzOFFHxDAZCW08flaAZB3NtSb7fCoMy76u7s5sT1FEDnMtVvxTqPMtVMvqjvj1jV1hgZDZD');
if (!defined('META_CAPI_TEST_CODE')) define('META_CAPI_TEST_CODE', 'TEST12345');
