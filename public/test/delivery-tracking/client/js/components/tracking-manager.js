// Gestionnaire de suivi en temps réel - RÉCEPTION WEBSOCKET UNIQUEMENT
class TrackingManager {
    constructor(trackingController, uiManager, mapService = null) {
        this.trackingController = trackingController;
        this.uiManager = uiManager;
        this.mapService = mapService; // Service Google Maps
        this.websocketManager = null; // Manager WebSocket
        this.currentOrderId = null;
        this.lastTrackingData = null; // Dernières données reçues du WebSocket
        this.pollingInterval = null; // Pour le polling API
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
        
        // Utiliser WebSocket OU API polling (pas les deux)
        if (this.websocketManager && this.websocketManager.isConnected()) {
            // Mode WebSocket: souscrire aux mises à jour en temps réel
            this.websocketManager.subscribeToDeliveryTracking(orderId);
            console.log(`✅ Suivi activé pour la commande ${orderId} via WebSocket`);
        } else {
            // Mode API: récupérer les données de tracking via polling
            console.warn("⚠️ WebSocket non connecté - utilisation de l'API pour le tracking");
            this.uiManager.showInfo("Mode tracking via API (WebSocket indisponible)");
            this.startApiPolling(orderId);
            console.log(`✅ Suivi activé pour la commande ${orderId} via API polling`);
        }
        
        // Initialiser l'affichage
        this.showTracking();
        this.setupConnectionStatus();
        
        return true;
    }

    // Polling API pour le tracking quand WebSocket n'est pas disponible
    startApiPolling(orderId) {
        // Arrêter tout polling existant
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        // Récupérer les données initiales
        this.fetchTrackingData(orderId);
        
        // Polling toutes les 10 secondes
        this.pollingInterval = setInterval(() => {
            this.fetchTrackingData(orderId);
        }, 10000);
    }
    
    async fetchTrackingData(orderId) {
        try {
            console.log(`📡 Récupération des vraies données de tracking pour commande ${orderId}`);
            
            // Appel API réel pour récupérer les données de tracking
            const response = await fetch(`${SHARED_CONFIG.API.BASE_URL}/tracking/delivery/${orderId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data && data.data) {
                console.log("📊 Données reçues de l'API:", data.data);
                console.log("📈 Progression:", data.data.progress_percentage + "%");
                console.log("📍 Distance restante:", data.data.distance_remaining);
                console.log("⏱️ ETA:", data.data.estimated_arrival);
                this.handleRealTimeUpdate(data.data);
                console.log("✅ Vraies données de tracking récupérées via API:", data.data);
            } else {
                console.warn("⚠️ Aucune donnée de tracking disponible");
            }
            
        } catch (error) {
            console.error("❌ Erreur lors de la récupération des données de tracking:", error);
            // En cas d'erreur, afficher un message informatif
            this.uiManager.showError("Impossible de récupérer les données de tracking");
        }
    }

    stopTracking() {
        console.log("🛑 Arrêt du suivi");
        
        // Arrêter le polling API
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
        
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
        
        // Si on reçoit des données WebSocket, arrêter le polling
        if (this.pollingInterval) {
            console.log("🛑 Arrêt du polling - WebSocket fonctionnel");
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
        
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