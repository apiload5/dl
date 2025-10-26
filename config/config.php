<?php
// SaveMedia Configuration
define('APP_NAME', 'SaveMedia');
define('APP_VERSION', '2.0');
define('API_BASE_URL', '/api');
define('UPLOAD_DIR', '/tmp/downloads');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('RATE_LIMIT', 10); // requests per minute
define('ALLOWED_DOMAINS', [
    'youtube.com', 'youtu.be', 'instagram.com', 'facebook.com',
    'twitter.com', 'x.com', 'tiktok.com', 'vimeo.com'
]);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Session configuration
session_start();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
