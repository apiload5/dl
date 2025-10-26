<?php
class SaveMedia {
    
    private static function logError($message) {
        error_log(date('Y-m-d H:i:s') . ' - SaveMedia Error: ' . $message);
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
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }
        
        $domain = strtolower($parsed['host']);
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
    
    private static function findYtDlp() {
        // Possible yt-dlp locations
        $paths = [
            '/usr/bin/yt-dlp',
            '/usr/local/bin/yt-dlp',
            '/home/webapp/.local/bin/yt-dlp',
            '/opt/python/run/venv/bin/yt-dlp'
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        
        // Try which command
        $which = trim(shell_exec('which yt-dlp 2>/dev/null'));
        if ($which && file_exists($which)) {
            return $which;
        }
        
        // Try python module
        $python_check = shell_exec('python3 -m yt_dlp --version 2>/dev/null');
        if ($python_check) {
            return 'python3 -m yt_dlp';
        }
        
        return null;
    }
    
    public static function getMediaInfo($url) {
        try {
            $ytdlp = self::findYtDlp();
            if (!$ytdlp) {
                throw new Exception("yt-dlp not available. Please install it manually.");
            }
            
            $command = $ytdlp . ' --dump-json --no-warnings --no-playlist ' . escapeshellarg($url) . ' 2>/dev/null';
            $output = shell_exec($command);
            
            if (!$output) {
                throw new Exception("Failed to extract media information");
            }
            
            $info = json_decode($output, true);
            if (!$info) {
                throw new Exception("Invalid media data received");
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
            self::logError("Media info extraction failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function downloadMedia($url, $quality = 'best', $format = 'mp4') {
        try {
            $ytdlp = self::findYtDlp();
            if (!$ytdlp) {
                throw new Exception("yt-dlp not available. Please install it manually.");
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
            
            // Get download URL
            $command = $ytdlp . ' --get-url --format ' . escapeshellarg($format_selector) . ' --no-warnings --no-playlist ' . escapeshellarg($url) . ' 2>/dev/null';
            $download_url = trim(shell_exec($command));
            
            if (!$download_url || !filter_var($download_url, FILTER_VALIDATE_URL)) {
                throw new Exception("Failed to get valid download URL");
            }
            
            // Get additional info
            $info_command = $ytdlp . ' --dump-json --no-warnings --no-playlist ' . escapeshellarg($url) . ' 2>/dev/null';
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
            self::logError("Media download failed: " . $e->getMessage());
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
