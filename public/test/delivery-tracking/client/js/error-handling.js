// Service de gestion des erreurs pour l'interface client
class CustomerErrorHandlingService {
    constructor() {
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.retryDelay = 1000; // 1 seconde
        this.isOnline = navigator.onLine;

        this.initializeConnectionMonitoring();
    }

    /**
     * Surveiller la connexion réseau
     */
    initializeConnectionMonitoring() {
        window.addEventListener("online", () => {
            this.isOnline = true;
            console.log("🟢 Connexion rétablie");
            this.showNotification("Connexion rétablie", "success");
        });

        window.addEventListener("offline", () => {
            this.isOnline = false;
            console.log("🔴 Connexion perdue");
            this.showNotification("Connexion internet perdue", "warning");
        });
    }

    /**
     * Gérer les erreurs API avec retry automatique
     */
    async handleApiError(error, operation, context = {}) {
        const operationKey = `${operation}_${JSON.stringify(context)}`;
        const attempts = this.retryAttempts.get(operationKey) || 0;

        console.error(`❌ Erreur ${operation}:`, error);

        // Si hors ligne, ne pas retry
        if (!this.isOnline) {
            this.showNotification(
                "Opération échouée - pas de connexion internet",
                "error",
            );
            return { success: false, error: "offline" };
        }

        // Erreurs non-récupérables
        if (this.isNonRetryableError(error)) {
            this.showNotification(
                `Erreur: ${this.getErrorMessage(error)}`,
                "error",
            );
            return { success: false, error: error.message };
        }

        // Retry automatique
        if (attempts < this.maxRetries) {
            this.retryAttempts.set(operationKey, attempts + 1);
            const delay = this.calculateRetryDelay(attempts);

            console.log(
                `🔄 Tentative ${attempts + 1}/${this.maxRetries} dans ${delay}ms`,
            );
            this.showNotification(
                `Nouvelle tentative... (${attempts + 1}/${this.maxRetries})`,
                "info",
            );

            await this.sleep(delay);
            return { success: false, shouldRetry: true };
        }

        // Échec final après tous les retries
        this.retryAttempts.delete(operationKey);
        this.showNotification(
            `Échec après ${this.maxRetries} tentatives`,
            "error",
        );
        return { success: false, error: "max_retries_exceeded" };
    }

    /**
     * Déterminer si l'erreur est récupérable
     */
    isNonRetryableError(error) {
        const nonRetryableCodes = [400, 401, 403, 404, 422];
        return error.status && nonRetryableCodes.includes(error.status);
    }

    /**
     * Calculer le délai de retry avec backoff exponentiel
     */
    calculateRetryDelay(attempts) {
        return this.retryDelay * Math.pow(2, attempts);
    }

    /**
     * Extraire un message d'erreur lisible
     */
    getErrorMessage(error) {
        if (error.response?.data?.message) {
            return error.response.data.message;
        }
        if (error.message) {
            return error.message;
        }
        return "Une erreur inconnue s'est produite";
    }

    /**
     * Afficher une notification à l'utilisateur
     */
    showNotification(message, type = "info") {
        // Créer ou réutiliser le conteneur de notifications
        let container = document.getElementById("error-notifications");
        if (!container) {
            container = document.createElement("div");
            container.id = "error-notifications";
            container.className = "position-fixed top-0 end-0 p-3";
            container.style.zIndex = "9999";
            document.body.appendChild(container);
        }

        // Créer la notification
        const notification = document.createElement("div");
        notification.className = `alert alert-${this.getBootstrapClass(type)} alert-dismissible fade show`;
        notification.innerHTML = `
            ${this.getIcon(type)} ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        container.appendChild(notification);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Convertir le type en classe Bootstrap
     */
    getBootstrapClass(type) {
        const mapping = {
            success: "success",
            error: "danger",
            warning: "warning",
            info: "info",
        };
        return mapping[type] || "secondary";
    }

    /**
     * Obtenir l'icône pour le type de notification
     */
    getIcon(type) {
        const icons = {
            success: "✅",
            error: "❌",
            warning: "⚠️",
            info: "ℹ️",
        };
        return icons[type] || "";
    }

    /**
     * Marquer une opération comme réussie
     */
    markOperationSuccess(operation, context = {}) {
        const operationKey = `${operation}_${JSON.stringify(context)}`;
        this.retryAttempts.delete(operationKey);
    }

    /**
     * Utilitaire pour attendre
     */
    sleep(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }
}