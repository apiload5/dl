<?php
// SaveMedia Configuration - Self Installing
define('APP_NAME', 'SaveMedia');
define('APP_VERSION', '2.0');
define('APP_ENV', 'production');

// Set PHP Configuration Programmatically
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');
ini_set('max_file_uploads', '20');
ini_set('allow_url_fopen', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');
ini_set('display_errors', '0');
ini_set('error_reporting', '0');

// Application Settings
define('API_BASE_URL', '/api');
define('UPLOAD_DIR', '/tmp/downloads');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
define('RATE_LIMIT', 10); // requests per minute

// Supported Platforms
define('ALLOWED_DOMAINS', [
    'youtube.com', 'youtu.be', 'instagram.com', 'facebook.com',
    'twitter.com', 'x.com', 'tiktok.com', 'vimeo.com', 'dailymotion.com'
]);

// Auto-create directories
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0777, true);
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'use_strict_mode' => true,
        'gc_maxlifetime' => 3600
    ]);
}

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Auto-install yt-dlp function
function installYtDlp() {
    $install_commands = [
        'python3 -m pip install --user yt-dlp 2>/dev/null',
        'pip3 install --user yt-dlp 2>/dev/null',
        'pip install --user yt-dlp 2>/dev/null'
    ];
    
    foreach ($install_commands as $cmd) {
        @shell_exec($cmd);
    }
}

// Check and install yt-dlp if needed
if (!shell_exec('which yt-dlp 2>/dev/null') && !file_exists('/home/webapp/.local/bin/yt-dlp')) {
    installYtDlp();
}
?>
