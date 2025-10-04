/**
 * Delivery Tracking Service - Handles real-time tracking simulation and updates
 * Manages route calculation, position simulation, and server communication
 */
class DeliveryTrackingService {
    // Constants for configuration
    static ROUTE_UPDATE_THROTTLE_MS = 3000; // Minimum interval between route redraws
    static MIN_SIMULATION_SPEED_KMH = 10;
    static MAX_SIMULATION_SPEED_KMH = 80;
    static DEFAULT_SIMULATION_SPEED_KMH = 40;
    static POSITION_UPDATE_INTERVAL_MS = 2000; // Position update frequency

    constructor(apiService, mapService, ui, orderManager = null) {
        this.apiService = apiService;
        this.trackingApi = new DeliveryTrackingApiService(apiService);
        this.mapService = mapService;
        this.ui = ui;
        this.orderManager = orderManager;

        this.state = {
            isTracking: false,
            isPaused: false,
            currentOrder: null,
            routeCoordinates: [],
            currentIndex: 0,
        };

        this.intervals = {
            simulation: null,
            positionUpdate: null,
        };

        this.callbacks = {};
    }

    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    emit(event, data = null) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach((callback) => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Callback error for event ${event}:`, error);
                }
            });
        }
    }

    async startTracking(orderIdentifier, speed = DELIVERY_CONFIG.SIMULATION.DEFAULT_SPEED, estimatedDurationMinutes = null) {
        if (this.state.isTracking) {
            throw new Error("Tracking already in progress");
        }

        try {
            const trackingData = await this.initializeTracking(orderIdentifier, estimatedDurationMinutes);
            await this.setupRoute(trackingData.orderDetails, trackingData.currentPosition, speed, estimatedDurationMinutes);
            this.startPositionUpdates();
            this.emit("trackingStarted", trackingData);
            return trackingData.apiResponse;
        } catch (error) {
            this.resetState();
            throw error;
        }
    }

    async initializeTracking(orderIdentifier, estimatedDurationMinutes) {
        let currentPosition;
        
        if (this.orderManager) {
            const selectedOrder = this.orderManager.getSelectedOrder();
            
            if (selectedOrder && selectedOrder.trackingData && selectedOrder.id === orderIdentifier) {
                const trackingData = selectedOrder.trackingData;
                
                // 🔧 CORRECTION CRITIQUE: Validation plus stricte des coordonnées
                console.log('🔍 [TrackingService] Vérification des coordonnées du livreur:', {
                    driver_lat: trackingData.driver_lat,
                    driver_lng: trackingData.driver_lng,
                    types: {
                        lat: typeof trackingData.driver_lat,
                        lng: typeof trackingData.driver_lng
                    }
                });
                
                if (trackingData.driver_lat && trackingData.driver_lng) {
                    const driverLat = parseFloat(trackingData.driver_lat);
                    const driverLng = parseFloat(trackingData.driver_lng);
                    
                    // Vérification supplémentaire pour s'assurer que les coordonnées sont valides
                    if (!isNaN(driverLat) && !isNaN(driverLng) && 
                        driverLat !== 0 && driverLng !== 0 &&
                        Math.abs(driverLat) <= 90 && Math.abs(driverLng) <= 180) {
                        
                        currentPosition = {
                            lat: driverLat,
                            lng: driverLng
                        };
                        
                        console.log('✅ [TrackingService] Utilisation de la position exacte du livreur:', currentPosition);
                    } else {
                        console.warn('⚠️ [TrackingService] Coordonnées du livreur invalides, fallback vers GPS');
                        currentPosition = await this.mapService.getCurrentGPSPosition();
                    }
                } else {
                    console.warn('⚠️ [TrackingService] Coordonnées du livreur manquantes, fallback vers GPS');
                    currentPosition = await this.mapService.getCurrentGPSPosition();
                }
                
                const apiResponse = {
                    _metadata: { success: true },
                    data: selectedOrder.trackingData
                };
                
                this.state.currentOrder = apiResponse.data;
                this.state.isTracking = true;
                this.state.isPaused = false;
                
                // 🔧 CORRECTION CRITIQUE: Initialiser currentIndex basé sur la progression existante
                const existingProgress = parseFloat(trackingData.progress_percentage || 0);
                console.log('🔄 [TrackingService] Progression existante détectée:', existingProgress + '%');
                
                if (existingProgress > 0) {
                    // On initialisera currentIndex après avoir récupéré les coordonnées de route
                    this.state.existingProgress = existingProgress;
                }
                
                const orderDetails = {
                    id: this.state.currentOrder.order_id,
                    order_number: this.state.currentOrder.order_number,
                    delivery_address: {
                        latitude: this.state.currentOrder.destination_lat,
                        longitude: this.state.currentOrder.destination_lng,
                        name: this.state.currentOrder.destination_address || "Adresse de livraison"
                    },
                    customer: {
                        full_name: this.state.currentOrder.customer_name || "Client",
                        phone_number: this.state.currentOrder.driver_phone
                    }
                };
                
                this.mapService.updateDriverPosition(
                    currentPosition.lat,
                    currentPosition.lng,
                    `Reprise de livraison - ${this.state.currentOrder?.order_number}`,
                );
                
                return { apiResponse, orderDetails, currentPosition };
            }
        }

        // 🔧 CORRECTION: Charger les détails de commande AVANT de démarrer le tracking
        let orderDetails;

        if (typeof orderIdentifier === 'number') {
            // C'est un ID, convertir en order_number et charger depuis le cache
            const orderNumber = this.apiService.getOrderNumberFromId(orderIdentifier);
            if (orderNumber) {
                orderDetails = this.apiService.getOrderFromCache(orderNumber);
            }
        } else {
            // C'est un order_number
            orderDetails = this.apiService.getOrderFromCache(orderIdentifier);
        }

        if (!orderDetails || !orderDetails.delivery_address) {
            throw new Error("Détails de commande introuvables. Veuillez recharger la page.");
        }

        // 🔧 CORRECTION CRITIQUE: Déterminer la position initiale du livreur
        let currentPosition;

        if (orderDetails.distribution_center?.latitude && orderDetails.distribution_center?.longitude) {
            // ✅ UTILISER LA POSITION DU CENTRE DE DISTRIBUTION
            currentPosition = {
                lat: parseFloat(orderDetails.distribution_center.latitude),
                lng: parseFloat(orderDetails.distribution_center.longitude)
            };
            console.log('✅ [TrackingService] Position initiale: Centre de distribution', {
                center: orderDetails.distribution_center.name,
                position: currentPosition
            });
        } else {
            // ⚠️ FALLBACK: GPS uniquement si pas de centre de distribution
            console.warn('⚠️ [TrackingService] Pas de centre de distribution, utilisation GPS');
            currentPosition = await this.mapService.getCurrentGPSPosition();
        }

        // Démarrer le tracking avec la position correcte
        const apiResponse = await this.trackingApi.startTracking(orderIdentifier, currentPosition);

        if (!apiResponse._metadata?.success || !apiResponse.data) {
            throw new Error(apiResponse._metadata?.message || "Impossible de démarrer le tracking");
        }

        this.state.currentOrder = apiResponse.data;
        this.state.isTracking = true;
        this.state.isPaused = false;

        this.mapService.updateDriverPosition(
            currentPosition.lat,
            currentPosition.lng,
            `Début de livraison - ${this.state.currentOrder?.order_number}`,
        );

        return { apiResponse, orderDetails, currentPosition };
    }

    async setupRoute(orderDetails, currentPosition, speed, estimatedDurationMinutes) {
        if (!orderDetails?.delivery_address) {
            throw new Error("Adresse de livraison non trouvée");
        }

        const destination = this.extractDestinationCoords(orderDetails);
        if (!destination) {
            throw new Error("Coordonnées de destination invalides");
        }

        this.mapService.setDestination(destination.lat, destination.lng, {
            customer: orderDetails.customer?.full_name,
            address: orderDetails.delivery_address.name || orderDetails.delivery_address.address,
            phone: orderDetails.customer?.phone_number,
        });

        const routeData = await this.mapService.drawRoute(currentPosition.lat, currentPosition.lng, destination.lat, destination.lng);

        if (routeData?.geometry?.coordinates) {
            this.state.routeCoordinates = routeData.geometry.coordinates;
            
            // 🔧 CORRECTION CRITIQUE: Initialiser currentIndex basé sur la progression existante
            if (this.state.existingProgress && this.state.existingProgress > 0) {
                const totalSteps = this.state.routeCoordinates.length;
                this.state.currentIndex = Math.floor((this.state.existingProgress / 100) * totalSteps);
                console.log(`🎯 [TrackingService] Index initialisé à ${this.state.currentIndex}/${totalSteps} basé sur ${this.state.existingProgress}%`);
                
                // Nettoyer la variable temporaire
                delete this.state.existingProgress;
            }
            
            // Allow immediate route updates during simulation
            this.state.lastRouteUpdate = 0;
            
            this.startSimulation(speed, estimatedDurationMinutes);
        } else {
            throw new Error("Impossible de calculer la route");
        }
    }

    extractDestinationCoords(orderDetails) {
        const lat = parseFloat(orderDetails.delivery_address.latitude);
        const lng = parseFloat(orderDetails.delivery_address.longitude);
        return !isNaN(lat) && !isNaN(lng) ? { lat, lng } : null;
    }

    /**
     * Start route simulation with calculated timing
     * @param {number} speed - Simulation speed in km/h
     * @param {number} estimatedDurationMinutes - Total estimated duration
     */
    startSimulation(speed, estimatedDurationMinutes) {
        this._clearExistingSimulation();
        
        const interval = this._calculateSimulationInterval(estimatedDurationMinutes);
        
        console.log(`🚀 [TrackingService] Starting simulation: ${this.state.routeCoordinates.length} points, ${interval}ms interval`);
        
        this.intervals.simulation = setInterval(() => {
            this.executeSimulationStep(speed);
        }, interval);
    }

    /**
     * Clear existing simulation interval if running
     * @private
     */
    _clearExistingSimulation() {
        if (this.intervals.simulation) {
            clearInterval(this.intervals.simulation);
        }
    }

    /**
     * Calculate simulation step interval based on duration and route complexity
     * @private
     * @param {number} estimatedDurationMinutes - Total estimated duration
     * @returns {number} Interval in milliseconds
     */
    _calculateSimulationInterval(estimatedDurationMinutes) {
        const totalDurationMs = this.calculateSimulationDuration(estimatedDurationMinutes);
        return totalDurationMs / this.state.routeCoordinates.length;
    }

    /**
     * Calculate total simulation duration
     * @param {number} estimatedDurationMinutes - Estimated duration in minutes
     * @returns {number} Duration in milliseconds
     */
    calculateSimulationDuration(estimatedDurationMinutes) {
        if (estimatedDurationMinutes && estimatedDurationMinutes > 0) {
            return estimatedDurationMinutes * 60 * 1000;
        }
        return 30 * 1000;
    }

    executeSimulationStep(speed) {
        if (this.state.isPaused || !this.state.isTracking) {
            return;
        }

        if (this.state.currentIndex >= this.state.routeCoordinates.length) {
            // 🚫 NE PAS AUTO-COMPLÉTER ! Seulement arrêter la simulation
            console.log("🏁 Fin de parcours atteinte - simulation terminée (pas de livraison automatique)");
            this.stopTracking();
            return;
        }

        const coord = this.state.routeCoordinates[this.state.currentIndex];
        const [lng, lat] = coord;

        this.updateCurrentPosition(lat, lng, speed);

        this.emit("progressUpdate", {
            position: { lat, lng },
            speed: this.ui?.getTravelSpeed() || speed,
        });

        this.state.currentIndex++;
    }

    /**
     * Update current driver position and redraw route with throttling
     * @param {number} lat - Current latitude
     * @param {number} lng - Current longitude  
     * @param {number} speed - Current speed in km/h
     */
    updateCurrentPosition(lat, lng, speed) {
        this.mapService.updateDriverPosition(
            lat,
            lng,
            `En livraison - ${this.state.currentOrder?.order_number}`,
            this.ui?.getTravelSpeed() || speed,
        );
        
        this._updateRouteIfNeeded(lat, lng);
    }

    /**
     * Update route with throttling to prevent excessive API calls
     * @private
     * @param {number} lat - Current latitude
     * @param {number} lng - Current longitude
     */
    _updateRouteIfNeeded(lat, lng) {
        if (!this._shouldUpdateRoute()) return;

        const now = Date.now();
        const timeSinceLastUpdate = now - (this.state.lastRouteUpdate || 0);
        
        if (timeSinceLastUpdate < DeliveryTrackingService.ROUTE_UPDATE_THROTTLE_MS) {
            console.log(`⏳ [TrackingService] Route update throttled (${timeSinceLastUpdate}ms remaining)`);
            return;
        }

        this._performRouteUpdate(lat, lng, now);
    }

    /**
     * Check if route should be updated
     * @private
     * @returns {boolean} True if route update is needed
     */
    _shouldUpdateRoute() {
        return this.state.currentOrder && this.state.destination;
    }

    /**
     * Perform the actual route update
     * @private
     * @param {number} lat - Current latitude
     * @param {number} lng - Current longitude
     * @param {number} timestamp - Current timestamp
     */
    _performRouteUpdate(lat, lng, timestamp) {
        console.log('🗺️ [TrackingService] Updating route from current position to destination');
        this.state.lastRouteUpdate = timestamp;
        
        this.mapService.drawRoute(lat, lng, this.state.destination.lat, this.state.destination.lng)
            .then(() => console.log('✅ [TrackingService] Route successfully updated'))
            .catch(error => console.warn('⚠️ [TrackingService] Route update failed:', error.message));
    }

    /**
     * Start periodic position updates to server
     * Includes initial delay to avoid overwriting existing data
     */
    startPositionUpdates() {
        const INITIAL_DELAY_MS = 5000;
        
        setTimeout(async () => {
            // Send initial position update
            await this._sendPositionUpdateSafely();
            
            // Start regular position updates
            this.intervals.positionUpdate = setInterval(async () => {
                await this._sendPositionUpdateSafely();
            }, DeliveryTrackingService.POSITION_UPDATE_INTERVAL_MS);
        }, INITIAL_DELAY_MS);
        
        console.log("✅ [TrackingService] Position update scheduling started");
    }

    /**
     * Send position update with error handling
     * @private
     */
    async _sendPositionUpdateSafely() {
        try {
            await this.sendPositionUpdate();
        } catch (error) {
            console.error("❌ [TrackingService] Position update failed:", error.message);
        }
    }

    async sendPositionUpdate() {
        if (!this.state.isTracking || !this.state.currentOrder) return;

        const position = this.mapService.getCurrentPosition();
        if (position) {
            const currentSpeed = this.ui?.getTravelSpeed() || 40;
            
            // 🔧 CALCUL DE PROGRESSION côté client
            const progressData = this.calculateProgress(position);
            
            try {
                const orderId = this.state.currentOrder.order_id || this.state.currentOrder.id;
                
                // 🔧 ENVOYER les données calculées au serveur
                const response = await this.trackingApi.updatePosition(
                    orderId,
                    position,
                    currentSpeed,
                    progressData.progressPercentage,
                    progressData.distanceRemaining,
                    progressData.estimatedDuration
                );
                
                if (response && response._metadata?.success && response.data) {
                    const serverData = response.data;
                    
                    // 🔧 MISE À JOUR CRITIQUE: Mettre à jour currentOrder avec les nouvelles données
                    this.state.currentOrder.progress_percentage = serverData.progress_percentage || progressData.progressPercentage;
                    this.state.currentOrder.distance_remaining = serverData.distance_remaining || progressData.distanceRemaining;
                    this.state.currentOrder.estimated_duration = serverData.estimated_duration || progressData.estimatedDuration;
                    
                    this.emit("progressUpdate", {
                        progress: serverData.progress_percentage || progressData.progressPercentage,
                        position: position,
                        speed: currentSpeed,
                        remainingDistance: serverData.distance_remaining || progressData.distanceRemaining,
                        remainingTime: serverData.estimated_duration || progressData.estimatedDuration
                    });
                }
            } catch (error) {
                console.error("Position update failed:", error);
            }
        }
    }
    
    // 🔧 NOUVELLE MÉTHODE : Calculer la progression basée sur la position actuelle
    calculateProgress(currentPosition) {
        // 🔧 PENDANT LA SIMULATION ACTIVE : Calculer la progression basée sur currentIndex
        if (this.state.routeCoordinates && this.state.routeCoordinates.length > 0) {
            // Calculer la progression basée sur l'index actuel dans la route
            const totalSteps = this.state.routeCoordinates.length;
            const completedSteps = this.state.currentIndex || 0;
            const progressPercentage = Math.min(100, Math.round((completedSteps / totalSteps) * 100));
            
            // Calculer la distance restante approximative
            const remainingSteps = totalSteps - completedSteps;
            const totalRouteDistance = this.estimateRouteDistance();
            const distanceRemaining = (remainingSteps / totalSteps) * totalRouteDistance;
            
            // Calculer le temps estimé restant
            const currentSpeed = this.ui?.getTravelSpeed() || 40;
            const estimatedDuration = distanceRemaining > 0 ? Math.round((distanceRemaining / currentSpeed) * 60) : 0;
            
            console.log(`📊 Progression simulée: ${progressPercentage}% - ${distanceRemaining.toFixed(2)}km restants - ${estimatedDuration}min (index: ${completedSteps}/${totalSteps})`);
            
            return {
                progressPercentage,
                distanceRemaining: parseFloat(distanceRemaining.toFixed(2)),
                estimatedDuration
            };
        }
        
        // 🔧 FALLBACK : Utiliser données existantes si pas de simulation active
        if (this.state.currentOrder && this.state.currentOrder.progress_percentage !== null && this.state.currentOrder.progress_percentage !== undefined) {
            const existingProgress = parseFloat(this.state.currentOrder.progress_percentage);
            const existingDistance = parseFloat(this.state.currentOrder.distance_remaining || 0);
            const existingDuration = parseInt(this.state.currentOrder.estimated_duration || 0);
            
            console.log(`📊 Utilisation données existantes (pas de simulation): ${existingProgress}% - ${existingDistance}km restants - ${existingDuration}min`);
            
            return {
                progressPercentage: existingProgress,
                distanceRemaining: existingDistance,
                estimatedDuration: existingDuration
            };
        }
        
        // 🔧 FALLBACK FINAL : valeurs par défaut
        if (!this.state.routeCoordinates || this.state.routeCoordinates.length === 0) {
            return {
                progressPercentage: 0,
                distanceRemaining: null,
                estimatedDuration: null
            };
        }
    }
    
    // 🔧 NOUVELLE MÉTHODE : Estimer la distance totale de la route
    estimateRouteDistance() {
        if (!this.state.routeCoordinates || this.state.routeCoordinates.length < 2) {
            return 10; // Distance par défaut en km
        }
        
        // Calcul simple basé sur les coordonnées (approximation)
        let totalDistance = 0;
        for (let i = 1; i < this.state.routeCoordinates.length; i++) {
            const [lng1, lat1] = this.state.routeCoordinates[i - 1];
            const [lng2, lat2] = this.state.routeCoordinates[i];
            totalDistance += this.haversineDistance(lat1, lng1, lat2, lng2);
        }
        
        return totalDistance;
    }
    
    // 🔧 NOUVELLE MÉTHODE : Calcul de distance Haversine
    haversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Rayon de la Terre en km
        const dLat = this.toRadians(lat2 - lat1);
        const dLon = this.toRadians(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
    
    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }

    pauseTracking() {
        this.state.isPaused = true;
        this.emit("trackingPaused");
    }

    resumeTracking() {
        this.state.isPaused = false;
        this.emit("trackingResumed");
    }

    stopTracking() {
        this.resetState();
        this.emit("trackingStopped");
    }

    async completeTracking() {
        try {
            const orderId = this.state.currentOrder.order_id || this.state.currentOrder.id;
            await this.trackingApi.completeTracking(orderId);
        } catch (error) {
            console.error("Error completing tracking:", error);
        }

        this.emit("trackingCompleted", { order: this.state.currentOrder });

        setTimeout(() => {
            this.resetState();
        }, 2000);
    }

    resetState() {
        this.state.isTracking = false;
        this.state.isPaused = false;
        this.state.currentOrder = null;
        this.state.routeCoordinates = [];
        this.state.currentIndex = 0;
        this.state.lastRouteUpdate = 0;

        Object.values(this.intervals).forEach((interval) => {
            if (interval) clearInterval(interval);
        });

        this.intervals.simulation = null;
        this.intervals.positionUpdate = null;
    }

    getTransportMode() {
        return "driving";
    }

    getTrackingState() {
        return {
            ...this.state,
            progress: 0,
        };
    }
}
