class DeliveryManager {
    constructor(mapService, trackingService, ui, orderManager) {
        this.mapService = mapService;
        this.trackingService = trackingService;
        this.ui = ui;
        this.orderManager = orderManager;
        this.currentEstimatedDuration = 0;
        this.isCalculatingRoute = false;
        this.controlsFacade = null;
        
        this.setupTrackingCallbacks();
    }

    // Méthode pour injecter la facade
    setControlsFacade(facade) {
        this.controlsFacade = facade;
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
            
            // 🎯 Utiliser la facade pour mettre à jour les contrôles
            if (this.controlsFacade) {
                this.controlsFacade.handleTrackingStarted(data.progress || 0);
            }
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
            
            // 🎯 Utiliser la facade pour l'état de completion
            if (this.controlsFacade) {
                this.controlsFacade.handleTrackingCompleted();
            }
        });
        
        this.trackingService.on('trackingStopped', () => {
            this.resetDelivery();
            this.ui.showInfo('Livraison arrêtée');
        });
        
        this.trackingService.on('trackingPaused', () => {
            const state = this.trackingService.getTrackingState();
            
            // 🎯 Utiliser la facade au lieu de l'ancienne méthode
            if (this.controlsFacade) {
                this.controlsFacade.handleTrackingPaused(state.progress || 0);
            } else {
                // Fallback vers l'ancienne méthode
                this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            }
            
            this.ui.showInfo('Livraison mise en pause');
        });
        
        this.trackingService.on('trackingResumed', () => {
            const state = this.trackingService.getTrackingState();
            
            // 🎯 Utiliser la facade au lieu de l'ancienne méthode
            if (this.controlsFacade) {
                this.controlsFacade.handleTrackingResumed(state.progress || 0);
            } else {
                // Fallback vers l'ancienne méthode
                this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
            }
            
            this.ui.showInfo('Livraison reprise');
        });
    }

    async calculateRouteForSelectedOrder() {
        if (this.isCalculatingRoute) {
            console.log('🔄 Calcul de route déjà en cours, ignoré');
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
            if (selectedOrder.status === DELIVERY_CONFIG.ORDER_STATUS.IN_PROGRESS && selectedOrder.trackingData) {
                const trackingData = selectedOrder.trackingData;
                
                this.ui.updateRouteEstimates(
                    parseInt(trackingData.estimated_duration),
                    parseFloat(trackingData.distance_remaining).toFixed(2),
                    true
                );
                
                this.currentEstimatedDuration = parseInt(trackingData.estimated_duration);
                this.isCalculatingRoute = false;
                return;
            }
            
            console.log('🗺️ Calcul de nouvelle route...');
            let currentPosition = await this.mapService.getCurrentGPSPosition();
            
            console.log('📍 Position actuelle:', currentPosition);
            console.log('🏠 Destination:', selectedOrder.delivery_address);
            
            // Afficher les marqueurs
            this.mapService.updateDriverPosition(
                currentPosition.lat, 
                currentPosition.lng, 
                { name: 'Ma position' }
            );
            
            this.mapService.setDestination(
                parseFloat(selectedOrder.delivery_address.latitude),
                parseFloat(selectedOrder.delivery_address.longitude),
                { 
                    name: selectedOrder.customer?.full_name || 'Client',
                    address: selectedOrder.delivery_address.address 
                }
            );
            
            // Tracer la route avec les paramètres corrects
            const routeInfo = await this.mapService.drawRoute(
                currentPosition.lat,
                currentPosition.lng,
                parseFloat(selectedOrder.delivery_address.latitude),
                parseFloat(selectedOrder.delivery_address.longitude)
            );
            
            if (routeInfo) {
                const currentSpeed = this.ui.getTravelSpeed();
                
                // 🔧 CORRECTION: Calcul simple et direct
                const distanceKm = parseFloat(routeInfo.distance) / 1000; // Convertir mètres en km
                const speedKmh = parseFloat(currentSpeed);
                
                console.log('📏 Distance calculée:', distanceKm, 'km');
                console.log('🚗 Vitesse:', speedKmh, 'km/h');
                
                let adjustedDuration;
                if (speedKmh && speedKmh > 0) {
                    // Calcul: temps = distance / vitesse (en heures) * 60 (pour minutes)
                    adjustedDuration = Math.round((distanceKm / speedKmh) * 60);
                } else {
                    // Fallback sur la durée originale de Mapbox
                    adjustedDuration = routeInfo.duration;
                }
                
                console.log(`✅ Route calculée: ${distanceKm}km en ${adjustedDuration}min à ${speedKmh}km/h`);
                
                // 🔧 CORRECTION CRITIQUE: Vérifier d'abord s'il y a des données existantes
                // Debug : vérifier les données de tracking
                console.log('🔍 [DeliveryManager] Vérification trackingData:', {
                    hasTrackingData: !!selectedOrder.trackingData,
                    trackingData: selectedOrder.trackingData,
                    progressPercentage: selectedOrder.trackingData?.progress_percentage
                });
                
                // Vérifier s'il y a des données de tracking existantes
                const hasExistingTracking = selectedOrder.trackingData && 
                    selectedOrder.trackingData.progress_percentage !== null && 
                    selectedOrder.trackingData.progress_percentage !== undefined &&
                    parseFloat(selectedOrder.trackingData.progress_percentage) > 0;
                
                // 🔧 VÉRIFICATION DANS LA BASE DE DONNÉES pour éviter les races conditions
                const existingTrackingFromDB = await this.checkExistingTrackingInDB(selectedOrder.id);
                
                if (!hasExistingTracking && !existingTrackingFromDB) {
                    // Pas de tracking existant - utiliser nouvelles données calculées
                    console.log('📡 Pas de tracking existant - initialisation avec nouvelles données');
                    this.ui.updateRouteEstimates(adjustedDuration, distanceKm);
                    this.currentEstimatedDuration = adjustedDuration;
                    this.ui.updateCurrentPosition(null, currentSpeed);
                    
                    await this.updateTrackingData(selectedOrder.id, {
                        distance_remaining: parseFloat(distanceKm.toFixed(2)),
                        estimated_duration: adjustedDuration,
                        progress_percentage: 0 // Nouveau calcul, donc 0%
                    });
                } else {
                    // Tracking existant - utiliser données existantes
                    const existingProgress = parseFloat(selectedOrder.trackingData.progress_percentage);
                    const existingDistance = parseFloat(selectedOrder.trackingData.distance_remaining);
                    const existingDuration = parseInt(selectedOrder.trackingData.estimated_duration);
                    
                    console.log('✅ Tracking existant détecté - conservation des données:', {
                        fromSelectedOrder: hasExistingTracking,
                        fromDatabase: existingTrackingFromDB,
                        progress: existingProgress,
                        remaining: existingDistance,
                        duration: existingDuration
                    });
                    
                    // 🔧 UTILISER LES DONNÉES EXISTANTES dans l'UI
                    this.ui.updateRouteEstimates(existingDuration, existingDistance);
                    this.ui.updateProgress(existingProgress); // 🎯 AJOUT: Afficher la progression existante!
                    this.currentEstimatedDuration = existingDuration;
                    this.ui.updateCurrentPosition(null, currentSpeed);
                    
                    // 🔧 METTRE À JOUR LA POSITION DU LIVREUR sur la carte avec les données existantes
                    if (selectedOrder.trackingData.driver_lat && selectedOrder.trackingData.driver_lng) {
                        const currentDriverPosition = {
                            lat: parseFloat(selectedOrder.trackingData.driver_lat),
                            lng: parseFloat(selectedOrder.trackingData.driver_lng)
                        };
                        
                        console.log(`🗺️ Mise à jour position livreur existante: ${currentDriverPosition.lat}, ${currentDriverPosition.lng}`);
                        
                        this.mapService.updateDriverPosition(
                            currentDriverPosition.lat,
                            currentDriverPosition.lng,
                            `Position actuelle - ${selectedOrder.order_number}`
                        );
                        
                        // 🔧 RECALCULER L'ITINÉRAIRE depuis la position actuelle du livreur
                        const destination = selectedOrder.destination_coordinates || selectedOrder.delivery_address;
                        if (destination) {
                            console.log(`🗺️ Recalcul itinéraire depuis position actuelle vers destination`);
                            
                            const routeInfo = await this.mapService.drawRoute(
                                currentDriverPosition.lat,
                                currentDriverPosition.lng,
                                destination.latitude,
                                destination.longitude
                            );
                            
                            if (routeInfo) {
                                console.log(`✅ Itinéraire recalculé depuis position actuelle: ${existingDistance}km restants`);
                            }
                        }
                    }
                }
            } else {
                this.ui.updateRouteEstimates(null, null);
            }
        } catch (error) {
            console.error('❌ Erreur lors du calcul de route:', error);
            this.ui.updateRouteEstimates(null, null);
        } finally {
            this.isCalculatingRoute = false;
        }
    }
    
    // 🔧 NOUVELLE MÉTHODE: Recalculer seulement le temps basé sur la vitesse actuelle
    updateTimeEstimateBasedOnSpeed() {
        const selectedOrder = this.orderManager.getSelectedOrder();
        if (!selectedOrder) return;
        
        // Récupérer la distance affichée actuelle
        const distanceText = this.ui.elements.estimatedDistance?.textContent;
        if (!distanceText || distanceText === 'Calcul...' || distanceText === 'Non disponible') {
            console.log('⚠️ Pas de distance disponible pour recalculer le temps');
            return;
        }
        
        // Extraire la valeur numérique de la distance (ex: "9.6 km" → 9.6)
        const distanceMatch = distanceText.match(/(\d+\.?\d*)/);
        if (!distanceMatch) {
            console.log('⚠️ Impossible d\'extraire la distance:', distanceText);
            return;
        }
        
        const distanceKm = parseFloat(distanceMatch[1]);
        const currentSpeed = this.ui.getTravelSpeed();
        
        if (currentSpeed && currentSpeed > 0) {
            // Recalculer le temps avec la nouvelle vitesse
            const newTimeMinutes = Math.round((distanceKm / currentSpeed) * 60);
            
            // Mettre à jour seulement le temps (pas la distance)
            this.ui.elements.estimatedTime.textContent = `${newTimeMinutes} min`;
            this.currentEstimatedDuration = newTimeMinutes;
            
            console.log(`🔄 Temps recalculé: ${distanceKm}km en ${newTimeMinutes}min à ${currentSpeed}km/h`);
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

    // 🔧 NOUVELLE MÉTHODE: Reprendre une livraison en cours
    async resumeDelivery(order) {
        try {
            console.log(`🔄 [DeliveryManager] Reprise de la livraison pour: ${order.order_number}`);
            
            // Vérifier que la commande a des données de tracking
            if (!order.trackingData) {
                throw new Error('Aucune donnée de tracking trouvée pour cette commande');
            }
            
            const trackingData = order.trackingData;
            
            // Restaurer l'état de l'interface avec les données existantes
            this.ui.updateProgress(trackingData.progress_percentage || 0);
            
            // Mettre à jour les estimations de route avec les données existantes
            if (trackingData.estimated_duration && trackingData.distance_remaining) {
                this.ui.updateRouteEstimates(
                    parseInt(trackingData.estimated_duration),
                    parseFloat(trackingData.distance_remaining).toFixed(2),
                    true
                );
                this.currentEstimatedDuration = parseInt(trackingData.estimated_duration);
            }
            
            // Reprendre le tracking avec les données existantes
            await this.trackingService.resumeExistingTracking(order.id, trackingData);
            
            // Mettre à jour les contrôles pour montrer que le tracking est actif
            if (this.controlsFacade) {
                this.controlsFacade.handleTrackingResumed(trackingData.progress_percentage || 0);
            }
            
            console.log(`✅ [DeliveryManager] Livraison reprise avec succès:`, {
                progression: trackingData.progress_percentage + '%',
                distance_restante: trackingData.distance_remaining + 'km',
                temps_estime: trackingData.estimated_duration + 'min'
            });
            
        } catch (error) {
            console.error(`❌ [DeliveryManager] Erreur lors de la reprise:`, error);
            throw new Error(`Impossible de reprendre la livraison: ${error.message}`);
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

        // 🔧 CORRECTION: Séparer les événements
        // 1. Input : seulement mise à jour de l'affichage
        this.ui.elements.simulationSpeed.addEventListener('input', () => {
            const speed = this.ui.getTravelSpeed();
            this.ui.updateTravelSpeedDisplay(speed);
            this.ui.updateCurrentPosition(null, speed);
            // PAS de recalcul ici !
        });
        
        // 🔧 2. Bouton "Mettre à jour" : recalculer le temps
        this.ui.elements.updateSpeedBtn.addEventListener('click', () => {
            console.log('🔄 Mise à jour manuelle de la vitesse demandée');
            this.updateTimeEstimateBasedOnSpeed();
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

    /**
     * Vérifier s'il existe des données de tracking dans la base de données
     */
    async checkExistingTrackingInDB(orderId) {
        try {
            const token = localStorage.getItem('delivery_person_token');
            if (!token) {
                return false;
            }

            const response = await fetch(`${SHARED_CONFIG.API.BASE_URL}/tracking/delivery/${orderId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                const progressPercentage = parseFloat(result.data?.progress_percentage || 0);
                console.log('🔍 [DeliveryManager] Vérification DB tracking:', {
                    orderId,
                    progress: progressPercentage + '%',
                    hasProgress: progressPercentage > 0
                });
                return progressPercentage > 0;
            }
            return false;
        } catch (error) {
            console.error('❌ Erreur vérification tracking DB:', error);
            return false;
        }
    }

    /**
     * Met à jour les données de tracking dans le backend (distance, durée, etc.)
     */
    async updateTrackingData(orderId, trackingData) {
        try {
            console.log(`📡 Envoi des données de tracking au backend:`, trackingData);
            
            // Récupérer le token d'authentification
            const token = localStorage.getItem('delivery_person_token');
            if (!token) {
                console.error('❌ Pas de token d\'authentification');
                return;
            }

            // Ajouter la position actuelle si disponible
            const currentPosition = await this.getCurrentPosition();
            if (currentPosition) {
                trackingData.driver_lat = currentPosition.lat;
                trackingData.driver_lng = currentPosition.lng;
            }

            // Appel API pour mettre à jour le tracking
            const response = await fetch(`${SHARED_CONFIG.API.BASE_URL}/tracking/delivery/${orderId}/position`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(trackingData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log(`✅ Données de tracking mises à jour:`, result);

        } catch (error) {
            console.error('❌ Erreur lors de la mise à jour du tracking:', error);
        }
    }

    /**
     * Récupère la position actuelle du livreur
     */
    async getCurrentPosition() {
        try {
            return await this.mapService.getCurrentPosition();
        } catch (error) {
            console.warn('⚠️ Impossible de récupérer la position actuelle:', error);
            return null;
        }
    }
}