// Gestionnaire principal de l'interface utilisateur
// Gère l'état des éléments DOM, notifications, connexion

class UIManager {
    constructor() {
        this.elements = {};
    }

    // === INITIALISATION DES ÉLÉMENTS DOM ===

    initElements() {
        // Panneaux principaux
        this.elements.loginPanel = document.getElementById("loginPanel");
        this.elements.clientPanel = document.getElementById("clientPanel");
        this.elements.selectedOrderPanel =
            document.getElementById("selectedOrderPanel");
        this.elements.trackingHistoryPanel = document.getElementById(
            "trackingHistoryPanel",
        );

        // Informations client connecté
        this.elements.currentClientName =
            document.getElementById("currentClientName");
        this.elements.currentClientEmail =
            document.getElementById("currentClientEmail");
        this.elements.logoutBtn = document.getElementById("logoutBtn");

        // Statut de connexion
        this.elements.connectionStatus =
            document.getElementById("connectionStatus");
        this.elements.websocketStatus =
            document.getElementById("websocketStatus");
        this.elements.apiStatus = document.getElementById("apiStatus");
        this.elements.mapStatus = document.getElementById("mapStatus");
        this.elements.lastUpdateTime =
            document.getElementById("lastUpdateTime");
    }

    // === GESTION DES PANNEAUX ===

    showLoginPanel() {
        this.elements.loginPanel.style.display = "block";
        this.elements.clientPanel.style.display = "none";
        this.updateConnectionStatus(false);
    }

    showClientPanel() {
        this.elements.loginPanel.style.display = "none";
        this.elements.clientPanel.style.display = "block";
        this.updateConnectionStatus(true);
    }

    // === GESTION DU STATUT DE CONNEXION ===

    updateConnectionStatus(connected) {
        if (this.elements.connectionStatus) {
            if (connected) {
                this.elements.connectionStatus.className =
                    "status-indicator status-online";
            } else {
                this.elements.connectionStatus.className =
                    "status-indicator status-offline";
            }
        }
    }

    // Méthode manquante pour le statut WebSocket
    updateWebSocketStatus(connected) {
        if (this.elements.websocketStatus) {
            if (connected) {
                this.elements.websocketStatus.textContent = "Connecté";
                this.elements.websocketStatus.className = "badge bg-success";
            } else {
                this.elements.websocketStatus.textContent = "Déconnecté";
                this.elements.websocketStatus.className = "badge bg-secondary";
            }
        }
    }

    updateLastUpdateTime() {
        if (this.elements.lastUpdateTime) {
            this.elements.lastUpdateTime.textContent =
                new Date().toLocaleTimeString();
        }
    }

    // === INFORMATIONS CLIENT ===

    updateClientInfo(user) {
        const fullName =
            user.full_name ||
            `${user.first_name || ""} ${user.last_name || ""}`.trim() ||
            "Client";
        this.elements.currentClientName.textContent = fullName;
        this.elements.currentClientEmail.textContent = user.email || "";
    }

    clearClientInfo() {
        if (this.elements.currentClientName) {
            this.elements.currentClientName.textContent = "";
        }
        if (this.elements.currentClientEmail) {
            this.elements.currentClientEmail.textContent = "";
        }
    }

    // === SYSTÈME DE NOTIFICATIONS ===

    showError(message, title = "Erreur") {
        console.error(title + ":", message);
        this.showNotification("error", title, message, 5000);
    }

    showSuccess(message, title = "Succès") {
        console.log(title + ":", message);
        this.showNotification("success", title, message, 4000);
    }

    showInfo(message, title = "Information") {
        console.info(title + ":", message);
        this.showNotification("info", title, message, 3000);
    }

    showNotification(type, title, message, duration = 4000) {
        const notification = document.createElement("div");
        notification.className = `notification ${type} fade-in`;
        notification.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <strong style="display: block; margin-bottom: 5px;">${title}</strong>
                    <div style="font-size: 0.9em;">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: inherit; font-size: 1.2em; cursor: pointer; margin-left: 10px;">&times;</button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification && notification.parentElement) {
                notification.style.opacity = "0";
                notification.style.transform = "translateX(100%)";
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    // === GESTION DES ÉTATS DE CHARGEMENT ===

    setLoadingState(element, isLoading) {
        if (typeof element === "string") {
            element = document.getElementById(element);
        }

        if (element) {
            element.disabled = isLoading;
            if (isLoading) {
                element.classList.add("loading");
                const originalText = element.innerHTML;
                element.dataset.originalText = originalText;
                element.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Chargement...';
            } else {
                element.classList.remove("loading");
                if (element.dataset.originalText) {
                    element.innerHTML = element.dataset.originalText;
                    delete element.dataset.originalText;
                }
            }
        }
    }

    showLoading(message = "Chargement...") {
        this.showNotification("info", "Chargement", message, 2000);
    }

    hideLoading() {
        const loadingElements = document.querySelectorAll(".loading");
        loadingElements.forEach((element) => {
            this.setLoadingState(element, false);
        });
    }

    // === UTILITAIRES ===

    formatDate(dateString) {
        if (!dateString) return "N/A";
        return new Date(dateString).toLocaleDateString("fr-FR", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }
}