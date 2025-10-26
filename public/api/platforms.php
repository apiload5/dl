<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

$platforms = [
    'youtube' => [
        'name' => 'YouTube',
        'formats' => ['mp4', 'webm', 'mp3'],
        'qualities' => ['4K', '1080p', '720p', '480p', '360p'],
        'example' => 'https://youtube.com/watch?v=...'
    ],
    'instagram' => [
        'name' => 'Instagram',
        'formats' => ['mp4', 'jpg'],
        'qualities' => ['Original'],
        'example' => 'https://instagram.com/p/...'
    ],
    'facebook' => [
        'name' => 'Facebook',
        'formats' => ['mp4'],
        'qualities' => ['HD', 'SD'],
        'example' => 'https://facebook.com/watch?v=...'
    ],
    'twitter' => [
        'name' => 'Twitter/X',
        'formats' => ['mp4', 'gif'],
        'qualities' => ['Original'],
        'example' => 'https://twitter.com/user/status/...'
    ],
    'tiktok' => [
        'name' => 'TikTok',
        'formats' => ['mp4'],
        'qualities' => ['Original', 'No Watermark'],
        'example' => 'https://tiktok.com/@user/video/...'
    ],
    'vimeo' => [
        'name' => 'Vimeo',
        'formats' => ['mp4'],
        'qualities' => ['4K', '1080p', '720p', '480p'],
        'example' => 'https://vimeo.com/...'
    ],
    'dailymotion' => [
        'name' => 'Dailymotion',
        'formats' => ['mp4'],
        'qualities' => ['1080p', '720p', '480p', '360p'],
        'example' => 'https://dailymotion.com/video/...'
    ]
];

SaveMedia::jsonResponse([
    'success' => true,
    'supported_platforms' => $platforms,
    'total_platforms' => count($platforms),
    'api_version' => APP_VERSION
]);
?>
