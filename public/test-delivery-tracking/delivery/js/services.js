// Service pour les appels API spécifiques au livreur
class DeliveryApiService {
    constructor() {
        this.baseUrl = CONFIG.API.BASE_URL;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    async getActiveDeliveries() {
        return this.request(CONFIG.API.ENDPOINTS.DELIVERY_ACTIVE);
    }

    async getDelivery(orderNumber) {
        const endpoint = CONFIG.API.ENDPOINTS.DELIVERY_GET.replace('{id}', orderNumber);
        return this.request(endpoint);
    }

    async createDelivery(deliveryData) {
        return this.request(CONFIG.API.ENDPOINTS.DELIVERY_CREATE, {
            method: 'POST',
            body: JSON.stringify(deliveryData)
        });
    }

    async startDelivery(orderNumber, driverPosition) {
        const endpoint = CONFIG.API.ENDPOINTS.DELIVERY_START.replace('{id}', orderNumber);
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(driverPosition)
        });
    }

    async updatePosition(orderNumber, position) {
        const endpoint = CONFIG.API.ENDPOINTS.DELIVERY_POSITION.replace('{id}', orderNumber);
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(position)
        });
    }

    async updateStatus(orderNumber, status) {
        const endpoint = CONFIG.API.ENDPOINTS.DELIVERY_STATUS.replace('{id}', orderNumber);
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify({ status })
        });
    }
}

// Service pour la gestion de la carte Mapbox du livreur
class DeliveryMapService {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.destinationMarker = null;
        this.routeLayer = null;
        this.initialized = false;
    }

    initialize(containerId) {
        if (this.initialized) {
            console.warn('Map already initialized');
            return;
        }

        mapboxgl.accessToken = CONFIG.MAPBOX.ACCESS_TOKEN;
        
        this.map = new mapboxgl.Map({
            container: containerId,
            style: CONFIG.MAPBOX.STYLE,
            center: CONFIG.MAPBOX.DEFAULT_CENTER,
            zoom: CONFIG.MAPBOX.DEFAULT_ZOOM
        });

        this.map.on('load', () => {
            this.initialized = true;
            console.log('Delivery map initialized successfully');
        });

        this.map.on('click', (e) => {
            this.handleMapClick(e);
        });

        return this.map;
    }

    handleMapClick(e) {
        // Permettre de définir la position du livreur en cliquant sur la carte
        if (window.deliveryApp && typeof window.deliveryApp.handleMapClick === 'function') {
            window.deliveryApp.handleMapClick(e.lngLat.lat, e.lngLat.lng);
        }
    }

    updateDriverPosition(lat, lng) {
        if (this.driverMarker) {
            this.driverMarker.remove();
        }
        
        this.driverMarker = new mapboxgl.Marker({ color: '#1E88E5' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Livreur</strong><br>
                Position actuelle<br>
                ${lat.toFixed(4)}, ${lng.toFixed(4)}
            `))
            .addTo(this.map);

        this.map.setCenter([lng, lat]);
    }

    setDestination(lat, lng, info = {}) {
        if (this.destinationMarker) {
            this.destinationMarker.remove();
        }
        
        this.destinationMarker = new mapboxgl.Marker({ color: '#E53935' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Destination</strong><br>
                ${info.customer || 'Client'}<br>
                ${info.address || 'Adresse de livraison'}
            `))
            .addTo(this.map);
    }

    async drawRoute(coordinates) {
        const sourceId = 'driver-route';
        const layerId = 'driver-route-layer';

        // Supprimer l'ancienne route
        if (this.map.getLayer(layerId)) {
            this.map.removeLayer(layerId);
        }
        if (this.map.getSource(sourceId)) {
            this.map.removeSource(sourceId);
        }

        // Ajouter la nouvelle route
        this.map.addSource(sourceId, {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            }
        });

        this.map.addLayer({
            id: layerId,
            type: 'line',
            source: sourceId,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': '#1E88E5',
                'line-width': 4,
                'line-opacity': 0.8
            }
        });

        this.routeLayer = layerId;
    }

    centerOnBounds(coordinates) {
        if (coordinates && coordinates.length > 0) {
            const bounds = new mapboxgl.LngLatBounds();
            coordinates.forEach(coord => bounds.extend(coord));
            this.map.fitBounds(bounds, { padding: 50 });
        }
    }

    destroy() {
        if (this.driverMarker) this.driverMarker.remove();
        if (this.destinationMarker) this.destinationMarker.remove();
        if (this.map) this.map.remove();
        this.initialized = false;
    }
}

// Service pour la simulation de livraison
class SimulationService {
    constructor(apiService, mapService) {
        this.apiService = apiService;
        this.mapService = mapService;
        this.isRunning = false;
        this.isPaused = false;
        this.routeCoordinates = [];
        this.currentIndex = 0;
        this.intervalId = null;
        this.currentDelivery = null;
        this.callbacks = {};
    }

    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    triggerCallback(event, data = null) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => callback(data));
        }
    }

    async startSimulation(orderNumber, speed = CONFIG.SIMULATION.DEFAULT_SPEED) {
        if (this.isRunning) {
            throw new Error('Simulation already running');
        }

        try {
            // Récupérer le mode de transport sélectionné
            const transportMode = window.deliveryApp?.ui?.getSelectedTransportMode() || 'walking';
            const modeConfig = CONFIG.SIMULATION.TRANSPORT_MODES[transportMode];
            
            // Ajuster la vitesse selon le mode de transport
            const adjustedSpeed = speed * modeConfig.speedMultiplier;
            
            // Démarrer la livraison via l'API
            const driverPosition = this.getCurrentDriverPosition();
            const response = await this.apiService.startDelivery(orderNumber, driverPosition);
            
            if (response.success && response.route) {
                this.routeCoordinates = response.route.geometry.coordinates;
                this.currentIndex = 0;
                this.currentDelivery = response.delivery;
                this.isRunning = true;
                this.isPaused = false;
                this.transportMode = transportMode;
                this.adjustedSpeed = adjustedSpeed;

                // Dessiner la route sur la carte
                await this.mapService.drawRoute(this.routeCoordinates);
                
                // Ajouter le marqueur de destination
                this.mapService.setDestination(
                    this.currentDelivery.destination_lat,
                    this.currentDelivery.destination_lng,
                    {
                        customer: this.currentDelivery.customer_name,
                        address: this.currentDelivery.destination_address
                    }
                );

                // Démarrer l'animation avec la vitesse ajustée
                this.runAnimation(adjustedSpeed);
                
                this.triggerCallback('simulationStarted', {
                    delivery: this.currentDelivery,
                    route: this.routeCoordinates,
                    transportMode: this.transportMode
                });

                return response;
            } else {
                throw new Error('Failed to start delivery');
            }
        } catch (error) {
            console.error('Error starting simulation:', error);
            throw error;
        }
    }

    runAnimation(speed) {
        const interval = CONFIG.SIMULATION.BASE_INTERVAL / speed;
        
        this.intervalId = setInterval(() => {
            if (this.isPaused || !this.isRunning) {
                return;
            }

            if (this.currentIndex < this.routeCoordinates.length) {
                const coord = this.routeCoordinates[this.currentIndex];
                const lng = coord[0];
                const lat = coord[1];
                
                // Mettre à jour la position sur la carte
                this.mapService.updateDriverPosition(lat, lng);
                
                // Mettre à jour via l'API
                this.updatePositionAPI(lat, lng);
                
                // Calculer et déclencher les callbacks de progression
                const progress = (this.currentIndex / this.routeCoordinates.length) * 100;
                this.triggerCallback('progressUpdate', {
                    progress: progress,
                    position: { lat, lng },
                    index: this.currentIndex,
                    total: this.routeCoordinates.length
                });
                
                // Avancer selon la vitesse
                this.currentIndex += Math.max(1, Math.floor(speed / CONFIG.SIMULATION.ROUTE_STEP_MULTIPLIER));
                
                // Vérifier si la simulation est terminée
                if (this.currentIndex >= this.routeCoordinates.length) {
                    this.completeSimulation();
                }
            }
        }, interval);
    }

    async updatePositionAPI(lat, lng) {
        if (!this.currentDelivery) return;

        try {
            await this.apiService.updatePosition(this.currentDelivery.order_number, {
                driver_lat: lat,
                driver_lng: lng
            });
        } catch (error) {
            console.error('Error updating position:', error);
        }
    }

    pauseSimulation() {
        this.isPaused = true;
        this.triggerCallback('simulationPaused');
    }

    resumeSimulation() {
        this.isPaused = false;
        this.triggerCallback('simulationResumed');
    }

    stopSimulation() {
        this.isRunning = false;
        this.isPaused = false;
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        this.currentIndex = 0;
        this.routeCoordinates = [];
        this.currentDelivery = null;
        
        this.triggerCallback('simulationStopped');
    }

    async completeSimulation() {
        this.isRunning = false;
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }

        // Marquer la livraison comme terminée
        if (this.currentDelivery) {
            try {
                await this.apiService.updateStatus(this.currentDelivery.order_number, 'delivered');
            } catch (error) {
                console.error('Error updating delivery status:', error);
            }
        }
        
        this.triggerCallback('simulationCompleted', {
            delivery: this.currentDelivery
        });
        
        // Nettoyer après un délai
        setTimeout(() => {
            this.currentDelivery = null;
            this.routeCoordinates = [];
            this.currentIndex = 0;
        }, 2000);
    }

    getCurrentDriverPosition() {
        // Retourner la position actuelle du livreur avec tous les champs requis
        if (window.deliveryApp && window.deliveryApp.currentPosition) {
            return {
                driver_lat: window.deliveryApp.currentPosition.lat,
                driver_lng: window.deliveryApp.currentPosition.lng,
                driver_name: window.deliveryApp.driverInfo?.name || CONFIG.DRIVER.DEFAULT_NAME,
                driver_phone: window.deliveryApp.driverInfo?.phone || CONFIG.DRIVER.DEFAULT_PHONE
            };
        }
        return {
            driver_lat: CONFIG.DRIVER.DEFAULT_POSITION.lat,
            driver_lng: CONFIG.DRIVER.DEFAULT_POSITION.lng,
            driver_name: CONFIG.DRIVER.DEFAULT_NAME,
            driver_phone: CONFIG.DRIVER.DEFAULT_PHONE
        };
    }

    getSimulationState() {
        return {
            isRunning: this.isRunning,
            isPaused: this.isPaused,
            progress: this.routeCoordinates.length > 0 ? 
                (this.currentIndex / this.routeCoordinates.length) * 100 : 0,
            currentDelivery: this.currentDelivery
        };
    }
}