<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Suivi en temps réel - {{ $order->order_number }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
        }
        
        .tracking-header {
            background: rgba(34,112,147, 1); /* Utilisation du primary */
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 60px;
        }
        
        .tracking-container {
            margin-top: 60px;
            height: calc(100vh - 60px);
            display: flex;
        }
        
        .map-container {
            flex: 1;
            position: relative;
        }
        
        #map {
            width: 100%;
            height: 100%;
        }
        
        .info-panel {
            width: 350px;
            background: white;
            border-left: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        
        .panel-section {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            flex-shrink: 0;
        }
        
        .panel-section.scrollable {
            flex: 1;
            overflow-y: auto;
            border-bottom: none;
        }
        
        .status-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }
        
        .progress-section {
            background: #f8f9fa;
        }
        
        .progress {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: rgba(34,112,147, 1);
            transition: width 0.3s ease;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.8rem;
        }
        
        .stat-value {
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
        }
        
        .websocket-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .ws-connected {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .ws-disconnected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .event-item {
            padding: 0.5rem;
            border-left: 3px solid rgba(34,112,147, 0.3);
            margin-bottom: 0.5rem;
            background: #f8f9fa;
            border-radius: 0 0.375rem 0.375rem 0;
            font-size: 0.8rem;
        }
        
        .event-time {
            font-size: 0.7rem;
            color: #6c757d;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        h6 {
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .tracking-container {
                flex-direction: column;
            }
            
            .info-panel {
                width: 100%;
                height: 40vh;
                border-left: none;
                border-top: 1px solid #dee2e6;
            }
            
            .map-container {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="tracking-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-auto">
                    <a href="{{ route('orders.details', $order->id) }}" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la commande
                    </a>
                </div>
                <div class="col text-center">
                    <h4 class="mb-0">
                        Suivi en temps réel - {{ $order->order_number }}
                    </h4>
                </div>
                <div class="col-auto">
                    <!-- Espace pour équilibrer -->
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Container -->
    <div class="tracking-container">
        <!-- Map -->
        <div class="map-container">
            <div id="map"></div>
            
            <!-- WebSocket Status -->
            <div id="websocket-status" class="websocket-status ws-disconnected">
                <i class="fas fa-wifi me-1"></i>
                <span id="ws-status-text">Connexion...</span>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="info-panel">
            <!-- Order Info -->
            <div class="panel-section">
                <h6 class="mb-3">Informations de la commande</h6>
                <div class="stat-item">
                    <span class="stat-label">Numéro</span>
                    <span class="stat-value">{{ $order->order_number }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Client</span>
                    <span class="stat-value">{{ $order->customer->full_name ?? 'N/A' }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Montant</span>
                    <span class="stat-value">{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Statut</span>
                    <span class="badge status-badge bg-{{ $order->status === 'processing' ? 'warning' : 'primary' }}">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="panel-section progress-section">
                <h6 class="mb-3">Progression de la livraison</h6>
                <div class="progress mb-3">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="progress-text" class="text-center mb-3">
                    <strong>0%</strong> complété
                </div>
                
                <div class="stat-item">
                    <span class="stat-label">Distance restante</span>
                    <span id="distance-remaining" class="stat-value">Calcul...</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Temps estimé</span>
                    <span id="eta-remaining" class="stat-value">Calcul...</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Vitesse actuelle</span>
                    <span id="current-speed" class="stat-value">-- km/h</span>
                </div>
            </div>

            <!-- Delivery Person Info -->
            @if($order->deliveryPerson)
            <div class="panel-section">
                <h6 class="mb-3">Livreur</h6>
                <div class="stat-item">
                    <span class="stat-label">Nom</span>
                    <span class="stat-value">{{ $order->deliveryPerson->user->fullname }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Téléphone</span>
                    <span class="stat-value">{{ $order->deliveryPerson->user->phone_number ?? 'N/A' }}</span>
                </div>
            </div>
            @endif

            <!-- Events History -->
            <div class="panel-section">
                <h6 class="mb-3">Historique des événements</h6>
                <div id="events-container">
                    <div class="event-item">
                        <div class="event-time">{{ now()->format('H:i:s') }}</div>
                        <div>Chargement des données en cours...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <!-- Reverb Client -->
    <script type="module">
        // Import du ReverbClient
        class ReverbClient {
            constructor(appKey, options = {}) {
                this.appKey = appKey;
                this.options = {
                    wsHost: options.wsHost || "127.0.0.1",
                    wsPort: options.wsPort || 8080,
                    ...options,
                };
                this.state = "initialized";
                this.channels = new Map();
                this.callbacks = new Map();
                this.socketId = null;
                this.connection = { bind: this.bind.bind(this) };
                this.connect();
            }

            connect() {
                const url = `ws://${this.options.wsHost}:${this.options.wsPort}/app/${this.appKey}?protocol=7&client=js&version=8.3.0&flash=false`;
                console.log("🔗 ReverbClient connecting to:", url);

                this.ws = new WebSocket(url);

                this.ws.onopen = () => {
                    this.setState("connecting");
                };

                this.ws.onmessage = (event) => {
                    const message = JSON.parse(event.data);
                    this.handleMessage(message);
                };

                this.ws.onerror = (error) => {
                    console.error("❌ ReverbClient error:", error);
                    this.setState("failed");
                };

                this.ws.onclose = (event) => {
                    console.log("🔒 ReverbClient closed:", event.code, event.reason);
                    this.setState("disconnected");
                };
            }

            handleMessage(message) {
                if (message.event === "pusher:ping") {
                    this.send({
                        event: "pusher:pong",
                        data: {},
                    });
                    return;
                }

                if (message.event === "pusher:connection_established") {
                    this.socketId = JSON.parse(message.data).socket_id;
                    this.setState("connected");
                    this.trigger("connected");
                } else if (message.channel) {
                    const channel = this.channels.get(message.channel);
                    if (channel) {
                        channel.trigger(
                            message.event,
                            JSON.parse(message.data || "{}"),
                        );
                    }
                }
            }

            setState(newState) {
                const previousState = this.state;
                this.state = newState;
                console.log(`🔄 ReverbClient: ${previousState} → ${newState}`);
                this.trigger("state_change", {
                    previous: previousState,
                    current: newState,
                });
            }

            subscribe(channelName) {
                const channel = new ReverbChannel(this, channelName);
                this.channels.set(channelName, channel);

                if (this.state === "connected") {
                    channel.subscribe();
                }

                return channel;
            }

            bind(event, callback) {
                if (!this.callbacks.has(event)) {
                    this.callbacks.set(event, []);
                }
                this.callbacks.get(event).push(callback);
            }

            trigger(event, data) {
                const callbacks = this.callbacks.get(event) || [];
                callbacks.forEach((callback) => callback(data));
            }

            send(data) {
                if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                    this.ws.send(JSON.stringify(data));
                }
            }
        }

        class ReverbChannel {
            constructor(client, name) {
                this.client = client;
                this.name = name;
                this.callbacks = new Map();
                this.subscribed = false;
            }

            subscribe() {
                this.client.send({
                    event: "pusher:subscribe",
                    data: { channel: this.name },
                });
            }

            bind(event, callback) {
                if (!this.callbacks.has(event)) {
                    this.callbacks.set(event, []);
                }
                this.callbacks.get(event).push(callback);
            }

            trigger(event, data) {
                const callbacks = this.callbacks.get(event) || [];
                callbacks.forEach((callback) => callback(data));

                if (event === "pusher_internal:subscription_succeeded") {
                    this.subscribed = true;
                    console.log(`📡 Souscrit au canal: ${this.name}`);
                }
            }
        }

        // Rendre disponible globalement
        window.ReverbClient = ReverbClient;
    </script>

    <!-- Configuration -->
    <script>
        window.MANAGER_TRACKING_CONFIG = @json($trackingConfig);
    </script>

    <!-- Manager Tracking App -->
    <script>
        class ManagerTrackingApp {
            constructor() {
                this.map = null;
                this.pusher = null;
                this.trackingChannel = null;
                this.currentOrder = null;
                this.driverMarker = null;
                this.destinationMarker = null;
                this.routeLayer = null;
                this.mapInitialized = false;
                
                this.init();
            }

            async init() {
                console.log('🚀 Manager Tracking App - Initialisation...');
                
                this.initializeMap();
                this.initializeWebSocket();
                await this.loadInitialData();
                this.startPeriodicUpdates();
            }

            initializeMap() {
                mapboxgl.accessToken = MANAGER_TRACKING_CONFIG.mapbox.access_token;
                
                this.map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [11.5021, 3.8480], // Yaoundé
                    zoom: 12
                });

                this.map.on('load', () => {
                    console.log('✅ Carte initialisée');
                });
            }

            initializeWebSocket() {
                if (!MANAGER_TRACKING_CONFIG.websocket.enabled) {
                    console.warn('WebSocket désactivé');
                    this.updateWebSocketStatus(false);
                    return;
                }

                try {
                    // Utiliser ReverbClient au lieu de Pusher
                    this.pusher = new ReverbClient(MANAGER_TRACKING_CONFIG.websocket.key, {
                        wsHost: MANAGER_TRACKING_CONFIG.websocket.host,
                        wsPort: MANAGER_TRACKING_CONFIG.websocket.port
                    });

                    this.trackingChannel = this.pusher.subscribe('delivery-tracking');
                    
                    this.trackingChannel.bind('delivery-position-updated', (data) => {
                        console.log('📍 Position mise à jour:', data);
                        this.handlePositionUpdate(data);
                    });

                    // Écouter les changements d'état de connexion
                    this.pusher.bind('connected', () => {
                        console.log('✅ Reverb WebSocket connecté');
                        this.updateWebSocketStatus(true);
                    });

                    this.pusher.bind('state_change', (states) => {
                        console.log(`🔄 État WebSocket: ${states.previous} → ${states.current}`);
                        const isConnected = states.current === 'connected';
                        this.updateWebSocketStatus(isConnected);
                    });

                } catch (error) {
                    console.error('❌ Erreur WebSocket:', error);
                    this.updateWebSocketStatus(false);
                }
            }

            async loadInitialData() {
                try {
                    const response = await fetch(MANAGER_TRACKING_CONFIG.api_endpoints.tracking_details);
                    const data = await response.json();
                    
                    if (data._metadata?.success && data.data) {
                        this.currentOrder = data.data;
                        this.updateUI();
                        this.updateMap();
                    }
                } catch (error) {
                    console.error('❌ Erreur chargement données:', error);
                }
            }

            handlePositionUpdate(data) {
                if (data.order_number === MANAGER_TRACKING_CONFIG.order_number) {
                    console.log('📊 Mise à jour position pour commande:', data.order_number);
                    
                    // Mettre à jour les données
                    Object.assign(this.currentOrder, data);
                    
                    // Mettre à jour l'interface
                    this.updateUI();
                    this.updateMap();
                    
                    // Ajouter événement
                    this.addEvent(`Position mise à jour: ${data.driver_lat}, ${data.driver_lng}`);
                }
            }

            updateUI() {
                if (!this.currentOrder) return;

                // Progression
                const progress = parseFloat(this.currentOrder.progress_percentage || 0);
                document.getElementById('progress-bar').style.width = `${progress}%`;
                document.getElementById('progress-text').innerHTML = `<strong>${Math.round(progress)}%</strong> complété`;
                
                // Statistiques
                document.getElementById('distance-remaining').textContent = 
                    this.currentOrder.distance_remaining ? `${this.currentOrder.distance_remaining} km` : 'N/A';
                    
                document.getElementById('eta-remaining').textContent = 
                    this.currentOrder.estimated_duration ? `${this.currentOrder.estimated_duration} min` : 'N/A';
                    
                document.getElementById('current-speed').textContent = 
                    this.currentOrder.current_speed ? `${this.currentOrder.current_speed} km/h` : '-- km/h';
            }

            updateMap() {
                if (!this.currentOrder || !this.map) return;

                const hasDriverCoords = this.currentOrder.driver_lat && this.currentOrder.driver_lng;
                const hasDestinationCoords = this.currentOrder.destination_lat && this.currentOrder.destination_lng;

                // Position du livreur
                if (hasDriverCoords) {
                    const driverPosition = [
                        parseFloat(this.currentOrder.driver_lng),
                        parseFloat(this.currentOrder.driver_lat)
                    ];

                    if (this.driverMarker) {
                        this.driverMarker.setLngLat(driverPosition);
                    } else {
                        this.driverMarker = new mapboxgl.Marker({ color: '#007bff' })
                            .setLngLat(driverPosition)
                            .setPopup(new mapboxgl.Popup().setHTML('<strong>Livreur</strong>'))
                            .addTo(this.map);
                    }
                }

                // Destination
                if (hasDestinationCoords) {
                    const destinationPosition = [
                        parseFloat(this.currentOrder.destination_lng),
                        parseFloat(this.currentOrder.destination_lat)
                    ];

                    if (!this.destinationMarker) {
                        this.destinationMarker = new mapboxgl.Marker({ color: '#dc3545' })
                            .setLngLat(destinationPosition)
                            .setPopup(new mapboxgl.Popup().setHTML('<strong>Destination</strong>'))
                            .addTo(this.map);
                    }
                }

                // Tracer l'itinéraire seulement s'il n'existe pas encore
                if (hasDriverCoords && hasDestinationCoords && !this.map.getSource('route')) {
                    this.drawRoute();
                }

                // Ajuster la vue seulement si c'est la première fois
                if (!this.mapInitialized) {
                    this.fitMapToPoints();
                    this.mapInitialized = true;
                }
            }

            async drawRoute() {
                const start = [parseFloat(this.currentOrder.driver_lng), parseFloat(this.currentOrder.driver_lat)];
                const end = [parseFloat(this.currentOrder.destination_lng), parseFloat(this.currentOrder.destination_lat)];

                try {
                    const response = await fetch(
                        `https://api.mapbox.com/directions/v5/mapbox/driving/${start[0]},${start[1]};${end[0]},${end[1]}?steps=true&geometries=geojson&access_token=${MANAGER_TRACKING_CONFIG.mapbox.access_token}`
                    );
                    
                    const data = await response.json();
                    
                    if (data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        
                        // Supprimer l'ancien itinéraire s'il existe
                        if (this.map.getSource('route')) {
                            this.map.removeLayer('route');
                            this.map.removeSource('route');
                        }

                        // Ajouter le nouvel itinéraire
                        this.map.addSource('route', {
                            type: 'geojson',
                            data: {
                                type: 'Feature',
                                properties: {},
                                geometry: route.geometry
                            }
                        });

                        this.map.addLayer({
                            id: 'route',
                            type: 'line',
                            source: 'route',
                            layout: {
                                'line-join': 'round',
                                'line-cap': 'round'
                            },
                            paint: {
                                'line-color': 'rgba(34,112,147, 0.8)',
                                'line-width': 4
                            }
                        });

                        console.log('✅ Itinéraire tracé');
                    }
                } catch (error) {
                    console.error('❌ Erreur lors du calcul de l\'itinéraire:', error);
                }
            }

            fitMapToPoints() {
                const points = [];
                
                if (this.driverMarker) {
                    points.push(this.driverMarker.getLngLat().toArray());
                }
                
                if (this.destinationMarker) {
                    points.push(this.destinationMarker.getLngLat().toArray());
                }

                if (points.length > 0) {
                    const bounds = new mapboxgl.LngLatBounds();
                    points.forEach(point => bounds.extend(point));
                    
                    this.map.fitBounds(bounds, {
                        padding: { top: 50, bottom: 50, left: 50, right: 50 },
                        maxZoom: 15
                    });
                }
            }

            updateWebSocketStatus(connected) {
                const statusElement = document.getElementById('websocket-status');
                const textElement = document.getElementById('ws-status-text');
                
                if (connected) {
                    statusElement.className = 'websocket-status ws-connected';
                    textElement.textContent = 'Temps réel actif';
                } else {
                    statusElement.className = 'websocket-status ws-disconnected';
                    textElement.textContent = 'Hors ligne';
                }
            }

            addEvent(message) {
                const container = document.getElementById('events-container');
                const eventDiv = document.createElement('div');
                eventDiv.className = 'event-item';
                eventDiv.innerHTML = `
                    <div class="event-time">${new Date().toLocaleTimeString()}</div>
                    <div>${message}</div>
                `;
                container.insertBefore(eventDiv, container.firstChild);
                
                // Limiter à 10 événements
                while (container.children.length > 10) {
                    container.removeChild(container.lastChild);
                }
            }

            startPeriodicUpdates() {
                setInterval(() => {
                    this.loadInitialData();
                }, 5000); // Mise à jour toutes les 5 secondes
            }
        }

        // Initialiser l'application
        document.addEventListener('DOMContentLoaded', () => {
            window.managerApp = new ManagerTrackingApp();
        });
    </script>
</body>
</html>