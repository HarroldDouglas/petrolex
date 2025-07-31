// Application principale pour la simulation livreur
class DeliveryDriverApp {
    constructor() {
        this.services = {
            api: new DeliveryApiService(),
            map: new DeliveryMapService(),
            simulation: null
        };
        
        this.ui = new DeliveryUIComponents();
        this.formHandler = null;
        this.eventHandler = null;
        this.currentPosition = null;
        this.selectedDelivery = null;
        this.driverInfo = null;
        
        this.init();
    }

    init() {
        // Initialiser les services
        this.initializeServices();
        
        // Configurer les gestionnaires d'événements
        this.setupEventHandlers();
        
        // Peupler les formulaires avec les valeurs par défaut
        this.ui.populateFormWithDefaults();
        
        // Initialiser le statut de connexion
        this.ui.updateConnectionStatus(false);
        
        console.log('Delivery Driver App initialized');
    }

    initializeServices() {
        // Initialiser la carte
        this.services.map.initialize('map');
        
        // Initialiser le service de simulation
        this.services.simulation = new SimulationService(this.services.api, this.services.map);
        
        // Configurer les callbacks de simulation
        this.services.simulation.on('simulationStarted', (data) => {
            this.handleSimulationStarted(data);
        });
        
        this.services.simulation.on('progressUpdate', (data) => {
            this.handleProgressUpdate(data);
        });
        
        this.services.simulation.on('simulationCompleted', (data) => {
            this.handleSimulationCompleted(data);
        });
        
        this.services.simulation.on('simulationStopped', () => {
            this.handleSimulationStopped();
        });
        
        this.services.simulation.on('simulationPaused', () => {
            this.handleSimulationPaused();
        });
        
        this.services.simulation.on('simulationResumed', () => {
            this.handleSimulationResumed();
        });
    }

    setupEventHandlers() {
        // Gestionnaire de formulaires
        this.formHandler = new DeliveryFormHandler(this.ui, {
            onDriverSetup: (driverData) => this.handleDriverSetup(driverData),
            onCreateDelivery: (deliveryData) => this.createDelivery(deliveryData),
            onStartSimulation: (speed) => this.startSimulation(speed),
            onPauseSimulation: () => this.pauseSimulation(),
            onStopSimulation: () => this.stopSimulation()
        });
        
        // Gestionnaire d'événements
        this.eventHandler = new DeliveryEventHandler({
            onSelectDelivery: (orderNumber) => this.selectDelivery(orderNumber),
            onRefreshDeliveries: () => this.loadDeliveries(),
            onMapClick: (lat, lng) => this.handleMapClick(lat, lng)
        });
        
        // Exposer les méthodes globalement
        window.deliveryApp = this;
    }

    async handleDriverSetup(driverData) {
        this.driverInfo = driverData;
        this.currentPosition = { lat: driverData.lat, lng: driverData.lng };
        
        // Mettre à jour la carte
        this.services.map.updateDriverPosition(driverData.lat, driverData.lng);
        
        // Mettre à jour l'interface
        this.ui.updateDriverInfo(driverData.name, this.currentPosition);
        this.ui.updateDriverStatus(CONFIG.STATUS.DRIVER_STATES.FREE, 'secondary');
        this.ui.updateConnectionStatus(true);
        
        // Passer au panneau de livraison
        this.ui.showDeliveryPanel();
        
        // Charger les livraisons
        await this.loadDeliveries();
        
        this.ui.showSuccess('Livreur configuré avec succès');
    }

    async createDelivery(deliveryData) {
        try {
            const response = await this.services.api.createDelivery(deliveryData);
            
            if (response.success) {
                this.ui.showSuccess('Livraison créée avec succès!');
                this.ui.clearDeliveryForm();
                await this.loadDeliveries();
            } else {
                throw new Error(response.message || 'Échec de la création');
            }
        } catch (error) {
            console.error('Error creating delivery:', error);
            throw error;
        }
    }

    async loadDeliveries() {
        try {
            const response = await this.services.api.getActiveDeliveries();
            
            if (response.success) {
                this.ui.renderDeliveries(response.deliveries, this.driverInfo.name);
            } else {
                this.ui.renderDeliveries([]);
            }
        } catch (error) {
            console.error('Error loading deliveries:', error);
            this.ui.showError('Erreur lors du chargement des livraisons');
        }
    }

    async selectDelivery(orderNumber) {
        try {
            // Récupérer les détails de la livraison via l'API
            const response = await this.services.api.getDelivery(orderNumber);
            
            if (response.success) {
                const delivery = response.delivery;
                this.selectedDelivery = orderNumber;
                
                // Afficher les détails de l'ordre sélectionné
                this.ui.showSelectedOrderDetails(delivery);
                
                // Mettre en surbrillance l'élément sélectionné
                this.ui.highlightSelectedDelivery(orderNumber);
                
                // Calculer et afficher la route estimée
                await this.calculateRouteEstimate(delivery);
                
                // Afficher les contrôles de simulation
                this.ui.showSimulationControls();
                
                // Afficher une notification de sélection
                this.ui.showNotification(
                    'success', 
                    '✅ Commande sélectionnée !', 
                    `La livraison ${orderNumber} a été sélectionnée. Consultez les détails ci-dessus et cliquez sur "Démarrer livraison" pour lancer la simulation.`,
                    5000
                );
            } else {
                throw new Error('Impossible de récupérer les détails de la livraison');
            }
        } catch (error) {
            console.error('Error selecting delivery:', error);
            this.ui.showError('Erreur lors de la sélection de la livraison: ' + error.message);
        }
    }

    async calculateRouteEstimate(delivery) {
        if (!this.currentPosition || !delivery.destination_lat || !delivery.destination_lng) {
            this.ui.updateRouteEstimates(null, null);
            return;
        }

        const transportMode = this.ui.getSelectedTransportMode();
        const mapboxProfile = CONFIG.SIMULATION.TRANSPORT_MODES[transportMode].mapboxProfile;

        try {
            // Utiliser l'API Mapbox Directions avec le bon profil de transport
            const query = await fetch(
                `https://api.mapbox.com/directions/v5/mapbox/${mapboxProfile}/${this.currentPosition.lng},${this.currentPosition.lat};${delivery.destination_lng},${delivery.destination_lat}?access_token=${CONFIG.MAPBOX.ACCESS_TOKEN}`
            );
            const result = await query.json();
            
            if (result.routes && result.routes.length > 0) {
                const route = result.routes[0];
                const duration = Math.round(route.duration / 60); // en minutes
                const distance = (route.distance / 1000).toFixed(1); // en km
                
                this.ui.updateRouteEstimates(duration, distance);
                this.currentRouteData = { route, totalDuration: duration, totalDistance: distance };
            } else {
                this.ui.updateRouteEstimates(null, null);
            }
        } catch (error) {
            console.error('Error calculating route:', error);
            this.ui.updateRouteEstimates(null, null);
        }
    }

    async recalculateRouteForTransportMode() {
        if (this.selectedDelivery) {
            const response = await this.services.api.getDelivery(this.selectedDelivery);
            if (response.success) {
                await this.calculateRouteEstimate(response.delivery);
            }
        }
    }

    calculateRemainingTimeDistance(currentPosition, destinationLat, destinationLng, progressPercent) {
        if (!this.currentRouteData) return { time: null, distance: null };

        const remaining = (100 - progressPercent) / 100;
        const remainingTime = Math.round(this.currentRouteData.totalDuration * remaining);
        const remainingDistance = (this.currentRouteData.totalDistance * remaining).toFixed(1);

        return { time: remainingTime, distance: remainingDistance };
    }

    async startSimulation(speed) {
        if (!this.selectedDelivery) {
            this.ui.showError('Veuillez sélectionner une livraison');
            return;
        }

        try {
            // Afficher une notification de démarrage
            this.ui.showNotification(
                'info', 
                '🚀 Simulation démarrée !', 
                `La simulation de livraison pour ${this.selectedDelivery} a démarré. Le livreur va maintenant se déplacer automatiquement vers la destination. Vous pouvez contrôler la vitesse et voir la progression en temps réel.`,
                6000
            );

            const response = await this.services.simulation.startSimulation(this.selectedDelivery, speed);
            
            if (response.success) {
                this.ui.updateDriverStatus(CONFIG.STATUS.DRIVER_STATES.DELIVERING, 'warning');
                
                // Notification supplémentaire de succès
                setTimeout(() => {
                    this.ui.showNotification(
                        'success',
                        '📍 Route calculée !',
                        'La route optimale a été calculée et le livreur se déplace maintenant vers la destination.',
                        4000
                    );
                }, 1500);
            }
        } catch (error) {
            console.error('Error starting simulation:', error);
            this.ui.showError('Erreur lors du démarrage de la simulation: ' + error.message);
        }
    }

    pauseSimulation() {
        this.services.simulation.pauseSimulation();
    }

    resumeSimulation() {
        this.services.simulation.resumeSimulation();
    }

    stopSimulation() {
        this.services.simulation.stopSimulation();
    }

    handleMapClick(lat, lng) {
        // Permettre de définir la position du livreur en cliquant sur la carte
        if (this.ui.elements.setupPanel.style.display !== 'none') {
            this.ui.updateMapPosition(lat, lng);
            this.services.map.updateDriverPosition(lat, lng);
        }
    }

    // Callbacks de simulation
    handleSimulationStarted(data) {
        const state = this.services.simulation.getSimulationState();
        this.ui.setSimulationControlsState(state.isRunning, state.isPaused);
        this.ui.updateProgress(0);
    }

    handleProgressUpdate(data) {
        this.ui.updateProgress(data.progress);
        this.currentPosition = data.position;
        this.ui.updateDriverInfo(this.driverInfo.name, this.currentPosition);
        
        // Mise à jour en temps réel du temps et distance restant
        if (this.selectedDelivery && this.currentRouteData && 
            this.currentRouteData.route && 
            this.currentRouteData.route.geometry && 
            this.currentRouteData.route.geometry.coordinates &&
            this.currentRouteData.route.geometry.coordinates.length > 0) {
            
            const coordinates = this.currentRouteData.route.geometry.coordinates;
            const lastCoordinate = coordinates[coordinates.length - 1];
            
            const remaining = this.calculateRemainingTimeDistance(
                this.currentPosition, 
                lastCoordinate[1], // latitude
                lastCoordinate[0], // longitude
                data.progress
            );
            
            if (remaining.time !== null && remaining.distance !== null) {
                this.ui.updateRouteEstimates(remaining.time, remaining.distance, true);
            }
        }
    }

    handleSimulationCompleted(data) {
        this.ui.updateProgress(100);
        this.ui.updateDriverStatus(CONFIG.STATUS.DRIVER_STATES.FREE, 'success');
        this.ui.showSuccess('Livraison terminée !');
        
        setTimeout(() => {
            this.resetSimulation();
            this.loadDeliveries();
        }, 2000);
    }

    handleSimulationStopped() {
        this.resetSimulation();
    }

    handleSimulationPaused() {
        const state = this.services.simulation.getSimulationState();
        this.ui.setSimulationControlsState(state.isRunning, state.isPaused);
    }

    handleSimulationResumed() {
        const state = this.services.simulation.getSimulationState();
        this.ui.setSimulationControlsState(state.isRunning, state.isPaused);
    }

    resetSimulation() {
        const state = this.services.simulation.getSimulationState();
        this.ui.setSimulationControlsState(state.isRunning, state.isPaused);
        this.ui.updateProgress(0);
        this.ui.updateDriverStatus(CONFIG.STATUS.DRIVER_STATES.FREE, 'secondary');
        this.ui.hideSimulationControls();
        this.selectedDelivery = null;
    }

    // Méthodes publiques pour les événements onclick
    refreshDeliveries() {
        this.eventHandler.refreshDeliveries();
    }

    handleMapClick(lat, lng) {
        this.eventHandler.handleMapClick(lat, lng);
    }

    // Getters pour les autres services
    getCurrentPosition() {
        return this.currentPosition || CONFIG.DRIVER.DEFAULT_POSITION;
    }

    getDriverInfo() {
        return this.driverInfo;
    }

    getSelectedDelivery() {
        return this.selectedDelivery;
    }
}

// Initialiser l'application quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    window.deliveryApp = new DeliveryDriverApp();
});