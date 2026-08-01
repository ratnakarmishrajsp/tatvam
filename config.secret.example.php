<?php
/**
 * TATVAM - Secret Credentials EXAMPLE (Safe to commit)
 * Copy this file to config.secret.php and fill in your real values.
 */

// Cashfree Payment Gateway Credentials
define('CASHFREE_APP_ID', 'YOUR_CASHFREE_APP_ID');
define('CASHFREE_SECRET_KEY', 'YOUR_CASHFREE_SECRET_KEY');
define('CASHFREE_ENV', 'TEST'); // 'TEST' for sandbox, 'PRODUCTION' for live

// SMTP Credentials
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM_NAME', 'TATVAM Support Desk');

// Meta Conversion API (CAPI) & Pixel
define('META_PIXEL_ID', '123456789012345');
define('META_CAPI_ACCESS_TOKEN', 'EAAB...YOUR_ACCESS_TOKEN');
define('META_CAPI_TEST_CODE', 'TEST12345');
