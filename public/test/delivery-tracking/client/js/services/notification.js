class CustomerNotificationService {
    constructor() {
        this.retryAttempts = new Map();
        this.networkErrorCount = 0;
    }

    showNotification(message, type = "info", duration = 5000) {
        const notification = document.createElement("div");
        notification.className = `alert alert-${this.getBootstrapClass(type)} alert-dismissible fade show position-fixed`;
        notification.style.cssText =
            "top: 20px; right: 20px; z-index: 9999; max-width: 350px;";

        notification.innerHTML = `
            <strong>${this.getIcon(type)}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    // Méthodes de raccourci
    success(message, duration = 5000) {
        this.showNotification(message, "success", duration);
    }

    error(message, duration = 5000) {
        this.showNotification(message, "error", duration);
    }

    warning(message, duration = 5000) {
        this.showNotification(message, "warning", duration);
    }

    info(message, duration = 5000) {
        this.showNotification(message, "info", duration);
    }

    getBootstrapClass(type) {
        const classes = {
            success: "success",
            error: "danger",
            warning: "warning",
            info: "info",
        };
        return classes[type] || "info";
    }

    getIcon(type) {
        const icons = {
            success: "✅",
            error: "❌",
            warning: "⚠️",
            info: "ℹ️",
        };
        return icons[type] || "ℹ️";
    }

    clearRetries(context) {
        for (const [key] of this.retryAttempts) {
            if (key.startsWith(context)) {
                this.retryAttempts.delete(key);
            }
        }
    }

    getNetworkStatus() {
        return {
            online: navigator.onLine,
            errorCount: this.networkErrorCount,
        };
    }
}
