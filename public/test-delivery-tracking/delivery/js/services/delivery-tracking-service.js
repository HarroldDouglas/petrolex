class DeliveryTrackingService {
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
                
                if (trackingData.driver_lat && trackingData.driver_lng) {
                    currentPosition = {
                        lat: parseFloat(trackingData.driver_lat),
                        lng: parseFloat(trackingData.driver_lng)
                    };
                } else {
                    currentPosition = await this.mapService.getCurrentGPSPosition();
                }
                
                const apiResponse = {
                    _metadata: { success: true },
                    data: selectedOrder.trackingData
                };
                
                this.state.currentOrder = apiResponse.data;
                this.state.isTracking = true;
                this.state.isPaused = false;
                
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
        
        currentPosition = await this.mapService.getCurrentGPSPosition();
        const apiResponse = await this.trackingApi.startTracking(orderIdentifier, currentPosition);

        if (!apiResponse._metadata?.success || !apiResponse.data) {
            throw new Error(apiResponse._metadata?.message || "Impossible de démarrer le tracking");
        }

        this.state.currentOrder = apiResponse.data;
        this.state.isTracking = true;
        this.state.isPaused = false;

        let orderDetails;
        
        if (this.state.currentOrder.destination_lat && this.state.currentOrder.destination_lng) {
            orderDetails = {
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
        } else {
            const orderNumber = this.state.currentOrder.order_number;
            orderDetails = this.apiService.getOrderFromCache(orderNumber);
            
            if (!orderDetails) {
                const orderId = this.state.currentOrder.order_id;
                if (orderId) {
                    const convertedOrderNumber = this.apiService.getOrderNumberFromId(orderId);
                    if (convertedOrderNumber) {
                        orderDetails = this.apiService.getOrderFromCache(convertedOrderNumber);
                    }
                }
            }
        }
        
        if (!orderDetails || !orderDetails.delivery_address) {
            throw new Error("Détails de commande introuvables. Veuillez recharger.");
        }

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

        const routeData = await this.mapService.drawRoute(currentPosition, destination, this.getTransportMode());

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

    startSimulation(speed, estimatedDurationMinutes) {
        if (this.intervals.simulation) {
            clearInterval(this.intervals.simulation);
        }

        const totalDurationMs = this.calculateSimulationDuration(estimatedDurationMinutes);
        const interval = totalDurationMs / this.state.routeCoordinates.length;

        this.intervals.simulation = setInterval(() => {
            this.executeSimulationStep(speed);
        }, interval);
    }

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
            this.completeTracking();
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

    updateCurrentPosition(lat, lng, speed) {
        this.mapService.updateDriverPosition(
            lat,
            lng,
            `En livraison - ${this.state.currentOrder?.order_number}`,
            this.ui?.getTravelSpeed() || speed,
        );
    }

    startPositionUpdates() {
        this.intervals.positionUpdate = setInterval(async () => {
            await this.sendPositionUpdate();
        }, DELIVERY_CONFIG.SIMULATION.POSITION_UPDATE_INTERVAL);
    }

    async sendPositionUpdate() {
        if (!this.state.isTracking || !this.state.currentOrder) return;

        const position = this.mapService.getCurrentPosition();
        if (position) {
            const currentSpeed = this.ui?.getTravelSpeed() || 40;
            
            try {
                const orderId = this.state.currentOrder.order_id || this.state.currentOrder.id;
                
                const response = await this.trackingApi.updatePosition(
                    orderId,
                    position,
                    currentSpeed
                );
                
                if (response && response._metadata?.success && response.data) {
                    const serverData = response.data;
                    this.emit("progressUpdate", {
                        progress: serverData.progress_percentage || 0,
                        position: position,
                        speed: currentSpeed,
                        remainingDistance: serverData.distance_remaining,
                        remainingTime: serverData.estimated_duration
                    });
                }
            } catch (error) {
                console.error("Position update failed:", error);
            }
        }
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
