// SaveMedia JavaScript Application
class SaveMediaApp {
    constructor() {
        this.apiBase = '/api';
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupFormValidation();
    }
    
    bindEvents() {
        const downloadBtn = document.getElementById('downloadBtn');
        const mediaUrl = document.getElementById('mediaUrl');
        
        downloadBtn.addEventListener('click', () => this.handleDownload());
        mediaUrl.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handleDownload();
            }
        });
        
        // Format change handler
        const formatSelect = document.getElementById('formatSelect');
        formatSelect.addEventListener('change', (e) => {
            const qualitySelect = document.getElementById('qualitySelect');
            if (e.target.value === 'mp3') {
                qualitySelect.innerHTML = `
                    <option value="best">Best Quality</option>
                    <option value="high">High (320kbps)</option>
                    <option value="medium">Medium (192kbps)</option>
                    <option value="low">Low (128kbps)</option>
                `;
            } else {
                qualitySelect.innerHTML = `
                    <option value="best">Best Quality</option>
                    <option value="high">High (1080p)</option>
                    <option value="medium">Medium (720p)</option>
                    <option value="low">Low (360p)</option>
                `;
            }
        });
    }
    
    setupFormValidation() {
        const mediaUrl = document.getElementById('mediaUrl');
        mediaUrl.addEventListener('input', (e) => {
            const url = e.target.value;
            const isValid = this.validateUrl(url);
            
            if (url && !isValid) {
                e.target.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-invalid');
            }
        });
    }
    
    validateUrl(url) {
        const supportedDomains = [
            'youtube.com', 'youtu.be', 'instagram.com', 'facebook.com',
            'twitter.com', 'x.com', 'tiktok.com', 'vimeo.com', 'dailymotion.com'
        ];
        
        try {
            const urlObj = new URL(url);
            return supportedDomains.some(domain => urlObj.hostname.includes(domain));
        } catch {
            return false;
        }
    }
    
    async handleDownload() {
        const url = document.getElementById('mediaUrl').value.trim();
        const quality = document.getElementById('qualitySelect').value;
        const format = document.getElementById('formatSelect').value;
        
        if (!url) {
            this.showError('Please enter a valid URL');
            return;
        }
        
        if (!this.validateUrl(url)) {
            this.showError('Please enter a supported platform URL');
            return;
        }
        
        this.showLoading(true);
        this.hideError();
        this.hideResults();
        
        try {
            // First get media info
            const info = await this.getMediaInfo(url);
            
            if (info.success) {
                // Then get download link
                const download = await this.downloadMedia(url, quality, format);
                
                if (download.success) {
                    this.showResults(info, download);
                } else {
                    this.showError(download.error || 'Failed to get download link');
                }
            } else {
                this.showError(info.error || 'Failed to get media information');
            }
        } catch (error) {
            this.showError('Network error. Please try again.');
            console.error('Download error:', error);
        } finally {
            this.showLoading(false);
        }
    }
    
    async getMediaInfo(url) {
        const response = await fetch(`${this.apiBase}/info`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ url })
        });
        
        return await response.json();
    }
    
    async downloadMedia(url, quality, format) {
        const response = await fetch(`${this.apiBase}/download`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ url, quality, format })
        });
        
        return await response.json();
    }
    
    showResults(info, download) {
        const resultsDiv = document.getElementById('resultsDiv');
        const mediaInfo = document.getElementById('mediaInfo');
        const downloadLinks = document.getElementById('downloadLinks');
        
        // Media info HTML
        mediaInfo.innerHTML = `
            <div class="media-info">
                ${download.thumbnail ? `<img src="${download.thumbnail}" alt="Thumbnail" class="media-thumbnail">` : ''}
                <div class="media-details">
                    <h5>${download.title}</h5>
                    <p><strong>Platform:</strong> ${download.platform.charAt(0).toUpperCase() + download.platform.slice(1)}</p>
                    <p><strong>Uploader:</strong> ${download.uploader}</p>
                    ${download.duration ? `<p><strong>Duration:</strong> ${this.formatDuration(download.duration)}</p>` : ''}
                    ${download.view_count ? `<p><strong>Views:</strong> ${this.formatNumber(download.view_count)}</p>` : ''}
                </div>
            </div>
        `;
        
        // Download links HTML
        downloadLinks.innerHTML = `
            <div class="text-center">
                <a href="${download.download_url}" class="download-btn" target="_blank" rel="noopener">
                    <i class="fas fa-download me-2"></i>Download ${download.format.toUpperCase()} (${download.quality})
                </a>
                <p class="mt-3 text-muted">
                    <small>Right-click and "Save as" to download the file</small>
                </p>
            </div>
        `;
        
        resultsDiv.classList.remove('d-none');
        resultsDiv.classList.add('fade-in-up');
    }
    
    showLoading(show) {
        const loadingDiv = document.getElementById('loadingDiv');
        if (show) {
            loadingDiv.classList.remove('d-none');
        } else {
            loadingDiv.classList.add('d-none');
        }
    }
    
    showError(message) {
        const errorDiv = document.getElementById('errorDiv');
        const errorMessage = document.getElementById('errorMessage');
        
        errorMessage.textContent = message;
        errorDiv.classList.remove('d-none');
        errorDiv.classList.add('fade-in-up');
    }
    
    hideError() {
        const errorDiv = document.getElementById('errorDiv');
        errorDiv.classList.add('d-none');
    }
    
    hideResults() {
        const resultsDiv = document.getElementById('resultsDiv');
        resultsDiv.classList.add('d-none');
    }
    
    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        } else {
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }
    }
    
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SaveMediaApp();
});

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
