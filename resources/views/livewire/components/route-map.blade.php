<div>
    <div class="row justify-content-center">
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
            
            <!-- Informations de l'itinéraire  -->
            <div class="route-info mb-3" id="route-info-panel">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-ruler"></i> Distance:</strong> <span id="distance-text"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-clock"></i> Durée estimée:</strong> <span id="duration-text"></span></p>
                    </div>
                </div>
            </div>

            <div class="controls">
                <div class="mb-1">
                    <label for="start-point" class="form-label">Départ:</label>
                    <input type="text" id="start-point" placeholder="Entrer le lieux de départ" class="form-control">
                </div>
                <div class="mb-1">
                    <label for="end-point" class="form-label">Destination:</label>
                    <input type="text" id="end-point" placeholder="Entrer le lieux de destination" class="form-control">
                </div>
                <button class="btn btn-info" onclick="calculateRoute()">Itinéraire</button>
            </div>
            <div id="map"></div>
            <div id="route-info" class="route-info" style="display:none;"></div>

        </div>
    </div>

    @push('styles')
    <style>
        #map { height: 400px; }
        .controls { 
            width: 350px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px; 
        }
        #route-info { 
            margin-top: 10px; 
            border: 1px solid #ccc; 
            padding: 10px; }
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
        var map = L.map('map');

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        map.locate({setView: true, maxZoom: 16});

        function onLocationFound(e) {
            L.marker(e.latlng).addTo(map)
                .bindPopup("You are here").openPopup();
            map.setView(e.latlng, 13);
        }

        function onLocationError(err) {
            console.warn(err.message);
            map.setView([3.87731485497796, 11.546855378375056], 12);
            alert("Could not determine your location.");
        }

        map.on('locationfound', onLocationFound);
        map.on('locationerror', onLocationError);

        var routeLayer;

        async function geocode(address) {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=jsonv2`);
            const data = await response.json();
            if (data && data.length > 0) {
                return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
            }
            return null;
        }

        async function calculateRoute() {
            const startAddress = document.getElementById('start-point').value;
            const endAddress = document.getElementById('end-point').value;

            const startCoords = await geocode(startAddress);
            const endCoords = await geocode(endAddress);

            if (!startCoords || !endCoords) {
                alert("Could not find coordinates for one or both locations.");
                return;
            }

            const routingUrl = `https://router.project-osrm.org/route/v1/driving/${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}?overview=full&geometries=geojson`;

            const response = await fetch(routingUrl);
            const data = await response.json();

            if (data && data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const geometry = route.geometry;
                const distance = route.distance / 1000; // in km
                const duration = route.duration / 60; // in minutes

                if (routeLayer) {
                    map.removeLayer(routeLayer);
                }
                routeLayer = L.geoJSON(geometry, {
                    style: {
                        color: 'red',
                        weight: 5,
                        opacity: 0.7
                    }
                }).addTo(map);

                const routeInfoDiv = document.getElementById('route-info');
                const distanceText = document.getElementById('distance-text');
                const durationText = document.getElementById('duration-text');
                distanceText.textContent = `${distance.toFixed(2)} km`;
                durationText.textContent = `${Math.floor(duration)} minutes`;
                routeInfoDiv.innerHTML = `Distance: ${distance.toFixed(2)} km<br>Estimated Time: ${Math.floor(duration)} minutes`;
                routeInfoDiv.style.display = 'block';

                // Fit the map to the route bounds
                const bounds = routeLayer.getBounds();
                map.fitBounds(bounds);

            } else {
                alert("Could not find a route between the specified points.");
            }
        }
        
    </script>
    @endpush
</div>