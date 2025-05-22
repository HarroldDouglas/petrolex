<div>
    <div class="row justify-content-center map-container">
        <div class="col-md-12">
            <!-- Information sur le trajet (en une ligne) -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="me-3 mb-2">
                            <i class="ti ti-car text-primary me-1"></i> 
                            <strong>Mode:</strong> Voiture
                        </div>
                        <div class="me-3 mb-2">
                            <i class="ti ti-map-pin text-primary me-1"></i>
                            <strong>Départ:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $pointA['lat'] }},{{ $pointA['lng'] }}" 
                               target="_blank" title="Ouvrir dans Google Maps" class="text-primary">
                                {{ $pointA['name'] }} <i class="ti ti-external-link text-primary"></i>
                            </a>
                            <small class="text-muted">({{ $pointA['lat'] }}, {{ $pointA['lng'] }})</small>
                        </div>
                        <div class="me-3 mb-2">
                            <i class="ti ti-arrow-right text-secondary"></i>
                        </div>
                        <div class="mb-2">
                            <i class="ti ti-flag text-danger me-1"></i>
                            <strong>Arrivée:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $pointB['lat'] }},{{ $pointB['lng'] }}" 
                               target="_blank" title="Ouvrir dans Google Maps" class="text-danger">
                                {{ $pointB['name'] }} <i class="ti ti-external-link text-danger"></i>
                            </a>
                            <small class="text-muted">({{ $pointB['lat'] }}, {{ $pointB['lng'] }})</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informations de l'itinéraire -->
            <div class="route-info mb-3" id="route-info-panel">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-ruler"></i> Distance:</strong> <span id="distance-text">Calcul en cours...</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-clock"></i> Durée estimée:</strong> <span id="duration-text">Calcul en cours...</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Message d'erreur pour Google Maps -->
            <div id="maps-error" class="alert alert-danger" style="display: none;">
                <strong>Erreur de chargement de Google Maps</strong>
                <p>La clé API Google Maps n'est pas correctement configurée pour ce domaine.</p>
                <p>Pour les tests locaux, utilisez la solution alternative ci-dessous.</p>
            </div>
            
            <!-- Carte -->
            <div id="petrolex-map" style="height: 500px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"></div>
            
            <!-- Solution de secours si Google Maps échoue -->
            <div id="fallback-map" style="display: none;">
                <div class="alert alert-warning mt-3">
                    <strong>Affichage de secours :</strong> 
                    <p>La carte interactive n'a pas pu être chargée. Voici une représentation simplifiée du trajet.</p>
                    <p>
                        <a href="https://www.google.com/maps/dir/?api=1&origin={{ $pointA['lat'] }},{{ $pointA['lng'] }}&destination={{ $pointB['lat'] }},{{ $pointB['lng'] }}&travelmode=driving" 
                           target="_blank" class="btn btn-primary btn-sm">
                            <i class="ti ti-map"></i> Voir l'itinéraire sur Google Maps
                        </a>
                    </p>
                </div>
                <div class="text-center mt-3 p-5 bg-light rounded border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center bg-primary text-white p-3 rounded">
                            <i class="ti ti-map-pin fs-3"></i><br>
                            Point A<br>
                            {{ $pointA['name'] }}
                        </div>
                        <div class="flex-grow-1 px-4">
                            <i class="ti ti-arrows-right fs-1"></i>
                            <div class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>~ 2 km</span>
                                <span>~ 5-10 min</span>
                            </div>
                        </div>
                        <div class="text-center bg-danger text-white p-3 rounded">
                            <i class="ti ti-flag fs-3"></i><br>
                            Point B<br>
                            {{ $pointB['name'] }}
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Informations sur le trajet</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Distance estimée
                                <span class="badge bg-primary rounded-pill">2 km</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Durée du trajet (voiture)
                                <span class="badge bg-success rounded-pill">5-10 min</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .map-container {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .route-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }
        .gm-style-iw {
            min-width: 200px;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Empêcher l'initMap global d'être redéfini (pour éviter la récursion infinie)
        if (typeof window._petrolexMapsInitialized === 'undefined') {
            window._petrolexMapsInitialized = false;
            
            // Fonction pour afficher le fallback quand la carte échoue
            function showFallbackMap() {
                document.getElementById('petrolex-map').style.display = 'none';
                document.getElementById('maps-error').style.display = 'block';
                document.getElementById('fallback-map').style.display = 'block';
                document.getElementById('distance-text').textContent = 'Environ 2 km';
                document.getElementById('duration-text').textContent = 'Environ 5-10 minutes';
            }
            
            // Notre fonction d'initialisation spécifique
            function setupPetrolexMap() {
                try {
                    // Points fixes pour la carte
                    var POINT_A = {
                        lat: {{ $pointA['lat'] }},
                        lng: {{ $pointA['lng'] }},
                        name: "{{ $pointA['name'] }}"
                    };
                    
                    var POINT_B = {
                        lat: {{ $pointB['lat'] }},
                        lng: {{ $pointB['lng'] }},
                        name: "{{ $pointB['name'] }}"
                    };
                    
                    // Zoom par défaut
                    var DEFAULT_ZOOM = {{ $defaultZoom }};
                    
                    // Calcul du centre entre les points A et B
                    var centerLat = (POINT_A.lat + POINT_B.lat) / 2;
                    var centerLng = (POINT_A.lng + POINT_B.lng) / 2;
                    
                    // Création de la carte
                    var map = new google.maps.Map(document.getElementById('petrolex-map'), {
                        zoom: DEFAULT_ZOOM,
                        center: { lat: centerLat, lng: centerLng },
                        mapTypeControl: true,
                        fullscreenControl: true,
                        streetViewControl: true,
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        }
                    });
                    
                    // Services pour les itinéraires
                    var directionsService = new google.maps.DirectionsService();
                    var directionsRenderer = new google.maps.DirectionsRenderer({
                        map: map,
                        suppressMarkers: true // On gère nos propres marqueurs
                    });
                    
                    // Marqueur de départ (A)
                    var originMarker = new google.maps.Marker({
                        position: { lat: POINT_A.lat, lng: POINT_A.lng },
                        map: map,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                            scaledSize: new google.maps.Size(40, 40)
                        },
                        title: POINT_A.name,
                        animation: google.maps.Animation.DROP
                    });
                    
                    // Marqueur de destination (B)
                    var destinationMarker = new google.maps.Marker({
                        position: { lat: POINT_B.lat, lng: POINT_B.lng },
                        map: map,
                        icon: {
                            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                            scaledSize: new google.maps.Size(40, 40)
                        },
                        title: POINT_B.name,
                        animation: google.maps.Animation.DROP
                    });
                    
                    // Info-bulles pour les marqueurs
                    var originInfowindow = new google.maps.InfoWindow({
                        content: `<div><strong>${POINT_A.name}</strong><br>Point de départ</div>`
                    });
                    
                    var destinationInfowindow = new google.maps.InfoWindow({
                        content: `<div><strong>${POINT_B.name}</strong><br>Destination</div>`
                    });
                    
                    // Afficher les info-bulles au clic sur les marqueurs
                    originMarker.addListener('click', function() {
                        originInfowindow.open(map, originMarker);
                    });
                    
                    destinationMarker.addListener('click', function() {
                        destinationInfowindow.open(map, destinationMarker);
                    });
                    
                    // Calcul et affichage de l'itinéraire
                    // Options pour la requête d'itinéraire
                    var request = {
                        origin: { lat: POINT_A.lat, lng: POINT_A.lng },
                        destination: { lat: POINT_B.lat, lng: POINT_B.lng },
                        travelMode: google.maps.TravelMode.DRIVING,
                        provideRouteAlternatives: true
                    };
                    
                    // Demande d'itinéraire
                    directionsService.route(request, function(response, status) {
                        if (status === 'OK') {
                            // Affichage de l'itinéraire sur la carte
                            directionsRenderer.setDirections(response);
                            
                            // Récupération des informations de l'itinéraire
                            var route = response.routes[0];
                            var leg = route.legs[0];
                            
                            // Mise à jour des informations d'itinéraire
                            document.getElementById('distance-text').textContent = leg.distance.text;
                            document.getElementById('duration-text').textContent = leg.duration.text;
                            
                            // Envoi des données à Livewire
                            @this.set('distance', leg.distance.text);
                            @this.set('duration', leg.duration.text);
                            @this.set('steps', leg.steps.length);
                        } else {
                            console.error('Erreur lors du calcul de l\'itinéraire:', status);
                            document.getElementById('distance-text').textContent = 'Environ 2 km';
                            document.getElementById('duration-text').textContent = 'Environ 5-10 minutes';
                        }
                    });
                } catch (error) {
                    console.error('Erreur lors de l\'initialisation de Google Maps:', error);
                    showFallbackMap();
                }
            }
            
            // Fonction qui sera appelée par l'API Google Maps
            window.initMapPetrolex = function() {
                window._petrolexMapsInitialized = true;
                setupPetrolexMap();
            };
        }
        
        // Initialisation de la carte quand la page est chargée
        document.addEventListener('DOMContentLoaded', function() {
            // Éviter les initialisations multiples
            if (window._petrolexMapScriptLoaded) {
                return;
            }
            window._petrolexMapScriptLoaded = true;
            
            // Attendre 2 secondes pour voir si la carte se charge
            var mapTimeout = setTimeout(function() {
                if (!window._petrolexMapsInitialized) {
                    showFallbackMap();
                }
            }, 2000);
            
            // Tentative de chargement de l'API Google Maps
            try {
                // Utiliser un nom de callback unique pour éviter les collisions
                var script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMapPetrolex';
                script.async = true;
                script.defer = true;
                
                script.onerror = function() {
                    clearTimeout(mapTimeout);
                    showFallbackMap();
                };
                
                document.head.appendChild(script);
            } catch (error) {
                clearTimeout(mapTimeout);
                console.error('Erreur lors du chargement de Google Maps:', error);
                showFallbackMap();
            }
        });
    </script>
    @endpush
</div>