<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#supported">Platforms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#api">API</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        Download Media from Any Platform
                    </h1>
                    <p class="lead mb-5">
                        Fast, free, and secure media downloader supporting YouTube, Instagram, Facebook, Twitter, TikTok, and more!
                    </p>
                    
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
                        <div class="spinner-border text-primary me-2" role="status"></div>
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

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Why Choose <?php echo APP_NAME; ?>?</h2>
                    <p class="lead">Fast, reliable, and feature-rich media downloading</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Lightning Fast</h4>
                        <p>Download media in seconds with our optimized servers and CDN network.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>100% Secure</h4>
                        <p>Your privacy is protected. We don't store your data or downloaded files.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Friendly</h4>
                        <p>Works perfectly on all devices - desktop, tablet, and mobile.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-hd-video"></i>
                        </div>
                        <h4>HD Quality</h4>
                        <p>Download in multiple formats and qualities up to 4K resolution.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h4>Multi-Platform</h4>
                        <p>Support for 10+ popular social media and video platforms.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h4>Developer API</h4>
                        <p>RESTful API for developers to integrate into their applications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Platforms -->
    <section id="supported" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Supported Platforms</h2>
                    <p class="lead">Download from your favorite social media and video platforms</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-youtube"></i>
                        <h5>YouTube</h5>
                        <p>Videos, playlists, and audio</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-instagram"></i>
                        <h5>Instagram</h5>
                        <p>Posts, stories, and reels</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-facebook"></i>
                        <h5>Facebook</h5>
                        <p>Videos and live streams</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-twitter"></i>
                        <h5>Twitter/X</h5>
                        <p>Videos and GIFs</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-tiktok"></i>
                        <h5>TikTok</h5>
                        <p>Short videos without watermark</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fab fa-vimeo"></i>
                        <h5>Vimeo</h5>
                        <p>High-quality videos</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fas fa-video"></i>
                        <h5>Dailymotion</h5>
                        <p>Videos and channels</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="platform-card">
                        <i class="fas fa-plus"></i>
                        <h5>More Coming</h5>
                        <p>Regular updates with new platforms</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- API Documentation -->
    <section id="api" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Developer API</h2>
                    <p class="lead">Integrate <?php echo APP_NAME; ?> into your applications</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="api-docs">
                        <h4>API Endpoints</h4>
                        <div class="endpoint">
                            <h5><span class="badge bg-success">POST</span> /api/info</h5>
                            <p>Get media information without downloading</p>
                            <pre><code>{
  "url": "https://youtube.com/watch?v=..."
}</code></pre>
                        </div>
                        <div class="endpoint">
                            <h5><span class="badge bg-primary">POST</span> /api/download</h5>
                            <p>Get download links for media</p>
                            <pre><code>{
  "url": "https://youtube.com/watch?v=...",
  "quality": "high",
  "format": "mp4"
}</code></pre>
                        </div>
                        <div class="endpoint">
                            <h5><span class="badge bg-info">GET</span> /api/platforms</h5>
                            <p>Get list of supported platforms</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p>Free multi-platform media downloader</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
                    <p>Powered by AWS & PHP</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
