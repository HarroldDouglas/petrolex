// Application principale pour le suivi client
class ClientTrackingApp {
    constructor() {
        this.services = {
            api: new ApiService(),
            map: new MapService(),
            websocket: new WebSocketService()
        };
        
        this.ui = new UIComponents();
        this.formHandler = null;
        this.eventHandler = null;
        this.currentOrder = null;
        
        this.init();
    }

    init() {
        // Initialiser les services
        this.initializeServices();
        
        // Configurer les gestionnaires d'événements
        this.setupEventHandlers();
        
        // Charger les commandes disponibles
        this.loadAvailableOrders();
        
        console.log('Client Tracking App initialized');
    }

    initializeServices() {
        // Initialiser la carte
        this.services.map.initialize('map');
        
        // Initialiser WebSocket (correction du nom de méthode)
        this.services.websocket.connect();
        
        // Configurer les callbacks WebSocket
        this.services.websocket.on('connected', () => {
            this.ui.updateConnectionStatus(true);
            this.ui.addToHistory('Connexion WebSocket établie');
        });
        
        this.services.websocket.on('disconnected', () => {
            this.ui.updateConnectionStatus(false);
            this.ui.addToHistory('Connexion WebSocket perdue');
        });
        
        this.services.websocket.on('locationUpdated', (data) => {
            this.handleLocationUpdate(data);
        });
        
        this.services.websocket.on('statusUpdated', (data) => {
            this.handleStatusUpdate(data);
        });
    }

    setupEventHandlers() {
        // Gestionnaire de formulaires
        this.formHandler = new FormHandler(this.ui, {
            onTrackingStart: (orderNumber) => this.startTracking(orderNumber)
        });
        
        // Gestionnaire d'événements
        this.eventHandler = new EventHandler({
            onCallDriver: () => this.callDriver(),
            onResetTracking: () => this.resetTracking(),
            onSelectOrder: (orderNumber) => this.selectOrder(orderNumber),
            onRefreshOrders: () => this.loadAvailableOrders()
        });
        
        // Exposer les méthodes globalement pour les événements onclick
        window.clientApp = this;
    }

    async loadAvailableOrders() {
        try {
            const response = await this.services.api.getActiveDeliveries();
            
            if (response.success) {
                this.ui.renderAvailableOrders(response.deliveries);
            } else {
                this.ui.renderAvailableOrders([]);
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            this.ui.showError('Erreur de connexion au serveur');
            this.ui.renderAvailableOrders([]);
        }
    }

    async startTracking(orderNumber) {
        try {
            const response = await this.services.api.getDelivery(orderNumber);
            
            if (response.success) {
                this.currentOrder = response.delivery;
                this.setupTracking();
                this.services.websocket.subscribeToDelivery(orderNumber);
                this.ui.addToHistory(`Suivi démarré pour la commande ${orderNumber}`);
            } else {
                this.ui.showError('Commande non trouvée');
            }
        } catch (error) {
            console.error('Error loading order:', error);
            this.ui.showError('Erreur lors du chargement de la commande');
        }
    }

    setupTracking() {
        // Basculer vers le panneau de suivi
        this.ui.showTrackingPanel();
        this.ui.updateOrderNumber(this.currentOrder.order_number);

        // Mettre à jour les informations
        this.updateDeliveryInfo();
        this.updateMapView();
    }

    updateDeliveryInfo() {
        const delivery = this.currentOrder;

        // Mettre à jour le statut
        this.ui.updateDeliveryStatus(delivery.status);

        // Mettre à jour les informations du livreur
        this.ui.updateDriverInfo(delivery);
    }

    async updateMapView() {
        const delivery = this.currentOrder;

        // Définir la destination
        if (delivery.destination_lat && delivery.destination_lng) {
            this.services.map.setDestination(
                delivery.destination_lat, 
                delivery.destination_lng,
                {
                    name: delivery.customer_name,
                    address: delivery.destination_address
                }
            );
        }

        // Position du livreur
        if (delivery.driver_lat && delivery.driver_lng) {
            await this.updateDriverPosition(delivery.driver_lat, delivery.driver_lng);
        }

        // Centrer la carte
        if (delivery.destination_lat && delivery.destination_lng) {
            this.services.map.centerOnLocation(delivery.destination_lat, delivery.destination_lng);
        }
    }

    async updateDriverPosition(lat, lng) {
        // Attendre que la carte soit initialisée
        if (!this.services.map.initialized) {
            console.warn('Map not initialized yet, waiting...');
            setTimeout(() => this.updateDriverPosition(lat, lng), 500);
            return;
        }

        this.services.map.updateDriverPosition(lat, lng, {
            name: this.currentOrder.driver_name || 'Livreur'
        });

        // Calculer et afficher la route
        if (this.currentOrder.destination_lat && this.currentOrder.destination_lng) {
            const routeInfo = await this.services.map.drawRoute(
                [lng, lat], 
                [this.currentOrder.destination_lng, this.currentOrder.destination_lat]
            );

            if (routeInfo) {
                this.ui.updateETA(routeInfo.duration, routeInfo.distance);
            }
        }
    }

    handleLocationUpdate(data) {
        if (this.currentOrder && data.delivery_id === this.currentOrder.id) {
            this.updateDriverPosition(data.latitude, data.longitude);
            this.ui.addToHistory(`Position livreur mise à jour - ${new Date().toLocaleTimeString()}`);
        }
    }

    handleStatusUpdate(data) {
        if (this.currentOrder && data.delivery_id === this.currentOrder.id) {
            this.currentOrder.status = data.status;
            this.ui.updateDeliveryStatus(data.status);
            
            const statusText = CONFIG.STATUS.TRANSLATIONS[data.status] || data.status;
            this.ui.addToHistory(`Statut mis à jour: ${statusText} - ${new Date().toLocaleTimeString()}`);
        }
    }

    selectOrder(orderNumber) {
        this.ui.elements.orderNumber.value = orderNumber;
        this.startTracking(orderNumber);
    }

    callDriver() {
        if (this.currentOrder && this.currentOrder.driver_phone) {
            this.ui.showSuccess(`Appel simulé vers: ${this.currentOrder.driver_phone}`);
        } else {
            this.ui.showError('Numéro de téléphone non disponible');
        }
    }

    resetTracking() {
        // Nettoyer la carte
        if (this.services.map.driverMarker) {
            this.services.map.driverMarker.remove();
            this.services.map.driverMarker = null;
        }
        if (this.services.map.destinationMarker) {
            this.services.map.destinationMarker.remove();
            this.services.map.destinationMarker = null;
        }

        // Nettoyer la route
        if (this.services.map.map && this.services.map.map.getSource('route')) {
            this.services.map.map.getSource('route').setData({
                'type': 'Feature',
                'properties': {},
                'geometry': {
                    'type': 'LineString',
                    'coordinates': []
                }
            });
        }

        // Déconnecter WebSocket
        this.services.websocket.disconnect();
        
        // Réinitialiser l'interface
        this.ui.showSetupPanel();
        this.ui.clearHistory();
        this.ui.updateETA(null, null);
        this.formHandler.resetForm();
        this.currentOrder = null;
        
        // Recharger les commandes
        this.loadAvailableOrders();
        
        // Réinitialiser WebSocket
        this.services.websocket.connect();
    }

    refreshOrders() {
        this.loadAvailableOrders();
    }
}

// Initialiser l'application quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    window.clientApp = new ClientTrackingApp();
});