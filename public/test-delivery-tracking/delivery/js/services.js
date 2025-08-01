// Service pour les appels API avec authentification
class DeliveryPersonApiService {
    constructor() {
        this.baseUrl = CONFIG.API.BASE_URL;
        this.token = localStorage.getItem('delivery_person_token');
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('delivery_person_token', token);
    }

    clearToken() {
        this.token = null;
        localStorage.removeItem('delivery_person_token');
    }

    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        if (this.token) {
            headers.Authorization = `Bearer ${this.token}`;
        }
        
        return headers;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const defaultOptions = {
            headers: this.getHeaders()
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            
            if (response.status === 401) {
                this.clearToken();
                throw new Error('Session expirée. Veuillez vous reconnecter.');
            }
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Authentification
    async login(email, password) {
        const endpoint = CONFIG.API.ENDPOINTS.LOGIN;
        const response = await this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify({
                login: email,
                password: password
            })
        });
        
        console.log('Login response:', response); // Debug
        
        // Corriger la vérification pour utiliser _metadata.success
        if (response._metadata?.success && response.data) {
            this.setToken(response.data.access_token);
            return response.data;
        }
        
        throw new Error(response._metadata?.message || 'Erreur de connexion');
    }

    // Récupérer les commandes du livreur
    async getOrders(deliveryPersonId, filters = {}, page = 1) {
        let endpoint = CONFIG.API.ENDPOINTS.DELIVERY_PERSON_ORDERS.replace('{id}', deliveryPersonId);
        
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters
        });
        
        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    // Démarrer le tracking d'une commande
    async startTracking(orderNumber, position) {
        const orderId = this.getOrderIdFromCache(orderNumber);
        
        if (!orderId) {
            throw new Error('Impossible de récupérer l\'ID de la commande. Rechargez les commandes.');
        }
        
        try {
            // Créer le tracking initial avec l'order_id
            const createResponse = await this.request('/tracking/delivery', {
                method: 'POST',
                body: JSON.stringify({
                    order_id: orderId,
                    driver_lat: position.lat,
                    driver_lng: position.lng,
                    timestamp: new Date().toISOString()
                })
            });
            
            return createResponse;
        } catch (error) {
            // Si le tracking existe déjà, essayer de le démarrer directement avec l'orderId
            const startResponse = await this.request(`/tracking/delivery/${orderId}/start`, {
                method: 'POST',
                body: JSON.stringify({
                    driver_lat: position.lat,
                    driver_lng: position.lng,
                    timestamp: new Date().toISOString()
                })
            });
            
            return startResponse;
        }
    }
    
    // Nouvelle méthode pour récupérer l'order_id depuis le cache
    getOrderIdFromCache(orderNumber) {
        const cachedOrders = JSON.parse(localStorage.getItem('cached_orders') || '[]');
        const order = cachedOrders.find(o => o.order_number === orderNumber);
        return order ? order.id : null;
    }
    
    // Mettre en cache les order_id quand on charge les commandes
    cacheOrderIds(orders) {
        localStorage.setItem('cached_orders', JSON.stringify(orders));
    }

    // Mettre à jour la position
    async updatePosition(orderNumber, position) {
        const orderId = this.getOrderIdFromCache(orderNumber);
        
        if (!orderId) {
            console.warn('Order ID not found for position update:', orderNumber);
            return;
        }
        
        return this.request(`/tracking/delivery/${orderId}/position`, {
            method: 'PATCH',
            body: JSON.stringify({
                driver_lat: position.lat,
                driver_lng: position.lng,
                timestamp: new Date().toISOString()
            })
        });
    }

    // Récupérer les détails du tracking
    async getTrackingDetails(orderNumber) {
        const orderId = this.getOrderIdFromCache(orderNumber);
        
        if (!orderId) {
            throw new Error('Order ID not found for tracking details');
        }
        
        return this.request(`/tracking/delivery/${orderId}`);
    }
}

// Service pour la gestion de la carte Mapbox du livreur
class DeliveryPersonMapService {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.destinationMarker = null;
        this.routeLayer = null;
        this.initialized = false;
        this.currentPosition = null;
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
            console.log('Delivery person map initialized successfully');
        });

        this.map.on('error', (e) => {
            console.error('Map error:', e);
        });

        return this.map;
    }

    updateDriverPosition(lat, lng, popupContent = null) {
        if (this.driverMarker) {
            this.driverMarker.remove();
        }
        
        const popup = popupContent ? new mapboxgl.Popup().setHTML(popupContent) : null;
        
        this.driverMarker = new mapboxgl.Marker({ color: '#1E88E5' })
            .setLngLat([lng, lat]);
            
        if (popup) {
            this.driverMarker.setPopup(popup);
        }
        
        this.driverMarker.addTo(this.map);
        this.currentPosition = { lat, lng };
        
        // Center map on driver position
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
                ${info.address || 'Adresse de livraison'}<br>
                ${info.phone ? `📞 ${info.phone}` : ''}
            `))
            .addTo(this.map);
    }

    async drawRoute(startCoords, endCoords, transportMode = 'driving') {
        const sourceId = 'delivery-route';
        const layerId = 'delivery-route-layer';

        try {
            // Supprimer l'ancienne route
            if (this.map.getLayer(layerId)) {
                this.map.removeLayer(layerId);
            }
            if (this.map.getSource(sourceId)) {
                this.map.removeSource(sourceId);
            }

            // Calculer la route avec Mapbox Directions
            const profile = CONFIG.SIMULATION.TRANSPORT_MODES[transportMode]?.mapboxProfile || 'driving';
            const query = await fetch(
                `https://api.mapbox.com/directions/v5/mapbox/${profile}/${startCoords.lng},${startCoords.lat};${endCoords.lng},${endCoords.lat}?geometries=geojson&access_token=${CONFIG.MAPBOX.ACCESS_TOKEN}`
            );
            const result = await query.json();
            
            if (result.routes && result.routes.length > 0) {
                const route = result.routes[0];
                
                // Ajouter la nouvelle route
                this.map.addSource(sourceId, {
                    type: 'geojson',
                    data: {
                        type: 'Feature',
                        properties: {},
                        geometry: route.geometry
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
                
                // Ajuster la vue pour afficher toute la route
                this.fitBounds(route.geometry.coordinates);
                
                return {
                    duration: Math.round(route.duration / 60), // en minutes
                    distance: (route.distance / 1000).toFixed(1), // en km
                    geometry: route.geometry
                };
            }
        } catch (error) {
            console.error('Error drawing route:', error);
            return null;
        }
    }

    fitBounds(coordinates) {
        if (coordinates && coordinates.length > 0) {
            const bounds = new mapboxgl.LngLatBounds();
            coordinates.forEach(coord => bounds.extend(coord));
            this.map.fitBounds(bounds, { padding: 50 });
        }
    }

    getCurrentPosition() {
        return this.currentPosition;
    }

    // Simuler le mouvement GPS
    async getCurrentGPSPosition() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    },
                    (error) => {
                        console.warn('GPS error:', error);
                        // Utiliser position par défaut de Paris
                        resolve({
                            lat: CONFIG.MAPBOX.DEFAULT_CENTER[1],
                            lng: CONFIG.MAPBOX.DEFAULT_CENTER[0]
                        });
                    }
                );
            } else {
                resolve({
                    lat: CONFIG.MAPBOX.DEFAULT_CENTER[1],
                    lng: CONFIG.MAPBOX.DEFAULT_CENTER[0]
                });
            }
        });
    }

    destroy() {
        if (this.driverMarker) this.driverMarker.remove();
        if (this.destinationMarker) this.destinationMarker.remove();
        if (this.map) this.map.remove();
        this.initialized = false;
    }
}

// Service pour la simulation de livraison réelle
class DeliveryTrackingService {
    constructor(apiService, mapService) {
        this.apiService = apiService;
        this.mapService = mapService;
        this.isTracking = false;
        this.isPaused = false;
        this.routeCoordinates = [];
        this.currentIndex = 0;
        this.intervalId = null;
        this.positionUpdateIntervalId = null;
        this.currentOrder = null;
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

    async startTracking(orderNumber, speed = CONFIG.SIMULATION.DEFAULT_SPEED) {
        if (this.isTracking) {
            throw new Error('Tracking already in progress');
        }

        try {
            // Récupérer la position GPS actuelle
            const currentPosition = await this.mapService.getCurrentGPSPosition();
            
            // Démarrer le tracking via l'API
            const response = await this.apiService.startTracking(orderNumber, currentPosition);
            
            // Correction: utiliser _metadata.success au lieu de response.success
            if (response._metadata?.success && response.data) {
                this.currentOrder = response.data;
                this.isTracking = true;
                this.isPaused = false;
                this.currentIndex = 0;

                // Récupérer les détails de la commande pour avoir la destination
                const orderDetails = this.getOrderDetailsFromCache(orderNumber);
                
                // Mettre à jour la carte avec la position de départ
                this.mapService.updateDriverPosition(
                    currentPosition.lat, 
                    currentPosition.lng,
                    `<strong>Position de départ</strong><br>Livraison ${orderNumber}`
                );

                // Définir la destination si on a les coordonnées
                if (orderDetails && orderDetails.delivery_address) {
                    const destLat = parseFloat(orderDetails.delivery_address.latitude);
                    const destLng = parseFloat(orderDetails.delivery_address.longitude);
                    
                    if (!isNaN(destLat) && !isNaN(destLng)) {
                        this.mapService.setDestination(destLat, destLng, {
                            customer: orderDetails.customer?.full_name,
                            address: orderDetails.delivery_address.name || orderDetails.delivery_address.address,
                            phone: orderDetails.customer?.phone_number
                        });

                        // Calculer et dessiner la route
                        const routeData = await this.mapService.drawRoute(
                            currentPosition, 
                            { lat: destLat, lng: destLng }, 
                            this.getTransportMode()
                        );

                        if (routeData) {
                            // Utiliser les coordonnées de la route calculée par Mapbox
                            this.routeCoordinates = routeData.geometry.coordinates;
                            
                            // Mettre à jour les estimations
                            this.triggerCallback('routeCalculated', {
                                duration: routeData.duration,
                                distance: routeData.distance,
                                coordinates: this.routeCoordinates
                            });

                            // Démarrer la simulation de mouvement
                            this.runSimulation(speed);
                        }
                    }
                }
                
                // Démarrer les mises à jour de position régulières
                this.startPositionUpdates();
                
                this.triggerCallback('trackingStarted', {
                    order: this.currentOrder,
                    route: this.routeCoordinates,
                    position: currentPosition
                });

                return response;
            } else {
                throw new Error(response._metadata?.message || 'Impossible de démarrer le tracking');
            }
        } catch (error) {
            console.error('Error starting tracking:', error);
            throw error;
        }
    }

    getOrderDetailsFromCache(orderNumber) {
        const cachedOrders = JSON.parse(localStorage.getItem('cached_orders') || '[]');
        return cachedOrders.find(o => o.order_number === orderNumber);
    }

    getTransportMode() {
        // Récupérer le mode de transport sélectionné dans l'UI
        return document.querySelector('input[name="transportMode"]:checked')?.value || 'driving';
    }

    runSimulation(speed) {
        if (!this.routeCoordinates || this.routeCoordinates.length === 0) {
            console.warn('No route coordinates available for simulation');
            return;
        }

        const interval = CONFIG.SIMULATION.BASE_INTERVAL / speed;
        
        this.intervalId = setInterval(() => {
            if (this.isPaused || !this.isTracking) {
                return;
            }

            if (this.currentIndex < this.routeCoordinates.length) {
                const coord = this.routeCoordinates[this.currentIndex];
                const lng = coord[0];
                const lat = coord[1];
                
                // Mettre à jour la position sur la carte
                this.mapService.updateDriverPosition(
                    lat, 
                    lng,
                    `<strong>En livraison</strong><br>${this.currentOrder?.order_number}<br>Position: ${lat.toFixed(4)}, ${lng.toFixed(4)}`
                );
                
                // Calculer et déclencher les callbacks de progression
                const progress = (this.currentIndex / this.routeCoordinates.length) * 100;
                this.triggerCallback('progressUpdate', {
                    progress: progress,
                    position: { lat, lng },
                    index: this.currentIndex,
                    total: this.routeCoordinates.length
                });
                
                // Avancer selon la vitesse (comme dans livreur.html)
                this.currentIndex += Math.max(1, Math.floor(speed / CONFIG.SIMULATION.ROUTE_STEP_MULTIPLIER));
                
                // Vérifier si la simulation est terminée
                if (this.currentIndex >= this.routeCoordinates.length) {
                    this.completeTracking();
                }
            }
        }, interval);
    }

    startPositionUpdates() {
        // Envoyer la position au serveur toutes les 10 secondes
        this.positionUpdateIntervalId = setInterval(async () => {
            if (!this.isTracking || !this.currentOrder) return;

            const position = this.mapService.getCurrentPosition();
            if (position) {
                try {
                    await this.apiService.updatePosition(this.currentOrder.order_number, position);
                } catch (error) {
                    console.error('Error updating position:', error);
                }
            }
        }, CONFIG.SIMULATION.POSITION_UPDATE_INTERVAL);
    }

    pauseTracking() {
        this.isPaused = true;
        this.triggerCallback('trackingPaused');
    }

    resumeTracking() {
        this.isPaused = false;
        this.triggerCallback('trackingResumed');
    }

    stopTracking() {
        this.isTracking = false;
        this.isPaused = false;
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        if (this.positionUpdateIntervalId) {
            clearInterval(this.positionUpdateIntervalId);
            this.positionUpdateIntervalId = null;
        }
        
        this.currentIndex = 0;
        this.routeCoordinates = [];
        this.currentOrder = null;
        
        this.triggerCallback('trackingStopped');
    }

    async completeTracking() {
        this.isTracking = false;
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        if (this.positionUpdateIntervalId) {
            clearInterval(this.positionUpdateIntervalId);
            this.positionUpdateIntervalId = null;
        }
        
        this.triggerCallback('trackingCompleted', {
            order: this.currentOrder
        });
        
        // Nettoyer après un délai
        setTimeout(() => {
            this.currentOrder = null;
            this.routeCoordinates = [];
            this.currentIndex = 0;
        }, 2000);
    }

    getTrackingState() {
        return {
            isTracking: this.isTracking,
            isPaused: this.isPaused,
            progress: this.routeCoordinates.length > 0 ? 
                (this.currentIndex / this.routeCoordinates.length) * 100 : 0,
            currentOrder: this.currentOrder
        };
    }
}