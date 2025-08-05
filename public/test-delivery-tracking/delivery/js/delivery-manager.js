class DeliveryManager {
    constructor(mapService, trackingService, ui, orderManager) {
        this.mapService = mapService;
        this.trackingService = trackingService;
        this.ui = ui;
        this.orderManager = orderManager;
        this.currentEstimatedDuration = 0;
        this.isCalculatingRoute = false;
        this.setupTrackingCallbacks();
    }

    setupTrackingCallbacks() {
        this.trackingService.on('trackingStarted', (data) => {
            const state = this.trackingService.getTrackingState();
            this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            
            if (data && data.apiResponse && data.apiResponse.data) {
                const trackingData = data.apiResponse.data;
                if (trackingData.progress_percentage) {
                    const progress = parseFloat(trackingData.progress_percentage);
                    if (!isNaN(progress)) {
                        this.ui.updateProgress(progress);
                    } else {
                        this.ui.updateProgress(0);
                    }
                } else {
                    this.ui.updateProgress(0);
                }
            } else {
                this.ui.updateProgress(0);
            }
            
            const selectedOrder = this.orderManager.getSelectedOrder();
            if (selectedOrder) {
                selectedOrder.status = DELIVERY_CONFIG.ORDER_STATUS.PROCESSING;
                this.ui.updateSelectedOrderDetails(selectedOrder);
            }
            
            this.ui.showInfo('Tracking démarré!');
        });
        
        this.trackingService.on('progressUpdate', (data) => {
            if (data.progress !== undefined) {
                this.ui.updateProgress(data.progress);
            }
            if (data.position) {
                this.ui.updateCurrentPosition(data.position, data.speed);
            }
            if (data.remainingDistance !== undefined && data.remainingTime !== undefined) {
                this.ui.updateRouteEstimates(data.remainingTime, data.remainingDistance, true);
            }
        });
        
        this.trackingService.on('trackingCompleted', (data) => {
            this.ui.updateProgress(100);
            this.ui.showSuccess('Livraison terminée avec succès!');
            
            const selectedOrder = this.orderManager.getSelectedOrder();
            if (selectedOrder) {
                selectedOrder.status = DELIVERY_CONFIG.ORDER_STATUS.DELIVERED;
                this.ui.updateSelectedOrderDetails(selectedOrder);
            }
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
        if (this.isCalculatingRoute) {
            return;
        }
        
        this.isCalculatingRoute = true;
        
        const selectedOrder = this.orderManager.getSelectedOrder();
        
        if (!selectedOrder || !selectedOrder.delivery_address?.latitude || !selectedOrder.delivery_address?.longitude) {
            this.ui.updateRouteEstimates(null, null);
            this.isCalculatingRoute = false;
            return;
        }
        
        try {
            if (selectedOrder.status === DELIVERY_CONFIG.ORDER_STATUS.PROCESSING && selectedOrder.trackingData) {
                const trackingData = selectedOrder.trackingData;
                
                this.ui.updateRouteEstimates(
                    parseInt(trackingData.estimated_duration),
                    parseFloat(trackingData.distance_remaining).toFixed(2),
                    true
                );
                
                this.currentEstimatedDuration = parseInt(trackingData.estimated_duration);
                
                if (trackingData.progress_percentage !== undefined && trackingData.progress_percentage !== null) {
                    const progress = parseFloat(trackingData.progress_percentage);
                    if (!isNaN(progress)) {
                        this.ui.updateProgress(progress);
                    }
                }
                
                if (trackingData.current_speed && trackingData.current_speed > 0) {
                    const speed = parseFloat(trackingData.current_speed);
                    if (!isNaN(speed)) {
                        this.ui.elements.simulationSpeed.value = speed;
                        this.ui.updateTravelSpeedDisplay(speed);
                        this.ui.updateCurrentPosition(null, speed);
                    }
                }
                
                this.ui.showDeliveryControls();
                this.ui.trackingUI.updateDeliveryButtonsForInProgressOrder();
                
                if (trackingData.driver_lat && trackingData.driver_lng) {
                    const driverLat = parseFloat(trackingData.driver_lat);
                    const driverLng = parseFloat(trackingData.driver_lng);
                    
                    if (!isNaN(driverLat) && !isNaN(driverLng)) {
                        const driverPosition = { lat: driverLat, lng: driverLng };
                        const destPosition = {
                            lat: selectedOrder.delivery_address.latitude,
                            lng: selectedOrder.delivery_address.longitude
                        };
                        
                        this.mapService.drawRoute(driverPosition, destPosition, 'driving');
                    }
                }
                
                this.isCalculatingRoute = false;
                return;
            }
            
            let currentPosition = await this.mapService.getCurrentGPSPosition();
            let defaultSpeed = this.ui.getTravelSpeed();
            
            const routeInfo = await this.mapService.drawRoute(
                currentPosition,
                {
                    lat: selectedOrder.delivery_address.latitude,
                    lng: selectedOrder.delivery_address.longitude
                },
                'driving'
            );
            
            if (routeInfo) {
                const travelSpeed = defaultSpeed;
                const TYPICAL_DRIVING_SPEED_KMH = 40;
                
                let adjustedDuration = routeInfo.duration;
                if (travelSpeed && travelSpeed !== TYPICAL_DRIVING_SPEED_KMH) {
                    adjustedDuration = Math.round((parseFloat(routeInfo.distance) / travelSpeed) * 60);
                }
                
                this.ui.updateRouteEstimates(adjustedDuration, routeInfo.distance);
                this.currentEstimatedDuration = adjustedDuration;
                this.ui.updateCurrentPosition(null, travelSpeed);
            } else {
                this.ui.updateRouteEstimates(null, null);
            }
        } catch (error) {
            console.error('Erreur lors du calcul de route:', error);
            this.ui.updateRouteEstimates(null, null);
        } finally {
            this.isCalculatingRoute = false;
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
            const speed = this.ui.getTravelSpeed();
            const orderIdentifier = selectedOrder.id;
            
            await this.trackingService.startTracking(orderIdentifier, speed, this.currentEstimatedDuration);
            
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
        this.ui.elements.startDeliveryBtn.addEventListener('click', () => this.startDelivery());
        this.ui.elements.pauseDeliveryBtn.addEventListener('click', () => this.pauseDelivery());
        this.ui.elements.stopDeliveryBtn.addEventListener('click', () => this.stopDelivery());
        this.ui.elements.completeDeliveryBtn.addEventListener('click', () => this.completeDelivery());

        this.ui.elements.simulationSpeed.addEventListener('input', () => {
            const speed = this.ui.getTravelSpeed();
            this.ui.updateTravelSpeedDisplay(speed);
            this.ui.updateCurrentPosition(null, speed); 
            this.calculateRouteForSelectedOrder();
        });

        this.initializeSpeedDisplay();
    }

    initializeSpeedDisplay() {
        setTimeout(() => {
            const initialSpeed = this.ui.getTravelSpeed();
            this.ui.updateTravelSpeedDisplay(initialSpeed);
            this.ui.updateCurrentPosition(null, initialSpeed);
        }, 100);
    }

    getTrackingState() {
        return this.trackingService.getTrackingState();
    }
}