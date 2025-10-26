# SaveMedia - Multi-Platform Media Downloader

A powerful PHP-based media downloader supporting YouTube, Instagram, Facebook, Twitter, TikTok, and more platforms.

## Features
- 🚀 Multi-platform support (YouTube, Instagram, Facebook, etc.)
- 🔒 Secure and privacy-focused
- 📱 Mobile-responsive design
- 🎯 RESTful API
- ⚡ Fast downloads with multiple quality options
- 🛡️ Rate limiting and security headers

## Supported Platforms
- YouTube (videos, playlists, audio)
- Instagram (posts, stories, reels)
- Facebook (videos, live streams)
- Twitter/X (videos, GIFs)
- TikTok (videos without watermark)
- Vimeo (high-quality videos)
- Dailymotion (videos and channels)

## Deployment

### AWS Elastic Beanstalk
```bash
# Initialize EB
eb init -p "PHP 8.1" savemedia

# Create environment
eb create savemedia-env --single-instance

# Deploy
eb deploy

Local Development 

# Install dependencies
composer install

# Start local server
php -S localhost:8000 -t public/
