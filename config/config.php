<?php
// SaveMedia Configuration - Compatible with .ebextensions
define('APP_NAME', 'SaveMedia');
define('APP_VERSION', '2.0');
define('APP_ENV', $_SERVER['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_SERVER['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Paths
define('API_BASE_URL', '/api');
define('UPLOAD_DIR', '/tmp/downloads');
define('LOG_DIR', '/var/log/savemedia');

// Limits (from environment or defaults)
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('RATE_LIMIT', 10); // requests per minute
define('MAX_EXECUTION_TIME', 300); // 5 minutes

// Supported platforms
define('ALLOWED_DOMAINS', [
    'youtube.com', 'youtu.be', 'instagram.com', 'facebook.com',
    'twitter.com', 'x.com', 'tiktok.com', 'vimeo.com', 'dailymotion.com'
]);

// Error reporting based on environment
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/var/log/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Additional PHP settings (complementing .ebextensions)
ini_set('max_execution_time', MAX_EXECUTION_TIME);
ini_set('default_socket_timeout', 60);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true,
        'gc_maxlifetime' => 3600
    ]);
}

// CORS headers (complementing nginx config)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Ensure directories exist
$directories = [UPLOAD_DIR, LOG_DIR];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}
?>
