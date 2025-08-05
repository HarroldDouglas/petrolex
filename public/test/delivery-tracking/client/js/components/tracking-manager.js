// Gestionnaire spécialisé pour le suivi des livraisons
// Affichage en temps réel, historique, statistiques

class TrackingManager {
    constructor(uiManager) {
        this.uiManager = uiManager;
        this.initTrackingElements();
    }

    initTrackingElements() {
        this.elements = {
            trackingOrderNumber: document.getElementById("trackingOrderNumber"),
            trackingOrderStatus: document.getElementById("trackingOrderStatus"),
            trackingDriverName: document.getElementById("trackingDriverName"),
            trackingDeliveryAddress: document.getElementById(
                "trackingDeliveryAddress",
            ),
            trackingETA: document.getElementById("trackingETA"),
            trackingDistance: document.getElementById("trackingDistance"),
            trackingProgress: document.getElementById("trackingProgress"),
            trackingProgressBar: document.getElementById("trackingProgressBar"),
            stopTrackingBtn: document.getElementById("stopTrackingBtn"),
            trackingHistory: document.getElementById("trackingHistory"),
        };
    }

    // === AFFICHAGE DU TRACKING ===

    showTracking() {
        const selectedOrderPanel =
            document.getElementById("selectedOrderPanel");
        const trackingHistoryPanel = document.getElementById(
            "trackingHistoryPanel",
        );

        if (selectedOrderPanel) selectedOrderPanel.style.display = "block";
        if (trackingHistoryPanel) trackingHistoryPanel.style.display = "block";
    }

    hideTracking() {
        const selectedOrderPanel =
            document.getElementById("selectedOrderPanel");
        const trackingHistoryPanel = document.getElementById(
            "trackingHistoryPanel",
        );

        if (selectedOrderPanel) selectedOrderPanel.style.display = "none";
        if (trackingHistoryPanel) trackingHistoryPanel.style.display = "none";
    }

    // === MISE À JOUR DES INFORMATIONS ===

    updateInfo(order, trackingData = {}) {
        // Numéro de commande
        if (this.elements.trackingOrderNumber) {
            this.elements.trackingOrderNumber.textContent = order.order_number;
        }

        // Statut de la commande
        this.updateOrderStatus(order);

        // Informations de livraison
        this.updateDeliveryInfo(order, trackingData);

        // Statistiques de tracking
        this.updateStats(trackingData);
    }

    updateOrderStatus(order) {
        if (!this.elements.trackingOrderStatus) return;

        const statusColor = CUSTOMER_CONFIG.STATUS.COLORS[order.status] || "secondary";
        const statusLabel =
            CUSTOMER_CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;

        this.elements.trackingOrderStatus.textContent = statusLabel;
        this.elements.trackingOrderStatus.className = `badge bg-${statusColor}`;
    }

    updateDeliveryInfo(order, trackingData) {
        // Nom du livreur
        if (this.elements.trackingDriverName) {
            const driverName =
                trackingData.driver_name ||
                order.delivery_person?.name ||
                order.driver?.name ||
                "-";
            this.elements.trackingDriverName.textContent = driverName;
        }

        // Adresse de livraison
        if (this.elements.trackingDeliveryAddress) {
            const deliveryAddress = this.extractDeliveryAddress(order);
            this.elements.trackingDeliveryAddress.textContent = deliveryAddress;
        }
    }

    updateStats(data) {
        // Temps estimé d'arrivée
        if (this.elements.trackingETA) {
            if (data.eta || data.estimated_time_remaining) {
                const timeValue = data.eta || data.estimated_time_remaining;
                this.elements.trackingETA.textContent = `${timeValue} min`;
            } else {
                this.elements.trackingETA.textContent = "-";
            }
        }

        // Distance restante
        if (this.elements.trackingDistance) {
            if (data.distance || data.distance_remaining) {
                const distanceValue = data.distance || data.distance_remaining;
                this.elements.trackingDistance.textContent = `${distanceValue} km`;
            } else {
                this.elements.trackingDistance.textContent = "-";
            }
        }
    }

    // === GESTION DU PROGRÈS ===

    updateProgress(percent) {
        const roundedPercent = Math.round(percent);

        if (this.elements.trackingProgress) {
            this.elements.trackingProgress.textContent = `${roundedPercent}%`;
        }

        if (this.elements.trackingProgressBar) {
            this.elements.trackingProgressBar.style.width = `${percent}%`;
        }
    }

    // === HISTORIQUE ===

    addHistoryItem(message, type = "info") {
        if (!this.elements.trackingHistory) return;

        const history = this.elements.trackingHistory;

        const item = document.createElement("div");
        item.className = "history-item fade-in";
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <span>${message}</span>
                <span class="time">${new Date().toLocaleTimeString()}</span>
            </div>
        `;

        history.insertBefore(item, history.firstChild);

        // Limiter le nombre d'éléments dans l'historique
        this.limitHistoryItems(history, 20);

        // Mettre à jour le temps de dernière mise à jour
        this.uiManager.updateLastUpdateTime();
    }

    limitHistoryItems(history, maxItems) {
        const items = history.querySelectorAll(".history-item");
        if (items.length > maxItems) {
            items[items.length - 1].remove();
        }
    }

    clearHistory() {
        if (this.elements.trackingHistory) {
            this.elements.trackingHistory.innerHTML = "";
        }
    }

    // === NETTOYAGE ===

    clearTracking() {
        // Masquer les panneaux
        this.hideTracking();

        // Vider l'historique
        this.clearHistory();

        // Réinitialiser les valeurs
        Object.values(this.elements).forEach((element) => {
            if (element && element.textContent !== undefined) {
                element.textContent = "-";
            }
        });

        // Réinitialiser la barre de progrès
        if (this.elements.trackingProgressBar) {
            this.elements.trackingProgressBar.style.width = "0%";
        }
    }

    // === UTILITAIRES ===

    extractDeliveryAddress(order) {
        return (
            order.delivery_address?.name ||
            order.delivery_address?.address ||
            order.delivery_address ||
            "Adresse non définie"
        );
    }
}
