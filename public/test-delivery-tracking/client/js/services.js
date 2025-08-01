// Service pour les appels API
class ApiService {
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
            return (await response.json()).data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    async getActiveDeliveries() {
        return this.request(CONFIG.API.ENDPOINTS.DELIVERY_ACTIVE);
    }

    async getDelivery(orderNumber) {
        return this.request(`${CONFIG.API.ENDPOINTS.DELIVERY_GET}/${orderNumber}`);
    }

    async createDelivery(deliveryData) {
        return this.request(CONFIG.API.ENDPOINTS.DELIVERY_CREATE, {
            method: 'POST',
            body: JSON.stringify(deliveryData)
        });
    }
}

// Service pour la gestion de la carte Mapbox
class MapService {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.destinationMarker = null;
        this.routeSource = null;
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
            this.addRouteSource();
            this.initialized = true;
            console.log('Map initialized successfully');
        });

        this.map.on('error', (e) => {
            console.error('Map error:', e);
        });

        return this.map;
    }

    addRouteSource() {
        if (!this.map.getSource('route')) {
            this.map.addSource('route', {
                'type': 'geojson',
                'data': {
                    'type': 'Feature',
                    'properties': {},
                    'geometry': {
                        'type': 'LineString',
                        'coordinates': []
                    }
                }
            });

            this.map.addLayer({
                'id': 'route',
                'type': 'line',
                'source': 'route',
                'layout': {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                'paint': {
                    'line-color': '#3887be',
                    'line-width': 5,
                    'line-opacity': 0.75
                }
            });
        }
    }

    updateDriverPosition(lat, lng, driverInfo = {}) {
        if (this.driverMarker) {
            this.driverMarker.remove();
        }
        
        this.driverMarker = new mapboxgl.Marker({ color: 'blue' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Livreur</strong><br>
                ${driverInfo.name || 'En cours...'}<br>
                Position actuelle
            `))
            .addTo(this.map);
    }

    setDestination(lat, lng, customerInfo = {}) {
        if (this.destinationMarker) {
            this.destinationMarker.remove();
        }
        
        this.destinationMarker = new mapboxgl.Marker({ color: 'green' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Destination</strong><br>
                ${customerInfo.name || 'Client'}<br>
                ${customerInfo.address || 'Adresse de livraison'}
            `))
            .addTo(this.map);
    }

    async drawRoute(start, end) {
        try {
            const query = await fetch(
                `https://api.mapbox.com/directions/v5/mapbox/driving/${start[0]},${start[1]};${end[0]},${end[1]}?steps=true&geometries=geojson&access_token=${CONFIG.MAPBOX.ACCESS_TOKEN}`
            );
            const json = await query.json();
            
            if (json.routes && json.routes.length > 0) {
                const route = json.routes[0];
                
                // Vérifier que la source existe avant de l'utiliser
                if (this.map.getSource('route')) {
                    this.map.getSource('route').setData({
                        'type': 'Feature',
                        'properties': {},
                        'geometry': route.geometry
                    });
                } else {
                    console.warn('Route source not found, recreating...');
                    this.addRouteSource();
                    
                    // Essayer à nouveau après création
                    if (this.map.getSource('route')) {
                        this.map.getSource('route').setData({
                            'type': 'Feature',
                            'properties': {},
                            'geometry': route.geometry
                        });
                    }
                }

                return {
                    duration: Math.round(route.duration / 60), // en minutes
                    distance: (route.distance / 1000).toFixed(1) // en km
                };
            }
        } catch (error) {
            console.error('Error getting route:', error);
            return null;
        }
    }

    centerOnLocation(lat, lng, zoom = 14) {
        if (this.map) {
            this.map.flyTo({
                center: [lng, lat],
                zoom: zoom,
                duration: CONFIG.UI.ANIMATION_DURATION
            });
        }
    }

    destroy() {
        if (this.driverMarker) this.driverMarker.remove();
        if (this.destinationMarker) this.destinationMarker.remove();
        if (this.map) this.map.remove();
        this.initialized = false;
    }
}

// Service pour la gestion WebSocket (Laravel Reverb)
class WebSocketService {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.connected = false;
        this.callbacks = {};
    }

    connect() {
        try {
            // Configuration pour Laravel Reverb
            this.pusher = new Pusher(CONFIG.WEBSOCKET.PUSHER_APP_KEY, {
                wsHost: CONFIG.WEBSOCKET.PUSHER_HOST,
                wsPort: CONFIG.WEBSOCKET.PUSHER_PORT,
                wssPort: CONFIG.WEBSOCKET.PUSHER_PORT,
                forceTLS: CONFIG.WEBSOCKET.PUSHER_FORCE_TLS,
                enabledTransports: CONFIG.WEBSOCKET.ENABLED_TRANSPORTS,
                disableStats: true,
                cluster: CONFIG.WEBSOCKET.CLUSTER
            });

            this.pusher.connection.bind('connected', () => {
                this.connected = true;
                this.triggerCallback('connected');
                console.log('Connected to Laravel Reverb WebSocket');
            });

            this.pusher.connection.bind('disconnected', () => {
                this.connected = false;
                this.triggerCallback('disconnected');
                console.log('Disconnected from WebSocket');
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('WebSocket error:', error);
                this.triggerCallback('error', error);
            });

            // S'abonner au channel de livraisons
            this.channel = this.pusher.subscribe('delivery-tracking');
            
            // Écouter les événements de mise à jour de position
            this.channel.bind('delivery-position-updated', (data) => {
                this.triggerCallback('positionUpdate', data);
            });

            // Écouter les événements de changement de statut
            this.channel.bind('delivery-status-updated', (data) => {
                this.triggerCallback('statusUpdate', data);
            });

        } catch (error) {
            console.error('WebSocket connection failed:', error);
            this.triggerCallback('error', error);
        }
    }

    subscribeToDelivery(orderNumber) {
        if (!this.pusher) {
            console.warn('WebSocket not initialized');
            return;
        }

        if (this.channel) {
            this.pusher.unsubscribe(this.channel.name);
        }

        this.channel = this.pusher.subscribe(`delivery.${orderNumber}`);
        
        this.channel.bind('DeliveryLocationUpdated', (data) => {
            this.triggerCallback('locationUpdated', data);
        });

        this.channel.bind('DeliveryStatusUpdated', (data) => {
            this.triggerCallback('statusUpdated', data);
        });

        console.log(`Subscribed to delivery.${orderNumber}`);
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

    disconnect() {
        if (this.channel) {
            this.pusher.unsubscribe(this.channel.name);
            this.channel = null;
        }
        if (this.pusher) {
            this.pusher.disconnect();
            this.pusher = null;
        }
        this.connected = false;
    }
}

// Service pour les appels API côté client (sans authentification)
class CustomerApiService {
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
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Récupérer la liste des clients
    async getCustomers(search = '', page = 1) {
        let endpoint = CONFIG.API.ENDPOINTS.CUSTOMERS;
        
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CONFIG.UI.DEFAULT_PAGINATION.toString()
        });
        
        if (search.trim()) {
            params.append('search', search.trim());
        }
        
        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    // Récupérer les commandes d'un client
    async getCustomerOrders(customerId, filters = {}, page = 1) {
        let endpoint = CONFIG.API.ENDPOINTS.CUSTOMER_ORDERS.replace('{id}', customerId);
        
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters
        });
        
        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    // Récupérer les détails du tracking d'une commande
    async getTrackingDetails(orderNumber) {
        const endpoint = CONFIG.API.ENDPOINTS.TRACKING_DETAILS.replace('{orderNumber}', orderNumber);
        return this.request(endpoint);
    }
}

// Service pour la gestion de la carte Mapbox côté client
class CustomerMapService {
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
            this.addRouteSource();
            this.initialized = true;
            console.log('Customer map initialized successfully');
        });

        this.map.on('error', (e) => {
            console.error('Map error:', e);
        });

        return this.map;
    }

    addRouteSource() {
        if (!this.map.getSource('route')) {
            this.map.addSource('route', {
                'type': 'geojson',
                'data': {
                    'type': 'Feature',
                    'properties': {},
                    'geometry': {
                        'type': 'LineString',
                        'coordinates': []
                    }
                }
            });

            this.map.addLayer({
                'id': 'route',
                'type': 'line',
                'source': 'route',
                'layout': {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                'paint': {
                    'line-color': '#3887be',
                    'line-width': 5,
                    'line-opacity': 0.75
                }
            });
        }
    }

    updateDriverPosition(lat, lng, driverInfo = {}) {
        if (this.driverMarker) {
            this.driverMarker.remove();
        }
        
        this.driverMarker = new mapboxgl.Marker({ color: '#1E88E5' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Livreur</strong><br>
                ${driverInfo.name || 'En cours...'}<br>
                Position actuelle
            `))
            .addTo(this.map);

        // Centrer la carte sur le livreur
        this.map.flyTo({
            center: [lng, lat],
            zoom: 14,
            duration: CONFIG.UI.ANIMATION_DURATION
        });
    }

    setDestination(lat, lng, customerInfo = {}) {
        if (this.destinationMarker) {
            this.destinationMarker.remove();
        }
        
        this.destinationMarker = new mapboxgl.Marker({ color: '#E53935' })
            .setLngLat([lng, lat])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>Destination</strong><br>
                ${customerInfo.name || 'Client'}<br>
                ${customerInfo.address || 'Adresse de livraison'}
            `))
            .addTo(this.map);
    }

    async drawRoute(start, end) {
        try {
            const query = await fetch(
                `https://api.mapbox.com/directions/v5/mapbox/driving/${start[0]},${start[1]};${end[0]},${end[1]}?steps=true&geometries=geojson&access_token=${CONFIG.MAPBOX.ACCESS_TOKEN}`
            );
            const json = await query.json();
            
            if (json.routes && json.routes.length > 0) {
                const route = json.routes[0];
                
                // Vérifier que la source existe avant de l'utiliser
                if (this.map.getSource('route')) {
                    this.map.getSource('route').setData({
                        'type': 'Feature',
                        'properties': {},
                        'geometry': route.geometry
                    });
                } else {
                    console.warn('Route source not found, recreating...');
                    this.addRouteSource();
                    
                    // Essayer à nouveau après création
                    if (this.map.getSource('route')) {
                        this.map.getSource('route').setData({
                            'type': 'Feature',
                            'properties': {},
                            'geometry': route.geometry
                        });
                    }
                }

                return {
                    duration: Math.round(route.duration / 60), // en minutes
                    distance: (route.distance / 1000).toFixed(1) // en km
                };
            }
        } catch (error) {
            console.error('Error getting route:', error);
            return null;
        }
    }

    centerOnLocation(lat, lng, zoom = 14) {
        if (this.map) {
            this.map.flyTo({
                center: [lng, lat],
                zoom: zoom,
                duration: CONFIG.UI.ANIMATION_DURATION
            });
        }
    }

    clearMarkers() {
        if (this.driverMarker) {
            this.driverMarker.remove();
            this.driverMarker = null;
        }
        if (this.destinationMarker) {
            this.destinationMarker.remove();
            this.destinationMarker = null;
        }
    }

    clearRoute() {
        if (this.map.getSource('route')) {
            this.map.getSource('route').setData({
                'type': 'Feature',
                'properties': {},
                'geometry': {
                    'type': 'LineString',
                    'coordinates': []
                }
            });
        }
    }

    destroy() {
        this.clearMarkers();
        if (this.map) this.map.remove();
        this.initialized = false;
    }
}

// Service pour la gestion WebSocket côté client
class CustomerWebSocketService {
    constructor() {
        this.pusher = null;
        this.channels = new Map();
        this.connected = false;
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

    initialize() {
        try {
            this.pusher = new Pusher(CONFIG.WEBSOCKET.APP_KEY, {
                wsHost: CONFIG.WEBSOCKET.HOST,
                wsPort: CONFIG.WEBSOCKET.PORT,
                wssPort: CONFIG.WEBSOCKET.PORT,
                forceTLS: CONFIG.WEBSOCKET.FORCE_TLS,
                enabledTransports: CONFIG.WEBSOCKET.ENABLED_TRANSPORTS,
                cluster: 'mt1'
            });

            this.pusher.connection.bind('connected', () => {
                console.log('Connected to WebSocket');
                this.connected = true;
                this.triggerCallback('connected');
            });

            this.pusher.connection.bind('disconnected', () => {
                console.log('Disconnected from WebSocket');
                this.connected = false;
                this.triggerCallback('disconnected');
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('WebSocket error:', error);
                this.triggerCallback('error', error);
            });

        } catch (error) {
            console.error('Error initializing WebSocket:', error);
            this.triggerCallback('error', error);
        }
    }

    subscribeToDeliveryTracking(orderNumber) {
        if (!this.pusher || !this.connected) {
            console.warn('WebSocket not connected, cannot subscribe');
            return;
        }

        const channelName = `delivery.${orderNumber}`;
        
        if (this.channels.has(channelName)) {
            console.log('Already subscribed to', channelName);
            return;
        }

        try {
            const channel = this.pusher.subscribe(channelName);
            
            channel.bind('location-updated', (data) => {
                this.triggerCallback('locationUpdate', data);
            });

            channel.bind('status-changed', (data) => {
                this.triggerCallback('statusUpdate', data);
            });

            channel.bind('delivery-completed', (data) => {
                this.triggerCallback('deliveryCompleted', data);
            });

            this.channels.set(channelName, channel);
            console.log('Subscribed to delivery tracking:', channelName);
            
        } catch (error) {
            console.error('Error subscribing to delivery tracking:', error);
        }
    }

    unsubscribeFromDeliveryTracking(orderNumber) {
        const channelName = `delivery.${orderNumber}`;
        
        if (this.channels.has(channelName)) {
            try {
                this.pusher.unsubscribe(channelName);
                this.channels.delete(channelName);
                console.log('Unsubscribed from delivery tracking:', channelName);
            } catch (error) {
                console.error('Error unsubscribing from delivery tracking:', error);
            }
        }
    }

    disconnect() {
        if (this.pusher) {
            // Désabonner de tous les channels
            this.channels.forEach((channel, channelName) => {
                this.pusher.unsubscribe(channelName);
            });
            this.channels.clear();
            
            this.pusher.disconnect();
            this.connected = false;
        }
    }

    isConnected() {
        return this.connected;
    }
}