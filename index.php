<?php
// PHP Configuration and Core Logic

// External API Configuration (API aur Ad URL ko constants bana diya gaya hai)
const API_BASE_URL = 'https://backend.savemedia.online';
const AD_REDIRECT_URL = 'https://otieu.com/4/10068616';

// Global variables to store state
$videoUrl = $_POST['videoUrl'] ?? $_GET['videoUrl'] ?? '';
$downloadFormatId = $_GET['format_id'] ?? '';
$videoInfo = null;
$error = null;
$success = null;

// CSS file ka naam jise hum link karenge
const CSS_FILE = 'style.css';

/**
 * Executes a cURL request to the external API.
 */
function makeApiRequest(string $endpoint, array $data = [], bool $isDownload = false)
{
    // API calling logic (cURL) goes here, same as in the previously provided PHP code.
    // ... (For brevity, the function body is omitted, but it contains the cURL logic) ...
    $ch = curl_init(API_BASE_URL . $endpoint);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if ($isDownload) {
        curl_setopt($ch, CURLOPT_HEADER, false);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return ['error' => 'API Error (CURL): ' . $curlError];
    }
    if ($httpCode !== 200) {
        $decoded = json_decode($response, true);
        $errorMessage = $decoded['error'] ?? 'API returned status code ' . $httpCode;
        return ['error' => $errorMessage];
    }
    
    return $response;
}

// 1. Handle Download Request (Jab user format select karke download button click karta hai)
if ($downloadFormatId && $videoUrl) {
    // API se file download karne ka request
    $downloadResponse = makeApiRequest('/download', [
        'url' => $videoUrl, 
        'format_id' => $downloadFormatId,
        'quality' => $_GET['quality'] ?? 'Unknown'
    ], true);

    if (is_array($downloadResponse) && isset($downloadResponse['error'])) {
        // Error hone par wapas redirect kar do
        header("Location: index.php?error=" . urlencode("Download failed: " . $downloadResponse['error']) . "&videoUrl=" . urlencode($videoUrl));
        exit;
    }

    // Download ko user ke browser par stream karna
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="video_download_' . time() . '.mp4"');
    header('Content-Length: ' . strlen($downloadResponse));
    echo $downloadResponse;
    exit;
}

// 2. Handle Info Request (Jab user URL daal kar submit karta hai)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $videoUrl) {
    // API se video info mangwana
    $apiResponse = makeApiRequest('/info', ['url' => $videoUrl]);
    
    if (is_array($apiResponse) && isset($apiResponse['error'])) {
        $error = $apiResponse['error'];
    } else {
        $data = json_decode($apiResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || isset($data['error'])) {
            $error = $data['error'] ?? 'Failed to decode API response.';
        } else {
            $videoInfo = $data;
            $success = 'Formats loaded successfully! Click on any format to download.';
        }
    }
}

// Handle error message passed via GET redirect
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// --- Helper Functions for HTML Rendering ---

/** Formats a number for display (e.g., 1234567 -> 1.2M) */
function formatNumber($num): string
{
    if (!is_numeric($num)) return 'Unknown';
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return (string)$num;
}

/** Sorts formats by quality in a predetermined order. */
function sortFormatsByQuality(array $formats): array
{
    $qualityOrder = [
        '1080p' => 0, '720p' => 1, '480p' => 2, '360p' => 3, '240p' => 4, 
        '144p' => 5, 'mp3' => 6
    ];

    usort($formats, function($a, $b) use ($qualityOrder) {
        $qualityA = strtolower($a['quality'] ?? '');
        $qualityB = strtolower($b['quality'] ?? '');

        $orderA = $qualityOrder[$qualityA] ?? 999;
        $orderB = $qualityOrder[$qualityB] ?? 999;

        return $orderA <=> $orderB;
    });

    return $formats;
}

/** Generates the HTML for the list of available formats. */
function generateFormatsList(?array $formats, string $videoUrl): string
{
    $output = '';
    
    if (empty($formats)) {
        // Fallback formats if no specific formats available
        $formats = [
            ['quality' => '720p', 'format_id' => 'best[height<=720]', 'filesize' => 'HD Quality', 'ext' => 'mp4', 'format_note' => 'Adaptive Stream'],
            ['quality' => '360p', 'format_id' => 'best[height<=360]', 'filesize' => 'Medium Quality', 'ext' => 'mp4', 'format_note' => 'Adaptive Stream'],
            ['quality' => 'MP3 Audio', 'format_id' => 'bestaudio', 'filesize' => 'Audio Only', 'ext' => 'mp3', 'format_note' => 'Audio Only']
        ];
    }
    
    $sortedFormats = sortFormatsByQuality($formats);
    
    foreach ($sortedFormats as $format) {
        $quality = htmlspecialchars($format['quality'] ?? 'Best Quality');
        $size = htmlspecialchars($format['filesize'] ?? 'Size unknown');
        $formatId = htmlspecialchars($format['format_id'] ?? 'best');
        $ext = strtoupper(htmlspecialchars($format['ext'] ?? 'MP4'));
        $formatNote = htmlspecialchars($format['format_note'] ?? '');
        $noteHtml = $formatNote ? '• ' . $formatNote : '';
        
        // Final download link jo PHP ke download section ko trigger karega
        $downloadLink = 'index.php?videoUrl=' . urlencode($videoUrl) . '&format_id=' . urlencode($formatId) . '&quality=' . urlencode($quality);

        $output .= '
            <div class="format-card">
                <div class="format-quality">' . $quality . '</div>
                <div class="format-size">' . $size . '</div>
                <div class="format-type" style="color: #888; font-size: 0.8rem; margin-bottom: 10px;">
                    ' . $ext . ' ' . $noteHtml . '
                </div>
                <button class="download-btn" onclick="startDownload(\'' . $downloadLink . '\', \'' . $quality . '\')">
                    Download ' . $quality . '
                </button>
            </div>
        ';
    }
    return $output;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Video Downloader - Download Videos from 1000+ Platforms</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&family=Josefin+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= CSS_FILE ?>"> 
</head>
<body>
    <section class="video-downloader-section">
        <div class="video-downloader-container">
            <header class="video-downloader-header" onclick="goToHomepage()">
                <h1>Free Video Downloader</h1>
                [cite_start]<p>Download videos from YouTube, Facebook, Instagram, TikTok & 1000+ more platforms! [cite: 76]</p>
            </header>

            <div class="ad-banner-top">
                Adsterra Banner 728x90
            </div>

            <div class="download-form-container">
                <h2 class="form-title">Free Online Video Downloader</h2>
                
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <input type="url" class="url-input" id="videoUrl" name="videoUrl" 
                               placeholder="Paste video URL here..." 
                               value="<?= htmlspecialchars($videoUrl) ?>" required>
                        <button type="submit" class="fetch-btn" id="fetchBtn">
                            Download
                        </button>
                    </div>
                </form>
                
                <p class="policy-text">By using our service you accept our Terms of Service and Privacy Policy.</p>
                
                <div id="errorContainer" class="error-container">
                    <?= htmlspecialchars($error) ?>
                </div>
                <div id="successContainer" class="success-container">
                    <?= htmlspecialchars($success) ?>
                </div>

                <div class="result-container" id="resultContainer" style="display: <?= $videoInfo ? 'block' : 'none'; ?>">
                    <div class="video-preview">
                        <div class="thumbnail-container">
                            <img id="videoThumbnail" 
                                 src="<?= htmlspecialchars($videoInfo['thumbnail'] ?? 'https://via.placeholder.com/200x120/667eea/white?text=No+Thumbnail') ?>" 
                                 alt="Video Thumbnail" class="thumbnail" 
                                 onerror="this.src='https://via.placeholder.com/200x120/667eea/white?text=No+Thumbnail'">
                            <div class="video-info">
                                [cite_start]<h3><?= htmlspecialchars($videoInfo['title'] ?? 'Video Title') ?></h3> [cite: 81, 82]
                                [cite_start]<p>Duration: <?= htmlspecialchars($videoInfo['duration'] ?? 'Loading...') ?></p> [cite: 82]
                                [cite_start]<p>Uploader: <?= htmlspecialchars($videoInfo['uploader'] ?? 'Loading...') ?></p> [cite: 82]
                                [cite_start]<p>Views: <?= formatNumber($videoInfo['view_count'] ?? 0) ?></p> [cite: 83]
                            </div>
                        </div>
                    </div>
                    
                    <div class="formats-section">
                        [cite_start]<h4>Available Formats</h4> [cite: 84]
                        <div id="formatsList" class="formats-grid">
                            <?= generateFormatsList($videoInfo['formats'] ?? [], $videoUrl) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ad-banner-middle">
                Adsterra Banner 728x90
            </div>

            [cite_start]<div class="faq-section"> [cite: 87]
                [cite_start]<h2>Frequently Asked Questions</h2> [cite: 87]
                
                <div class="faq-item">
                    [cite_start]<div class="faq-question">How to download videos using this tool?</div> [cite: 88]
                    <div class="faq-answer">
                        <ol>
                            [cite_start]<li>Copy the video URL from YouTube, Facebook, Instagram, etc. [cite: 88]</li>
                            [cite_start]<li>Paste the URL in the input box above [cite: 89]</li>
                            [cite_start]<li>Click "Download" button [cite: 89]</li>
                            [cite_start]<li>Select your preferred quality and format [cite: 89]</li>
                            [cite_start]<li>Click "Download" and wait for the download to start [cite: 90]</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    [cite_start]<div class="faq-question">Which platforms are supported?</div> [cite: 91]
                    <div class="faq-answer">
                        [cite_start]We support 1000+ platforms including: [cite: 91]
                        <ul>
                            [cite_start]<li>YouTube (Videos, Shorts, Live streams) [cite: 92]</li>
                            [cite_start]<li>Facebook (Videos, Reels) [cite: 92]</li>
                            [cite_start]<li>Instagram (Posts, Reels, Stories) [cite: 92]</li>
                            [cite_start]<li>TikTok (All videos) [cite: 92]</li>
                            [cite_start]<li>Twitter/X (Video posts) [cite: 93]</li>
                            [cite_start]<li>Dailymotion, Vimeo, Twitch [cite: 93]</li>
                            [cite_start]<li>And many more platforms... [cite: 94]</li>
                        </ul>
                    </div>
                </div>
            </div>

            [cite_start]<div class="ad-banner-bottom"> [cite: 95]
                Adsterra Banner 728x90
            </div>
        </div>
    </section>

    [cite_start]<div class="modal" id="popupModal"> [cite: 54]
        [cite_start]<div class="modal-content"> [cite: 56]
            [cite_start]<h3>Welcome Back!</h3> [cite: 58]
            <p>Thanks for using our downloader. [cite_start]Check out our special offers!</p> [cite: 96]
            [cite_start]<button class="skip-btn" onclick="skipAd()">Skip Ad</button> [cite: 59]
        </div>
    </div>

    <script>
        // Client-side JavaScript (ab sirf UI aur ad redirection flow sambhalega)
        
        // PHP se AD URL use karna
        [cite_start]const AD_REDIRECT_URL = '<?= AD_REDIRECT_URL ?>'; [cite: 98]
        
        document.addEventListener('DOMContentLoaded', function() {
            // FAQ Toggling
            const faqQuestions = document.querySelectorAll('.faq-question');
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    const isVisible = answer.style.display === 'block';
                    // Sabko band karke sirf current answer khole
                    document.querySelectorAll('.faq-answer').forEach(a => a.style.display = 'none');
                    if (!isVisible) {
                        answer.style.display = 'block';
                    }
                });
            });

            // Check if user returned from download
            checkReturnFromDownload();
        });

        // Header Click - Reload Page
        function goToHomepage() {
            window.location.href = window.location.pathname; 
        }

        // START DOWNLOAD PROCESS (Pehle Ad open karta hai, phir PHP download trigger karta hai)
        function startDownload(downloadLink, quality) {
            // Download shuru hone ka flag store karein
            sessionStorage.setItem('downloadStarted', 'true'); [cite: 136]
            
            // Ad page ko naye tab mein kholna
            window.open(AD_REDIRECT_URL, '_blank'); [cite: 137]
            
            // Thode der baad (Ad open hone ke liye) PHP download link par redirect karna
            setTimeout(() => {
                window.location.href = downloadLink;
            }, 500); 
        }

        // Check if user returned from download
        function checkReturnFromDownload() {
            const downloadStarted = sessionStorage.getItem('downloadStarted'); [cite: 147]
            
            if (downloadStarted === 'true') {
                setTimeout(() => {
                    document.getElementById('popupModal').style.display = 'flex'; [cite: 147]
                }, 1000);
                
                sessionStorage.removeItem('downloadStarted'); [cite: 148]
            }
        }

        // Skip Ad Popup
        function skipAd() {
            document.getElementById('popupModal').style.display = 'none'; [cite: 149]
        }
    </script>
</body>
</html>
