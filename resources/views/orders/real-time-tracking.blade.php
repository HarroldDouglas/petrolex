<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Suivi en temps réel - {{ $order->order_number }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Maps - pas besoin de CSS externe -->
    
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
                    <span class="badge status-badge {{ $order->status->getBadgeClass() }}">
                        {{ $order->status->label }}
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
            <div class="panel-section scrollable">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <!-- Google Maps callback -->
    <script>
        window.initGoogleMapsManager = function() {
            console.log("🗺️ Google Maps chargé pour Manager");
            window.googleMapsLoaded = true;
            if (window.managerApp && window.managerApp.onGoogleMapsReady) {
                window.managerApp.onGoogleMapsReady();
            }
        };
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $trackingConfig['google_maps']['api_key'] }}&libraries=places&callback=initGoogleMapsManager"></script>

    <!-- Configuration -->
    <script>
        window.MANAGER_TRACKING_CONFIG = @json($trackingConfig);

        // Fusionner les données de tracking avec les coordonnées de destination de la commande
        @if($order->deliveryTracking)
            window.INITIAL_TRACKING_DATA = @json($order->deliveryTracking);
            window.INITIAL_TRACKING_DATA.destination_lat = @json($order->destination_lat);
            window.INITIAL_TRACKING_DATA.destination_lng = @json($order->destination_lng);
        @else
            window.INITIAL_TRACKING_DATA = {
                destination_lat: @json($order->destination_lat),
                destination_lng: @json($order->destination_lng)
            };
        @endif

        console.log('📦 INITIAL_TRACKING_DATA:', window.INITIAL_TRACKING_DATA);
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
                this.routePolyline = null;
                this.mapInitialized = false;
                this.viewAdjusted = false;

                this.init();
            }

            async init() {
                console.log('🚀 Manager Tracking App - Initialisation...');

                // Attendre que Google Maps soit chargé avant de continuer
                await this.waitForGoogleMaps();

                this.initializeMap();
                this.initializeWebSocket();
                await this.loadInitialData();
                this.startPeriodicUpdates();
            }

            waitForGoogleMaps() {
                return new Promise((resolve) => {
                    if (window.google && window.google.maps) {
                        console.log('✅ Google Maps déjà chargé');
                        resolve();
                    } else {
                        console.log('⏳ En attente de Google Maps...');
                        window.initGoogleMapsManager = () => {
                            console.log('✅ Google Maps chargé via callback');
                            window.googleMapsLoaded = true;
                            resolve();
                        };
                    }
                });
            }

            initializeMap() {
                this.map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: 3.8480, lng: 11.5021 }, // Yaoundé
                    zoom: 12,
                    mapTypeControl: false,
                    streetViewControl: false
                });

                console.log('✅ Carte Google Maps initialisée');
                this.mapInitialized = true;
            }

            initializeWebSocket() {
                if (!MANAGER_TRACKING_CONFIG.websocket.enabled) {
                    console.warn('WebSocket désactivé');
                    this.updateWebSocketStatus(false);
                    return;
                }

                try {
                    // Configuration Pusher pour Reverb
                    Pusher.logToConsole = true; // Debug

                    console.log('🔧 Configuration Pusher:', {
                        key: MANAGER_TRACKING_CONFIG.websocket.key,
                        host: MANAGER_TRACKING_CONFIG.websocket.host,
                        port: MANAGER_TRACKING_CONFIG.websocket.port,
                        cluster: MANAGER_TRACKING_CONFIG.websocket.cluster
                    });

                    this.pusher = new Pusher(MANAGER_TRACKING_CONFIG.websocket.key, {
                        wsHost: MANAGER_TRACKING_CONFIG.websocket.host,
                        wsPort: MANAGER_TRACKING_CONFIG.websocket.port,
                        wssPort: MANAGER_TRACKING_CONFIG.websocket.port,
                        forceTLS: MANAGER_TRACKING_CONFIG.websocket.force_tls || false,
                        encrypted: false,
                        disableStats: true,
                        enabledTransports: ['ws', 'wss'],
                        cluster: MANAGER_TRACKING_CONFIG.websocket.cluster || 'mt1',
                        activityTimeout: 30000,
                        pongTimeout: 10000
                    });

                    // S'abonner au canal spécifique de la commande
                    const channelName = `delivery-${MANAGER_TRACKING_CONFIG.order_number}`;
                    console.log(`📡 Abonnement au canal: ${channelName}`);
                    this.trackingChannel = this.pusher.subscribe(channelName);

                    this.trackingChannel.bind('delivery-position-updated', (data) => {
                        console.log('📍 Position mise à jour:', data);
                        this.handlePositionUpdate(data);
                    });

                    // Écouter les changements d'état de connexion Pusher
                    this.pusher.connection.bind('connected', () => {
                        console.log('✅ Pusher WebSocket connecté');
                        this.updateWebSocketStatus(true);
                    });

                    this.pusher.connection.bind('disconnected', () => {
                        console.log('⚠️ Pusher WebSocket déconnecté');
                        this.updateWebSocketStatus(false);
                    });

                    this.pusher.connection.bind('error', (err) => {
                        console.error('❌ Erreur Pusher:', err);
                        this.updateWebSocketStatus(false);
                    });

                } catch (error) {
                    console.error('❌ Erreur WebSocket:', error);
                    this.updateWebSocketStatus(false);
                }
            }

            async loadInitialData() {
                try {
                    // Utiliser les données initiales chargées côté serveur
                    if (window.INITIAL_TRACKING_DATA) {
                        this.currentOrder = window.INITIAL_TRACKING_DATA;
                        console.log('✅ Données initiales chargées:', this.currentOrder);
                        this.updateUI();
                        this.updateMap();
                        this.addEvent('Données de tracking chargées');
                        return;
                    }

                    // Fallback: essayer de charger via API avec authentification
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(MANAGER_TRACKING_CONFIG.api_endpoints.tracking_details, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    const data = await response.json();

                    if (data._metadata?.success && data.data) {
                        this.currentOrder = data.data;
                        this.updateUI();
                        this.updateMap();
                    } else {
                        console.warn('⚠️ Pas de données de tracking disponibles');
                        this.addEvent('En attente des données de tracking...');
                    }
                } catch (error) {
                    console.error('❌ Erreur chargement données:', error);
                    this.addEvent('Erreur de chargement - en attente WebSocket');
                }
            }

            handlePositionUpdate(data) {
                console.log('📡 Événement WebSocket reçu:', data);

                // Initialiser currentOrder s'il n'existe pas
                if (!this.currentOrder) {
                    this.currentOrder = {};
                }

                // Mapper les données de l'événement vers le format attendu
                const mappedData = {
                    order_id: data.order_id,
                    order_number: data.order_number,
                    driver_lat: data.current_latitude || data.driver_lat,
                    driver_lng: data.current_longitude || data.driver_lng,
                    destination_lat: data.destination_latitude || data.destination_lat,
                    destination_lng: data.destination_longitude || data.destination_lng,
                    progress_percentage: data.progress_percentage,
                    distance_remaining: data.distance_remaining,
                    estimated_duration: data.estimated_duration || data.eta,
                    current_speed: data.current_speed,
                    status: data.status
                };

                console.log('✅ Données mappées:', mappedData);

                // Mettre à jour les données
                Object.assign(this.currentOrder, mappedData);

                // Mettre à jour l'interface
                this.updateUI();
                this.updateMap();

                // Ajouter événement
                const progress = Math.round(parseFloat(data.progress_percentage || 0));
                this.addEvent(`Position: ${progress}% - ${data.distance_remaining || '?'}km - ${data.estimated_duration || '?'}min`);
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
                if (!this.currentOrder || !this.map) {
                    console.log('⏭️ updateMap ignoré:', {currentOrder: !!this.currentOrder, map: !!this.map});
                    return;
                }

                const hasDriverCoords = this.currentOrder.driver_lat && this.currentOrder.driver_lng;
                const hasDestinationCoords = this.currentOrder.destination_lat && this.currentOrder.destination_lng;

                console.log('🗺️ updateMap:', {
                    hasDriverCoords,
                    hasDestinationCoords,
                    driver: hasDriverCoords ? `${this.currentOrder.driver_lat}, ${this.currentOrder.driver_lng}` : 'N/A',
                    destination: hasDestinationCoords ? `${this.currentOrder.destination_lat}, ${this.currentOrder.destination_lng}` : 'N/A'
                });

                // Position du livreur
                if (hasDriverCoords) {
                    const driverLatLng = { lat: parseFloat(this.currentOrder.driver_lat), lng: parseFloat(this.currentOrder.driver_lng) };

                    if (this.driverMarker) {
                        this.driverMarker.setPosition(driverLatLng);
                        console.log('📍 Marqueur livreur mis à jour');
                    } else {
                        this.driverMarker = new google.maps.Marker({
                            position: driverLatLng,
                            map: this.map,
                            title: 'Livreur',
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 10,
                                fillColor: '#007bff',
                                fillOpacity: 1,
                                strokeColor: '#ffffff',
                                strokeWeight: 2
                            }
                        });
                        console.log('✅ Marqueur livreur créé');
                    }
                }

                // Destination
                if (hasDestinationCoords) {
                    const destLatLng = { lat: parseFloat(this.currentOrder.destination_lat), lng: parseFloat(this.currentOrder.destination_lng) };

                    if (!this.destinationMarker) {
                        this.destinationMarker = new google.maps.Marker({
                            position: destLatLng,
                            map: this.map,
                            title: 'Destination',
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 10,
                                fillColor: '#dc3545',
                                fillOpacity: 1,
                                strokeColor: '#ffffff',
                                strokeWeight: 2
                            }
                        });
                        console.log('✅ Marqueur destination créé');
                    }
                }

                // Redessiner l'itinéraire à chaque mise à jour de position
                if (hasDriverCoords && hasDestinationCoords) {
                    console.log('🛣️ Mise à jour de l\'itinéraire...');
                    this.drawRoute();
                }

                // Ajuster la vue seulement si c'est la première fois
                if (!this.viewAdjusted && hasDriverCoords && hasDestinationCoords) {
                    console.log('🎯 Ajustement de la vue...');
                    this.fitMapToPoints();
                    this.viewAdjusted = true;
                }
            }

            async drawRoute() {
                const origin = { lat: parseFloat(this.currentOrder.driver_lat), lng: parseFloat(this.currentOrder.driver_lng) };
                const destination = { lat: parseFloat(this.currentOrder.destination_lat), lng: parseFloat(this.currentOrder.destination_lng) };

                console.log('🚗 Tracé itinéraire:', {
                    origin: `${origin.lat}, ${origin.lng}`,
                    destination: `${destination.lat}, ${destination.lng}`
                });

                // Nettoyer l'ancien itinéraire s'il existe
                if (this.routePolyline) {
                    this.routePolyline.setMap(null);
                    console.log('🗑️ Ancien itinéraire supprimé');
                }

                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: this.map,
                    suppressMarkers: true, // On utilise nos propres marqueurs
                    polylineOptions: {
                        strokeColor: '#227093',
                        strokeWeight: 4
                    }
                });

                try {
                    const result = await directionsService.route({
                        origin: origin,
                        destination: destination,
                        travelMode: google.maps.TravelMode.DRIVING
                    });

                    directionsRenderer.setDirections(result);
                    this.routePolyline = directionsRenderer;
                    console.log('✅ Itinéraire mis à jour');
                } catch (error) {
                    console.error('❌ Erreur itinéraire:', error);
                }
            }

            fitMapToPoints() {
                const bounds = new google.maps.LatLngBounds();

                if (this.driverMarker) {
                    bounds.extend(this.driverMarker.getPosition());
                }

                if (this.destinationMarker) {
                    bounds.extend(this.destinationMarker.getPosition());
                }

                if (!bounds.isEmpty()) {
                    this.map.fitBounds(bounds, { top: 50, bottom: 50, left: 50, right: 50 });
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
                // Pas besoin de polling si WebSocket fonctionne
                // On garde juste une mise à jour toutes les 30 secondes en backup
                setInterval(() => {
                    if (!this.currentOrder || !this.currentOrder.driver_lat) {
                        console.log('🔄 Tentative de rechargement des données (backup)');
                        this.loadInitialData();
                    }
                }, 30000);
            }
        }

        // Initialiser l'application
        document.addEventListener('DOMContentLoaded', () => {
            window.managerApp = new ManagerTrackingApp();
        });
    </script>
</body>
</html>