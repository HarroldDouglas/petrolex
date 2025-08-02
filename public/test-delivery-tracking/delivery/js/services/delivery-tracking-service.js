// Service pour la simulation de livraison en temps réel
class DeliveryTrackingService {
    constructor(apiService, mapService, ui) {
        this.apiService = apiService;
        this.trackingApi = new DeliveryTrackingApiService(apiService);
        this.mapService = mapService;
        this.ui = ui;

        // État de tracking
        this.state = {
            isTracking: false,
            isPaused: false,
            currentOrder: null,
            routeCoordinates: [],
            currentIndex: 0,
        };

        // Intervalles
        this.intervals = {
            simulation: null,
            positionUpdate: null,
        };

        // Callbacks
        this.callbacks = {};
    }

    // Gestion des événements
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

    // Démarrage du tracking
    async startTracking(
        orderNumber,
        speed = CONFIG.SIMULATION.DEFAULT_SPEED,
        estimatedDurationMinutes = null,
    ) {
        if (this.state.isTracking) {
            throw new Error("Tracking already in progress");
        }

        try {
            const trackingData = await this.initializeTracking(
                orderNumber,
                estimatedDurationMinutes,
            );
            await this.setupRoute(
                trackingData.orderDetails,
                trackingData.currentPosition,
                speed,
                estimatedDurationMinutes,
            );

            this.startPositionUpdates();
            this.emit("trackingStarted", trackingData);

            return trackingData.apiResponse;
        } catch (error) {
            this.resetState();
            throw error;
        }
    }

    async initializeTracking(orderNumber, estimatedDurationMinutes) {
        const currentPosition = await this.mapService.getCurrentGPSPosition();
        const apiResponse = await this.trackingApi.startTracking(
            orderNumber,
            currentPosition,
        );

        if (!apiResponse._metadata?.success || !apiResponse.data) {
            throw new Error(
                apiResponse._metadata?.message ||
                    "Impossible de démarrer le tracking",
            );
        }

        this.state.currentOrder = apiResponse.data;
        this.state.isTracking = true;
        this.state.isPaused = false;
        this.state.currentIndex = 0;

        const orderDetails = this.apiService.getOrderFromCache(orderNumber);

        this.mapService.updateDriverPosition(
            currentPosition.lat,
            currentPosition.lng,
            `<strong>Début de livraison</strong><br>Commande: ${orderNumber}`,
        );

        return { apiResponse, orderDetails, currentPosition };
    }

    async setupRoute(
        orderDetails,
        currentPosition,
        speed,
        estimatedDurationMinutes,
    ) {
        if (!orderDetails?.delivery_address) {
            throw new Error("Adresse de livraison non trouvée");
        }

        const destination = this.extractDestinationCoords(orderDetails);
        if (!destination) {
            throw new Error("Coordonnées de destination invalides");
        }

        // Configurer la destination sur la carte
        this.mapService.setDestination(destination.lat, destination.lng, {
            customer: orderDetails.customer?.full_name,
            address:
                orderDetails.delivery_address.name ||
                orderDetails.delivery_address.address,
            phone: orderDetails.customer?.phone_number,
        });

        // Calculer et dessiner la route
        const routeData = await this.mapService.drawRoute(
            currentPosition,
            destination,
            this.getTransportMode(),
        );

        if (routeData?.geometry?.coordinates) {
            this.state.routeCoordinates = routeData.geometry.coordinates;
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

    // Simulation de mouvement
    startSimulation(speed, estimatedDurationMinutes) {
        if (this.intervals.simulation) {
            clearInterval(this.intervals.simulation);
        }

        const totalDurationMs = this.calculateSimulationDuration(
            estimatedDurationMinutes,
        );
        const interval = totalDurationMs / this.state.routeCoordinates.length;

        this.intervals.simulation = setInterval(() => {
            this.executeSimulationStep(speed);
        }, interval);
    }

    calculateSimulationDuration(estimatedDurationMinutes) {
        if (estimatedDurationMinutes && estimatedDurationMinutes > 0) {
            return estimatedDurationMinutes * 60 * 1000; // minutes → ms
        }
        return 30 * 1000; // 30 secondes par défaut
    }

    executeSimulationStep(speed) {
        if (this.state.isPaused || !this.state.isTracking) {
            return;
        }

        if (this.state.currentIndex >= this.state.routeCoordinates.length) {
            this.completeTracking();
            return;
        }

        const coord = this.state.routeCoordinates[this.state.currentIndex];
        const [lng, lat] = coord;

        // Mettre à jour la position
        this.updateCurrentPosition(lat, lng, speed);

        // Calculer et émettre la progression
        const progress =
            (this.state.currentIndex / this.state.routeCoordinates.length) *
            100;
        this.emit("progressUpdate", {
            progress: Math.round(progress),
            position: { lat, lng },
            index: this.state.currentIndex,
            total: this.state.routeCoordinates.length,
            speed: this.ui?.getTravelSpeed() || speed,
        });

        this.state.currentIndex++;
    }

    updateCurrentPosition(lat, lng, speed) {
        this.mapService.updateDriverPosition(
            lat,
            lng,
            `<strong>En livraison</strong><br>
             Commande: ${this.state.currentOrder?.order_number}<br>
             Position: ${lat.toFixed(4)}, ${lng.toFixed(4)}<br>
             Vitesse: ${this.ui?.getTravelSpeed() || speed} km/h`,
            this.ui?.getTravelSpeed() || speed,
        );
    }

    // Mises à jour de position
    startPositionUpdates() {
        this.intervals.positionUpdate = setInterval(async () => {
            await this.sendPositionUpdate();
        }, CONFIG.SIMULATION.POSITION_UPDATE_INTERVAL);
    }

    async sendPositionUpdate() {
        if (!this.state.isTracking || !this.state.currentOrder) return;

        const position = this.mapService.getCurrentPosition();
        if (position) {
            const currentSpeed = this.ui?.getTravelSpeed() || 40;
            try {
                await this.trackingApi.updatePosition(
                    this.state.currentOrder.order_number,
                    position,
                    currentSpeed,
                );
            } catch (error) {
                console.error("Position update failed:", error);
            }
        }
    }

    // Contrôles
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
            await this.trackingApi.completeTracking(
                this.state.currentOrder.order_number,
            );
        } catch (error) {
            console.error("Error completing tracking:", error);
        }

        this.emit("trackingCompleted", { order: this.state.currentOrder });

        setTimeout(() => {
            this.resetState();
        }, 2000);
    }

    // Utilitaires
    resetState() {
        this.state.isTracking = false;
        this.state.isPaused = false;
        this.state.currentOrder = null;
        this.state.routeCoordinates = [];
        this.state.currentIndex = 0;

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
        const progress =
            this.state.routeCoordinates.length > 0
                ? Math.round(
                      (this.state.currentIndex /
                          this.state.routeCoordinates.length) *
                          100,
                  )
                : 0;

        return {
            ...this.state,
            progress,
        };
    }
}
