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
            trackingSpeed: document.getElementById("trackingSpeed"),
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
        
        // Use WebSocket only
        if (this.websocketManager && this.websocketManager.isConnected()) {
            this.websocketManager.subscribeToDeliveryTracking(orderId);
            console.log(`Tracking enabled for order ${orderId} via WebSocket`);
        } else {
            console.warn("WebSocket not connected - tracking unavailable");
            this.uiManager.showError("WebSocket connection required for tracking");
        }
        
        // Initialiser l'affichage
        this.showTracking();
        this.setupConnectionStatus();
        
        return true;
    }


    stopTracking() {
        console.log("🛑 Arrêt du suivi");
        
        
        // Arrêter WebSocket si connecté
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
        // 🔧 SUPPORT DES DEUX FORMATS : API Resource ET WebSocket Event
        const driverLat = trackingData.driver_position?.lat || trackingData.driver_lat;
        const driverLng = trackingData.driver_position?.lng || trackingData.driver_lng;
        const destLat = trackingData.destination?.lat || trackingData.destination_lat;
        const destLng = trackingData.destination?.lng || trackingData.destination_lng;
        
        if (this.mapService && driverLat && driverLng) {
            console.log(`🗺️ Mise à jour position livreur: ${driverLat}, ${driverLng}`);
            this.mapService.updateDriverPosition(
                parseFloat(driverLat),
                parseFloat(driverLng),
                { name: trackingData.driver_name || 'Livreur' }
            );

            // Afficher aussi la destination si disponible
            if (destLat && destLng) {
                console.log(`🎯 Mise à jour destination: ${destLat}, ${destLng}`);
                this.mapService.setDestination(
                    parseFloat(destLat),
                    parseFloat(destLng),
                    { name: trackingData.customer_name || 'Destination' }
                );
            }
        } else {
            console.warn("⚠️ Données de position manquantes pour la carte:", {
                driverLat, driverLng, destLat, destLng
            });
        }

        // Tracer l'itinéraire si disponible
        if (trackingData.route_points && trackingData.route_points.length > 0) {
            this.mapService.drawRouteFromPoints(trackingData.route_points);
        }

        // Mettre à jour l'interface utilisateur
        this.updateTrackingDisplay(trackingData);
        
        // Ajouter message d'historique si présent
        if (trackingData.message) {
            this.addToHistory(trackingData.message, trackingData.message_type || 'info');
        }
    }

    updateTrackingDisplay(trackingData) {
        console.log('🎨 [TrackingManager] updateTrackingDisplay avec:', trackingData);
        
        // Nom du livreur
        if (this.elements.trackingDriverName) {
            let driverName = '-';
            if (trackingData.driver_info) {
                driverName = trackingData.driver_info.name || `${trackingData.driver_info.first_name} ${trackingData.driver_info.last_name}`;
            } else if (trackingData.driver_name) {
                driverName = trackingData.driver_name;
            }
            this.elements.trackingDriverName.textContent = driverName;
        }

        // ETA - essayer plusieurs propriétés possibles
        if (this.elements.trackingETA) {
            let eta = trackingData.estimated_arrival || trackingData.estimated_duration || trackingData.eta;
            if (eta) {
                // Si c'est un nombre, ajouter "min"
                if (typeof eta === 'number') {
                    eta = `${eta} min`;
                }
                this.elements.trackingETA.textContent = eta;
            }
        }

        // Distance restante - essayer plusieurs propriétés
        if (this.elements.trackingDistance) {
            let distance = trackingData.distance_remaining || trackingData.distance;
            if (distance) {
                // Si c'est un nombre, ajouter "km"
                if (typeof distance === 'number') {
                    distance = `${distance} km`;
                } else if (typeof distance === 'string' && !distance.includes('km')) {
                    distance = `${distance} km`;
                }
                this.elements.trackingDistance.textContent = distance;
            }
        }

        // Vitesse actuelle
        if (this.elements.trackingSpeed) {
            let speed = trackingData.current_speed || trackingData.speed;
            if (speed) {
                // Si c'est un nombre, ajouter "km/h"
                if (typeof speed === 'number') {
                    speed = `${speed} km/h`;
                } else if (typeof speed === 'string' && !speed.includes('km/h')) {
                    speed = `${speed} km/h`;
                }
                this.elements.trackingSpeed.textContent = speed;
            }
        }

        // Progression
        if (trackingData.progress_percentage !== undefined) {
            const progress = parseFloat(trackingData.progress_percentage);
            
            if (this.elements.trackingProgress) {
                this.elements.trackingProgress.textContent = `${Math.round(progress)}%`;
            }
            
            if (this.elements.trackingProgressBar) {
                this.elements.trackingProgressBar.style.width = `${progress}%`;
            }
        }

        // Statut de la commande
        if (this.elements.trackingOrderStatus && trackingData.status) {
            let statusText = trackingData.status;
            let statusClass = 'badge bg-secondary';
            
            // Mapper les statuts
            if (statusText === 'in_progress') {
                statusText = 'En cours de livraison';
                statusClass = 'badge bg-warning';
            } else if (statusText === 'delivered') {
                statusText = 'Livrée';
                statusClass = 'badge bg-success';
            }
            
            this.elements.trackingOrderStatus.textContent = statusText;
            this.elements.trackingOrderStatus.className = statusClass;
        }

        // Adresse de livraison
        if (this.elements.trackingDeliveryAddress) {
            let address = 'Adresse non disponible';
            if (trackingData.delivery_address) {
                address = trackingData.delivery_address.name || trackingData.delivery_address.address || trackingData.delivery_address;
            } else if (trackingData.destination_address) {
                address = trackingData.destination_address;
            }
            this.elements.trackingDeliveryAddress.textContent = address;
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