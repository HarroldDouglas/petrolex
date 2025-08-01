class DeliveryManager {
    constructor(mapService, trackingService, ui, orderManager) {
        this.mapService = mapService;
        this.trackingService = trackingService;
        this.ui = ui;
        this.orderManager = orderManager;
        this.setupTrackingCallbacks();
    }

    setupTrackingCallbacks() {
        this.trackingService.on('trackingStarted', (data) => {
            const state = this.trackingService.getTrackingState();
            this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            this.ui.updateProgress(0);
            
            const selectedOrder = this.orderManager.getSelectedOrder();
            if (selectedOrder) {
                selectedOrder.status = CONFIG.ORDER_STATUS.PROCESSING;
                this.ui.updateSelectedOrderDetails(selectedOrder);
            }
            
            this.ui.showInfo('Tracking démarré! La position sera mise à jour en temps réel.');
        });

        // NOUVEAU: Callback pour quand la route est calculée
        this.trackingService.on('routeCalculated', (data) => {
            this.ui.updateRouteEstimates(data.duration, data.distance);
            this.ui.showSuccess(`Route calculée: ${data.distance}km, ${data.duration}min estimées`);
        });
        
        this.trackingService.on('progressUpdate', (data) => {
            this.ui.updateProgress(data.progress);
            this.ui.updateCurrentPosition(data.position, data.speed);
            
            if (data.progress > 0) {
                const estimatedTimeElement = this.ui.elements.estimatedTime.textContent;
                if (estimatedTimeElement && estimatedTimeElement !== 'Non disponible') {
                    const totalTime = parseInt(estimatedTimeElement);
                    if (!isNaN(totalTime)) {
                        const remainingTime = Math.round(totalTime * ((100 - data.progress) / 100));
                        this.ui.updateRouteEstimates(remainingTime, null, true);
                    }
                }
            }
        });
        
        this.trackingService.on('trackingCompleted', (data) => {
            this.ui.updateProgress(100);
            this.ui.showSuccess('Livraison terminée avec succès!');
            
            const selectedOrder = this.orderManager.getSelectedOrder();
            if (selectedOrder) {
                selectedOrder.status = CONFIG.ORDER_STATUS.DELIVERED;
                this.ui.updateSelectedOrderDetails(selectedOrder);
            }
            
            // Le bloc de commande ne sera masqué que lorsque l'utilisateur cliquera sur "Terminer"
            // setTimeout(() => {
            //     this.resetDelivery();
            //     this.orderManager.loadOrders();
            // }, 3000);
        });
        
        this.trackingService.on('trackingStopped', () => {
            this.resetDelivery();
            this.ui.showInfo('Livraison arrêtée');
        });
        
        this.trackingService.on('trackingPaused', () => {
            const state = this.trackingService.getTrackingState();
            this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            this.ui.showInfo('Livraison mise en pause');
        });
        
        this.trackingService.on('trackingResumed', () => {
            const state = this.trackingService.getTrackingState();
            this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            this.ui.showInfo('Livraison reprise');
        });
    }

    async calculateRouteForSelectedOrder() {
        const selectedOrder = this.orderManager.getSelectedOrder();
        if (!selectedOrder || !selectedOrder.delivery_address_latitude || !selectedOrder.delivery_address_longitude) {
            return;
        }
        
        try {
            const currentPosition = await this.mapService.getCurrentGPSPosition();
            const transportMode = this.ui.getSelectedTransportMode();
            
            const routeInfo = await this.mapService.drawRoute(
                currentPosition,
                {
                    lat: selectedOrder.delivery_address_latitude,
                    lng: selectedOrder.delivery_address_longitude
                },
                transportMode
            );
            
            if (routeInfo) {
                this.ui.updateRouteEstimates(routeInfo.duration, routeInfo.distance);
            } else {
                this.ui.updateRouteEstimates(null, null);
            }
        } catch (error) {
            console.error('Error calculating route:', error);
            this.ui.updateRouteEstimates(null, null);
        }
    }

    async startDelivery() {
        const selectedOrder = this.orderManager.getSelectedOrder();
        if (!selectedOrder) {
            this.ui.showError('Veuillez sélectionner une commande');
            return;
        }
        
        const trackingState = this.trackingService.getTrackingState();
        
        if (trackingState.isPaused) {
            this.trackingService.resumeTracking();
            return;
        }
        
        if (trackingState.isTracking) {
            this.ui.showError('Une livraison est déjà en cours');
            return;
        }
        
        this.ui.setLoadingState('startDeliveryBtn', true);
        
        try {
            const speed = this.ui.getSimulationSpeed();
            await this.trackingService.startTracking(selectedOrder.order_number, speed);
            this.ui.showSuccess('Livraison démarrée!');
        } catch (error) {
            this.ui.showError(error.message || 'Erreur lors du démarrage de la livraison');
        } finally {
            this.ui.setLoadingState('startDeliveryBtn', false);
        }
    }

    pauseDelivery() {
        this.trackingService.pauseTracking();
    }

    stopDelivery() {
        if (confirm('Êtes-vous sûr de vouloir arrêter cette livraison ?')) {
            this.trackingService.stopTracking();
        }
    }

    async completeDelivery() {
        const selectedOrder = this.orderManager.getSelectedOrder();
        if (!selectedOrder) {
            this.ui.showError('Veuillez sélectionner une commande à terminer.');
            return;
        }

        if (confirm('Êtes-vous sûr de vouloir marquer cette livraison comme terminée ?')) {
            this.ui.setLoadingState('completeDeliveryBtn', true);
            try {
                await this.trackingService.completeTracking(selectedOrder.id);
                this.ui.showSuccess('Livraison marquée comme terminée!');
                this.resetDelivery();
                this.orderManager.loadOrders();
                this.ui.hideSelectedOrderDetails();
            } catch (error) {
                this.ui.showError(error.message || 'Erreur lors de la finalisation de la livraison.');
            } finally {
                this.ui.setLoadingState('completeDeliveryBtn', false);
            }
        }
    }

    resetDelivery() {
        const state = this.trackingService.getTrackingState();
        this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
        this.ui.updateProgress(0);
        this.orderManager.clearSelectedOrder();
    }

    setupEventHandlers() {
        this.ui.elements.transportWalking.addEventListener('change', () => {
            this.ui.updateTransportInfo();
            this.calculateRouteForSelectedOrder();
        });
        
        this.ui.elements.transportDriving.addEventListener('change', () => {
            this.ui.updateTransportInfo();
            this.calculateRouteForSelectedOrder();
        });
        
        this.ui.elements.startDeliveryBtn.addEventListener('click', () => this.startDelivery());
        this.ui.elements.pauseDeliveryBtn.addEventListener('click', () => this.pauseDelivery());
        this.ui.elements.stopDeliveryBtn.addEventListener('click', () => this.stopDelivery());
    }

    getTrackingState() {
        return this.trackingService.getTrackingState();
    }
}