<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    SaveMedia::errorResponse('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['url']) || empty($input['url'])) {
    SaveMedia::errorResponse('URL is required');
}

$url = trim($input['url']);

if (!SaveMedia::validateUrl($url)) {
    SaveMedia::errorResponse('Invalid or unsupported URL');
}

$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!SaveMedia::rateLimitCheck($client_ip)) {
    SaveMedia::errorResponse('Rate limit exceeded. Please try again later.', 429);
}

try {
    $result = SaveMedia::getMediaInfo($url);
    SaveMedia::jsonResponse($result);
} catch (Exception $e) {
    error_log("Media info error: " . $e->getMessage());
    SaveMedia::errorResponse($e->getMessage(), 500);
}
?>
