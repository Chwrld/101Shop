//<script>
class SessionManager {
    constructor() {
        this.warningShown = false;
        this.expiredShown = false;
        this.checkInterval = null;
        this.init();
    }

    init() {
        // Start checking session status
        this.checkInterval = setInterval(() => this.checkSession(), 60000); // Check every minute
        this.checkSession(); // Initial check
        
        // Add click event to extend session buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('extend-session-btn')) {
                this.extendSession();
            }
        });
    }

    async checkSession() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/authentication/session_notif.php?action=timeleft');
            const data = await response.json();
            
            if (data.success) {
                this.handleTimeLeft(data.time_left);
            } else {
                this.handleSessionExpired();
            }
        } catch (error) {
            console.error('Session check failed:', error);
        }
    }

    handleTimeLeft(secondsLeft) {
        const minutesLeft = Math.floor(secondsLeft / 60);
        
        if (secondsLeft <= 0) {
            this.handleSessionExpired();
        } else if (minutesLeft <= 5 && !this.warningShown) {
            this.showWarning(minutesLeft);
        } else if (minutesLeft > 5 && this.warningShown) {
            this.hideWarning();
        }
    }

    showWarning(minutesLeft) {
        this.warningShown = true;
        
        // Remove existing warning
        this.hideWarning();
        
        // Create warning notification
        const warning = document.createElement('div');
        warning.className = 'session-warning alert alert-warning';
        warning.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>⚠️ Session Expiring!</strong> 
                    ${minutesLeft} minute${minutesLeft !== 1 ? 's' : ''} remaining.
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-warning extend-session-btn mr-2">
                        Extend Session
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="sessionManager.hideWarning()">
                        Dismiss
                    </button>
                </div>
            </div>
        `;
        
        document.body.prepend(warning);
        
        // Auto-hide after 10 seconds if not interacted with
        setTimeout(() => {
            if (this.warningShown) {
                this.hideWarning();
            }
        }, 10000);
    }

    hideWarning() {
        const warning = document.querySelector('.session-warning');
        if (warning) {
            warning.remove();
        }
        this.warningShown = false;
    }

    async extendSession() {
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/authentication/session_notif.php?action=extend');
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Session extended by 8 hours!');
                this.hideWarning();
                this.warningShown = false;
            } else {
                this.showError('Failed to extend session');
            }
        } catch (error) {
            console.error('Extend session failed:', error);
            this.showError('Network error - cannot extend session');
        }
    }

    handleSessionExpired() {
        if (this.expiredShown) return;
        
        this.expiredShown = true;
        clearInterval(this.checkInterval);
        
        // Show expired notification
        const expired = document.createElement('div');
        expired.className = 'session-expired alert alert-danger';
        expired.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>🔐 Session Expired!</strong> 
                    You have been logged out.
                </div>
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-sm btn-outline-danger">
                    Login Again
                </a>
            </div>
        `;
        
        document.body.prepend(expired);
        
        // Redirect to logout after 5 seconds
        setTimeout(() => {
            window.location.href = '<?php echo BASE_URL; ?>/authentication/logout.php';
        }, 5000);
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'danger');
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `session-notification alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.prepend(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
}

// Initialize session manager when page loads
let sessionManager;
document.addEventListener('DOMContentLoaded', () => {
    sessionManager = new SessionManager();
});
//</script>

/*<style>
.session-warning,
.session-expired,
.session-notification {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    min-width: 400px;
    max-width: 500px;
    animation: slideInRight 0.3s ease-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.session-expired {
    top: 20px;
    right: 20px;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Make sure alerts are visible above other content 
.alert {
    z-index: 9999;
}
</style>*/