<?php
class SaveMedia {
    
    private static function logError($message, $context = []) {
        $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $log_message .= ' - Context: ' . json_encode($context);
        }
        error_log($log_message);
    }
    
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
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if domain is allowed
        $parsed_url = parse_url($url);
        if (!$parsed_url || !isset($parsed_url['host'])) {
            return false;
        }
        
        $domain = strtolower($parsed_url['host']);
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
        try {
            // Check if yt-dlp is available
            $ytdlp_check = shell_exec('which yt-dlp 2>/dev/null');
            if (empty($ytdlp_check)) {
                throw new Exception("yt-dlp is not installed or not accessible");
            }
            
            // Build command with timeout
            $command = sprintf(
                'timeout 60 yt-dlp --dump-json --no-warnings --no-playlist %s 2>/dev/null',
                escapeshellarg($url)
            );
            
            $output = shell_exec($command);
            
            if (!$output) {
                throw new Exception("Failed to extract media information - no output from yt-dlp");
            }
            
            $info = json_decode($output, true);
            if (!$info) {
                throw new Exception("Invalid JSON response from yt-dlp");
            }
            
            return [
                'success' => true,
                'platform' => self::detectPlatform($url),
                'title' => $info['title'] ?? 'Unknown Title',
                'duration' => $info['duration'] ?? null,
                'uploader' => $info['uploader'] ?? 'Unknown',
                'view_count' => $info['view_count'] ?? null,
                'upload_date' => $info['upload_date'] ?? null,
                'thumbnail' => $info['thumbnail'] ?? null,
                'description' => isset($info['description']) ? substr($info['description'], 0, 200) . '...' : '',
                'formats_available' => count($info['formats'] ?? []),
                'url' => $url
            ];
            
        } catch (Exception $e) {
            self::logError("Media info extraction failed", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public static function downloadMedia($url, $quality = 'best', $format = 'mp4') {
        try {
            // Check if yt-dlp is available
            $ytdlp_check = shell_exec('which yt-dlp 2>/dev/null');
            if (empty($ytdlp_check)) {
                throw new Exception("yt-dlp is not installed or not accessible");
            }
            
            // Build format selector
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
            
            // Get download URL with timeout
            $command = sprintf(
                'timeout 60 yt-dlp --get-url --format %s --no-warnings --no-playlist %s 2>/dev/null',
                escapeshellarg($format_selector),
                escapeshellarg($url)
            );
            
            $download_url = trim(shell_exec($command));
            
            if (!$download_url || !filter_var($download_url, FILTER_VALIDATE_URL)) {
                throw new Exception("Failed to get valid download URL");
            }
            
            // Get additional info
            $info_command = sprintf(
                'timeout 60 yt-dlp --dump-json --no-warnings --no-playlist %s 2>/dev/null',
                escapeshellarg($url)
            );
            
            $info_output = shell_exec($info_command);
            $info = $info_output ? json_decode($info_output, true) : [];
            
            return [
                'success' => true,
                'platform' => self::detectPlatform($url),
                'title' => $info['title'] ?? 'Unknown Title',
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
            
        } catch (Exception $e) {
            self::logError("Media download failed", [
                'url' => $url,
                'quality' => $quality,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public static function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    public static function errorResponse($message, $status_code = 400) {
        self::jsonResponse([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ], $status_code);
    }
}
?>
