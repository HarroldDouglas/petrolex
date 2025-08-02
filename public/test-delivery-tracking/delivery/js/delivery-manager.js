class DeliveryManager {
    constructor(mapService, trackingService, ui, orderManager) {
        this.mapService = mapService;
        this.trackingService = trackingService;
        this.ui = ui;
        this.orderManager = orderManager;
        this.currentEstimatedDuration = 0; // Nouvelle propriété pour stocker la durée estimée
        this.isCalculatingRoute = false; // Protection contre les appels multiples simultanés
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

        // SUPPRIMÉ: Callback pour routeCalculated qui causait le conflit
        // Ne plus écraser le calcul de route personnalisé
        
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
            
            // Ne pas rediriger automatiquement - sera exécuté que lorsque l'utilisateur cliquera sur "Terminer"
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
        // Protection contre les appels multiples simultanés
        if (this.isCalculatingRoute) {
            console.log('calculateRouteForSelectedOrder: Calcul déjà en cours, ignorer');
            return;
        }
        
        this.isCalculatingRoute = true;
        
        const selectedOrder = this.orderManager.getSelectedOrder();
        
        if (!selectedOrder || !selectedOrder.delivery_address?.latitude || !selectedOrder.delivery_address?.longitude) {
            console.warn('Commande non sélectionnée ou adresse de livraison manquante');
            this.ui.updateRouteEstimates(null, null); // Réinitialiser les estimations si pas de commande
            this.isCalculatingRoute = false;
            return;
        }
        
        try {
            const currentPosition = await this.mapService.getCurrentGPSPosition();
            const routeInfo = await this.mapService.drawRoute(
                currentPosition,
                {
                    lat: selectedOrder.delivery_address.latitude,
                    lng: selectedOrder.delivery_address.longitude
                },
                'driving' // Mode de transport par défaut
            );
            
            if (routeInfo) {
                const travelSpeed = this.ui.getTravelSpeed();
                const TYPICAL_DRIVING_SPEED_KMH = 40; // Vitesse de référence pour les calculs
                
                let adjustedDuration = routeInfo.duration;
                if (travelSpeed && travelSpeed !== TYPICAL_DRIVING_SPEED_KMH) {
                    // Ajuster la durée en fonction de la vitesse sélectionnée
                    // Formule: (distance / vitesse) * 60
                    adjustedDuration = Math.round((parseFloat(routeInfo.distance) / travelSpeed) * 60);
                }
                
                this.ui.updateRouteEstimates(adjustedDuration, routeInfo.distance);
                // Mettre à jour la durée estimée pour la simulation
                this.currentEstimatedDuration = adjustedDuration;
                
                // AJOUT IMPORTANT : Afficher la vitesse après le calcul de route
                this.ui.updateCurrentPosition(null, travelSpeed);
            }
        } catch (error) {
            console.error('Error calculating route:', error);
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
            // IMPORTANT: Passer la durée estimée affichée pour que la simulation dure exactement ce temps
            await this.trackingService.startTracking(selectedOrder.order_number, speed, this.currentEstimatedDuration);
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

        // Mettre à jour l'affichage de la vitesse de déplacement et recalculer la route en temps réel
        this.ui.elements.simulationSpeed.addEventListener('input', () => {
            const speed = this.ui.getTravelSpeed();
            this.ui.updateTravelSpeedDisplay(speed);
            // Mettre à jour la vitesse affichée en bas du temps et de la distance
            this.ui.updateCurrentPosition(null, speed); 
            this.calculateRouteForSelectedOrder();
        });

        // Initialiser l'affichage de la vitesse dès le début
        this.initializeSpeedDisplay();
    }

    // Nouvelle méthode pour initialiser l'affichage de la vitesse
    initializeSpeedDisplay() {
        // Attendre que l'élément soit disponible
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