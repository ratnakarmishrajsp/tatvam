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

// Razorpay API Credentials (Get these from your Razorpay Dashboard)
define('RAZORPAY_KEY_ID', 'rzp_test_XXXXXXXXXXXXXX');
define('RAZORPAY_KEY_SECRET', 'YYYYYYYYYYYYYYYYYYYYYYYY');

// SMTP Credentials (For sending ebook delivery emails)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // 465 for SSL, 587 for TLS
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM_NAME', 'TATVAM Support Desk');

// Meta Conversion API (CAPI) & Pixel Configurations
define('META_PIXEL_ID', '123456789012345');
define('META_CAPI_ACCESS_TOKEN', 'EAAB...YOUR_ACCESS_TOKEN');
define('META_CAPI_TEST_CODE', 'TEST12345'); // Optional: Add testing code for Meta payload verification
