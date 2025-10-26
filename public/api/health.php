<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Check system health
$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'service' => APP_NAME . ' API',
    'version' => APP_VERSION,
    'php_version' => PHP_VERSION,
    'memory_usage' => memory_get_usage(true),
    'memory_peak' => memory_get_peak_usage(true)
];

// Check yt-dlp availability
$ytdlp_check = shell_exec('yt-dlp --version 2>/dev/null');
$health['yt_dlp_available'] = !empty($ytdlp_check);
$health['yt_dlp_version'] = trim($ytdlp_check ?: 'Not available');

// Check disk space
$disk_free = disk_free_space('/tmp');
$disk_total = disk_total_space('/tmp');
$health['disk_usage'] = [
    'free' => $disk_free,
    'total' => $disk_total,
    'used_percent' => round((($disk_total - $disk_free) / $disk_total) * 100, 2)
];

http_response_code(200);
echo json_encode($health, JSON_PRETTY_PRINT);
?>
