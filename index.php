<?php
// SaveMedia - Main Application Entry Point
require_once 'config/config.php';
require_once 'includes/functions.php';

// Safe FFmpeg & yt-dlp checks (without shell_exec)
$hasYtDlp = file_exists('/usr/bin/yt-dlp') || file_exists('/usr/local/bin/yt-dlp');
$hasFFmpeg = file_exists('/usr/bin/ffmpeg') || file_exists('/usr/local/bin/ffmpeg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Multi-Platform Media Downloader</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-download me-2"></i><?php echo APP_NAME; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-server me-1"></i>
                    Status: <span class="badge bg-success">Online</span>
                </span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-download me-3"></i>
                        SaveMedia Downloader
                    </h1>
                    <p class="lead mb-5">
                        Fast, free, and secure media downloader supporting YouTube, Instagram, Facebook, Twitter, TikTok, and more!
                    </p>
                    
                    <!-- System Status -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="status-card">
                                <i class="fas fa-server text-success"></i>
                                <h6>Server</h6>
                                <span class="badge bg-success">Online</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-card">
                                <i class="fas fa-download text-info"></i>
                                <h6>yt-dlp</h6>
                                <span class="badge bg-<?php echo ($hasYtDlp ? 'success' : 'warning'); ?>">
                                    <?php echo ($hasYtDlp ? 'Ready' : 'Installing...'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-card">
                                <i class="fas fa-video text-warning"></i>
                                <h6>FFmpeg</h6>
                                <span class="badge bg-<?php echo ($hasFFmpeg ? 'success' : 'warning'); ?>">
                                    <?php echo ($hasFFmpeg ? 'Ready' : 'Installing...'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Download Form -->
                    <div class="download-form">
                        <div class="input-group input-group-lg mb-3">
                            <input type="url" class="form-control" id="mediaUrl" 
                                   placeholder="Paste your media URL here..." required>
                            <button class="btn btn-success" type="button" id="downloadBtn">
                                <i class="fas fa-download me-2"></i>Download
                            </button>
                        </div>
                        
                        <!-- Options -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <select class="form-select" id="qualitySelect">
                                    <option value="best">Best Quality</option>
                                    <option value="high">High (1080p)</option>
                                    <option value="medium">Medium (720p)</option>
                                    <option value="low">Low (360p)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="formatSelect">
                                    <option value="mp4">Video (MP4)</option>
                                    <option value="mp3">Audio (MP3)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading -->
                    <div id="loadingDiv" class="d-none">
                        <div class="spinner-border text-light me-2" role="status"></div>
                        <span>Processing your request...</span>
                    </div>
                    
                    <!-- Results -->
                    <div id="resultsDiv" class="d-none">
                        <div class="card">
                            <div class="card-body">
                                <div id="mediaInfo"></div>
                                <div id="downloadLinks"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error -->
                    <div id="errorDiv" class="alert alert-danger d-none" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="errorMessage"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Platforms -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Supported Platforms</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-youtube text-danger"></i>
                        <h5>YouTube</h5>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-instagram text-primary"></i>
                        <h5>Instagram</h5>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-facebook text-primary"></i>
                        <h5>Facebook</h5>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-twitter text-info"></i>
                        <h5>Twitter/X</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Powered by AWS Elastic Beanstalk</p>
            <p>
                <a href="/api/health" class="text-light me-3">
                    <i class="fas fa-heartbeat me-1"></i>Health Check
                </a>
                <a href="/api/platforms" class="text-light">
                    <i class="fas fa-list me-1"></i>API Info
                </a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
