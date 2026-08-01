<?php
/**
 * TATVAM - Configuration Settings
 * Contains API Credentials, Site Constants, and Database configurations
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

// Cashfree Payment Gateway Credentials
define('CASHFREE_APP_ID', '13547397d3e30d5de7d3d1a22179374531');
define('CASHFREE_SECRET_KEY', 'cfsk_ma_prod_403c547114acb5ce4db767d374712a00_36752f6a');
define('CASHFREE_ENV', 'PRODUCTION'); // 'TEST' for sandbox, 'PRODUCTION' for live

// SMTP Credentials (For sending ebook delivery emails)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM_NAME', 'TATVAM Support Desk');

// Meta Conversion API (CAPI) & Pixel Configurations
define('META_PIXEL_ID', '1300320535510896');
define('META_CAPI_ACCESS_TOKEN', 'EAAkpjFZAtNEEBSJj3fz2I0ytF4BROgqpU2iXo0A1DeyBXGNBRHZCUeij2X68X4ZAUfyTnqV1vXixY4AztrOwkDRFToPaMpc1p0vNg1fIDVll8rh5j61h1hGmipAJJzOFFHxDAZCW08flaAZB3NtSb7fCoMy76u7s5sT1FEDnMtVvxTqPMtVMvqjvj1jV1hgZDZD');
define('META_CAPI_TEST_CODE', 'TEST12345');
