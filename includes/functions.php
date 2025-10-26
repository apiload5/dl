<?php
class SaveMedia {
    
    public static function detectPlatform($url) {
        $url = strtolower($url);
        $platforms = [
            'youtube' => ['youtube.com', 'youtu.be'],
            'instagram' => ['instagram.com'],
            'facebook' => ['facebook.com', 'fb.watch'],
            'twitter' => ['twitter.com', 'x.com'],
            'tiktok' => ['tiktok.com'],
            'vimeo' => ['vimeo.com'],
            'dailymotion' => ['dailymotion.com']
        ];
        
        foreach ($platforms as $platform => $domains) {
            foreach ($domains as $domain) {
                if (strpos($url, $domain) !== false) {
                    return $platform;
                }
            }
        }
        return 'generic';
    }
    
    public static function validateUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $domain = parse_url($url, PHP_URL_HOST);
        foreach (ALLOWED_DOMAINS as $allowed) {
            if (strpos($domain, $allowed) !== false) {
                return true;
            }
        }
        return false;
    }
    
    public static function rateLimitCheck($ip) {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }
        
        $current_time = time();
        $window = 60; // 1 minute
        
        // Clean old requests
        $_SESSION['rate_limit'] = array_filter(
            $_SESSION['rate_limit'],
            function($timestamp) use ($current_time, $window) {
                return ($current_time - $timestamp) < $window;
            }
        );
        
        if (count($_SESSION['rate_limit']) >= RATE_LIMIT) {
            return false;
        }
        
        $_SESSION['rate_limit'][] = $current_time;
        return true;
    }
    
    public static function getMediaInfo($url) {
        $command = "yt-dlp --dump-json --no-warnings " . escapeshellarg($url) . " 2>/dev/null";
        $output = shell_exec($command);
        
        if (!$output) {
            throw new Exception("Failed to extract media information");
        }
        
        $info = json_decode($output, true);
        if (!$info) {
            throw new Exception("Invalid media information");
        }
        
        return [
            'success' => true,
            'platform' => self::detectPlatform($url),
            'title' => $info['title'] ?? 'Unknown',
            'duration' => $info['duration'] ?? null,
            'uploader' => $info['uploader'] ?? 'Unknown',
            'view_count' => $info['view_count'] ?? null,
            'upload_date' => $info['upload_date'] ?? null,
            'thumbnail' => $info['thumbnail'] ?? null,
            'description' => isset($info['description']) ? substr($info['description'], 0, 200) . '...' : '',
            'formats_available' => count($info['formats'] ?? []),
            'url' => $url
        ];
    }
    
    public static function downloadMedia($url, $quality = 'best', $format = 'mp4') {
        // Build yt-dlp command
        $format_selector = 'best[ext=mp4]/best';
        
        switch ($quality) {
            case 'high':
                $format_selector = 'best[height<=1080]/best';
                break;
            case 'medium':
                $format_selector = 'best[height<=720]/best';
                break;
            case 'low':
                $format_selector = 'worst[height>=360]/worst';
                break;
        }
        
        if ($format === 'mp3') {
            $format_selector = 'bestaudio/best';
        }
        
        $command = "yt-dlp --get-url --format " . escapeshellarg($format_selector) . " " . escapeshellarg($url) . " 2>/dev/null";
        $download_url = trim(shell_exec($command));
        
        if (!$download_url) {
            throw new Exception("Failed to get download URL");
        }
        
        // Get additional info
        $info_command = "yt-dlp --dump-json --no-warnings " . escapeshellarg($url) . " 2>/dev/null";
        $info_output = shell_exec($info_command);
        $info = json_decode($info_output, true);
        
        return [
            'success' => true,
            'platform' => self::detectPlatform($url),
            'title' => $info['title'] ?? 'Unknown',
            'download_url' => $download_url,
            'duration' => $info['duration'] ?? null,
            'uploader' => $info['uploader'] ?? 'Unknown',
            'thumbnail' => $info['thumbnail'] ?? null,
            'format' => $format,
            'quality' => $quality,
            'file_size' => $info['filesize'] ?? $info['filesize_approx'] ?? null,
            'view_count' => $info['view_count'] ?? null,
            'upload_date' => $info['upload_date'] ?? null
        ];
    }
    
    public static function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    public static function errorResponse($message, $status_code = 400) {
        self::jsonResponse([
            'success' => false,
            'error' => $message
        ], $status_code);
    }
}
?>
