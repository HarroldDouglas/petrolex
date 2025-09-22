// Gestionnaire de suivi en temps réel - RÉCEPTION WEBSOCKET UNIQUEMENT
class TrackingManager {
    constructor(trackingController, uiManager, mapService = null) {
        this.trackingController = trackingController;
        this.uiManager = uiManager;
        this.mapService = mapService; // Service Google Maps
        this.websocketManager = null; // Manager WebSocket
        this.currentOrderId = null;
        this.lastTrackingData = null; // Dernières données reçues du WebSocket
        this.initTrackingElements();
    }
    
    setWebSocketManager(websocketManager) {
        this.websocketManager = websocketManager;
        console.log("🔌 WebSocketManager connecté au TrackingManager");
    }

    initTrackingElements() {
        this.elements = {
            trackingOrderNumber: document.getElementById("trackingOrderNumber"),
            trackingOrderStatus: document.getElementById("trackingOrderStatus"),
            trackingDriverName: document.getElementById("trackingDriverName"),
            trackingDeliveryAddress: document.getElementById("trackingDeliveryAddress"),
            trackingETA: document.getElementById("trackingETA"),
            trackingDistance: document.getElementById("trackingDistance"),
            trackingProgress: document.getElementById("trackingProgress"),
            trackingProgressBar: document.getElementById("trackingProgressBar"),
            trackingHistory: document.getElementById("trackingHistory"),
            connectionStatus: document.getElementById("connectionStatus"),
        };
        
        console.log("📱 Éléments UI de tracking initialisés");
    }

    showTracking() {
        const panel = document.getElementById("selectedOrderPanel");
        if (panel) {
            panel.style.display = "block";
            console.log("👁️ Panel de tracking affiché");
        }
    }

    hideTracking() {
        const panel = document.getElementById("selectedOrderPanel");
        if (panel) {
            panel.style.display = "none";
            console.log("🙈 Panel de tracking masqué");
        }
        this.currentOrderId = null;
        this.lastTrackingData = null;
    }

    // ✅ SEULE méthode pour recevoir les données de tracking via WebSocket
    async startTracking(orderId) {
        console.log(`🎯 Démarrage du suivi pour la commande: ${orderId}`);
        
        this.currentOrderId = orderId;
        
        // Vérifier que WebSocket est connecté
        if (!this.websocketManager || !this.websocketManager.isConnected()) {
            console.error("❌ WebSocket non connecté - impossible de recevoir les données de tracking");
            this.uiManager.showError("WebSocket déconnecté. Reconnexion en cours...");
            return false;
        }

        // Souscrire aux mises à jour de tracking via WebSocket
        this.websocketManager.subscribeToDeliveryTracking(orderId);
        
        // Initialiser l'affichage
        this.showTracking();
        this.setupConnectionStatus();
        
        console.log(`✅ Suivi activé pour la commande ${orderId} via WebSocket`);
        return true;
    }

    stopTracking() {
        console.log("🛑 Arrêt du suivi");
        
        if (this.currentOrderId && this.websocketManager) {
            this.websocketManager.unsubscribeFromDeliveryTracking(this.currentOrderId);
        }
        
        this.hideTracking();
        this.clearTracking();
    }

    // ✅ MISE À JOUR via données WebSocket uniquement
    handleRealTimeUpdate(trackingData) {
        console.log("📡 Mise à jour reçue via WebSocket:", trackingData);
        
        this.lastTrackingData = trackingData;

        // Mettre à jour la carte avec la position du livreur
        if (this.mapService && trackingData.driver_position) {
            this.mapService.updateDriverPosition(
                trackingData.driver_position.lat,
                trackingData.driver_position.lng,
                trackingData.driver_info || { name: 'Livreur' }
            );

            // Afficher aussi la destination si disponible
            if (trackingData.customer_position) {
                this.mapService.setDestination(
                    trackingData.customer_position.lat,
                    trackingData.customer_position.lng,
                    trackingData.customer_info || { name: 'Destination' }
                );
            }

            // Tracer l'itinéraire si disponible
            if (trackingData.route_points && trackingData.route_points.length > 0) {
                this.mapService.drawRouteFromPoints(trackingData.route_points);
            }
        }

        // Mettre à jour l'interface utilisateur
        this.updateTrackingDisplay(trackingData);
        
        // Ajouter message d'historique si présent
        if (trackingData.message) {
            this.addToHistory(trackingData.message, trackingData.message_type || 'info');
        }
    }

    updateTrackingDisplay(trackingData) {
        // Nom du livreur
        if (this.elements.trackingDriverName && trackingData.driver_info) {
            this.elements.trackingDriverName.textContent = 
                trackingData.driver_info.name || `${trackingData.driver_info.first_name} ${trackingData.driver_info.last_name}`;
        }

        // ETA
        if (this.elements.trackingETA && trackingData.estimated_arrival) {
            this.elements.trackingETA.textContent = trackingData.estimated_arrival;
        }

        // Progression
        if (this.elements.trackingProgress && trackingData.progress_percentage !== undefined) {
            this.elements.trackingProgress.textContent = `${Math.round(trackingData.progress_percentage)}%`;
        }

        if (this.elements.trackingProgressBar && trackingData.progress_percentage !== undefined) {
            this.elements.trackingProgressBar.style.width = `${trackingData.progress_percentage}%`;
        }

        // Distance restante
        if (this.elements.trackingDistance && trackingData.distance_remaining) {
            this.elements.trackingDistance.textContent = trackingData.distance_remaining;
        }
    }

    setupConnectionStatus() {
        const statusElement = document.getElementById('connectionStatus');
        if (statusElement && this.websocketManager) {
            this.updateConnectionStatus(this.websocketManager.isConnected());
        }
    }

    updateConnectionStatus(isConnected) {
        const statusElement = document.getElementById('connectionStatus');
        if (statusElement) {
            if (isConnected) {
                statusElement.className = 'status-indicator status-online';
                statusElement.title = 'WebSocket connecté';
            } else {
                statusElement.className = 'status-indicator status-offline';
                statusElement.title = 'WebSocket déconnecté';
            }
        }
    }

    addToHistory(message, type = 'info') {
        if (!this.elements.trackingHistory) return;

        const timestamp = new Date().toLocaleTimeString();
        const historyItem = document.createElement('div');
        historyItem.className = `history-item history-${type}`;
        historyItem.innerHTML = `
            <span class="timestamp">${timestamp}</span>
            <span class="message">${message}</span>
        `;

        this.elements.trackingHistory.insertBefore(historyItem, this.elements.trackingHistory.firstChild);
        
        // Limiter à 10 entrées
        const items = this.elements.trackingHistory.children;
        if (items.length > 10) {
            this.elements.trackingHistory.removeChild(items[items.length - 1]);
        }
    }

    clearTracking() {
        // Vider tous les champs
        Object.keys(this.elements).forEach(key => {
            const element = this.elements[key];
            if (element && element.textContent !== undefined) {
                element.textContent = key === 'trackingProgress' ? '0%' : '-';
            }
        });

        if (this.elements.trackingProgressBar) {
            this.elements.trackingProgressBar.style.width = '0%';
        }

        if (this.elements.trackingHistory) {
            this.elements.trackingHistory.innerHTML = '';
        }
    }

    updateInfo(order, trackingData = {}) {
        if (this.elements.trackingOrderNumber) {
            this.elements.trackingOrderNumber.textContent = order.order_number || 'N/A';
        }
        
        this.updateOrderStatus(order);
        this.updateDeliveryInfo(order, trackingData);
    }

    updateOrderStatus(order) {
        if (!this.elements.trackingOrderStatus) return;
        
        const statusColor = CUSTOMER_CONFIG.STATUS.COLORS[order.status] || 'secondary';
        const statusLabel = CUSTOMER_CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
        
        this.elements.trackingOrderStatus.textContent = statusLabel;
        this.elements.trackingOrderStatus.className = `badge bg-${statusColor}`;
    }

    updateDeliveryInfo(order, trackingData) {
        if (this.elements.trackingDeliveryAddress) {
            const address = this.extractDeliveryAddress(order);
            this.elements.trackingDeliveryAddress.textContent = address;
        }
    }

    extractDeliveryAddress(order) {
        if (order.delivery_address) {
            return order.delivery_address.name || order.delivery_address.address || 'Adresse non disponible';
        }
        return 'Adresse non disponible';
    }
}