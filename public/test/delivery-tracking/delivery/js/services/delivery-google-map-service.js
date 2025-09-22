// Service Google Maps pour l'interface livreur
class DeliveryGoogleMapService {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.destinationMarker = null;
        this.routeRenderer = null;
        this.directionsService = null;
        this.fallbackPolyline = null;
        this.initialized = false;
        this.currentPosition = null;
        this.infoWindows = [];
    }

    async initialize(containerId) {
        if (this.initialized) {
            console.warn("Map already initialized");
            return this.map;
        }

        try {
            // Centrer sur Yaoundé par défaut
            const yaounde = { lat: 3.848, lng: 11.502 };

            this.map = new google.maps.Map(document.getElementById(containerId), {
                zoom: 12,
                center: yaounde,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels.text",
                        stylers: [{ visibility: "off" }]
                    }
                ],
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
                zoomControl: true
            });

            // Initialiser les services Google Maps
            this.directionsService = new google.maps.DirectionsService();
            this.routeRenderer = new google.maps.DirectionsRenderer({
                suppressMarkers: true, // On gère nos propres marqueurs
                polylineOptions: {
                    strokeColor: "#28a745",
                    strokeWeight: 5,
                    strokeOpacity: 0.75
                }
            });
            this.routeRenderer.setMap(this.map);

            this.initialized = true;
            console.log("Google Map (Delivery) initialized successfully");

            return this.map;

        } catch (error) {
            console.error("Erreur initialisation Google Maps (Delivery):", error);
            throw error;
        }
    }

    updateDriverPosition(lat, lng, driverInfo = {}) {
        // Supprimer l'ancien marqueur du livreur
        if (this.driverMarker) {
            this.driverMarker.setMap(null);
        }

        // Créer un nouveau marqueur pour le livreur (position actuelle)
        this.driverMarker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: this.map,
            title: `Ma position: ${driverInfo.name || "Livreur"}`,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="#28a745" stroke="white" stroke-width="3"/>
                        <text x="20" y="26" text-anchor="middle" fill="white" font-size="16" font-weight="bold">📍</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(20, 20)
            },
            animation: google.maps.Animation.DROP
        });

        // InfoWindow pour le livreur
        const driverInfoWindow = new google.maps.InfoWindow({
            content: `
                <div style="padding: 8px; max-width: 200px;">
                    <strong>📍 Ma position</strong><br>
                    <span style="color: #666;">${driverInfo.name || "Livreur"}</span><br>
                    <small style="color: #999;">Position actuelle</small>
                </div>
            `
        });

        this.driverMarker.addListener('click', () => {
            this.closeAllInfoWindows();
            driverInfoWindow.open(this.map, this.driverMarker);
            this.infoWindows.push(driverInfoWindow);
        });

        // Centrer la carte sur la position du livreur
        this.map.panTo({ lat: lat, lng: lng });
        this.map.setZoom(14);
        
        // Stocker la position actuelle
        this.currentPosition = { lat, lng };
    }

    setDestination(lat, lng, customerInfo = {}) {
        // Supprimer l'ancien marqueur de destination
        if (this.destinationMarker) {
            this.destinationMarker.setMap(null);
        }

        // Créer un nouveau marqueur pour la destination
        this.destinationMarker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: this.map,
            title: `Destination: ${customerInfo.name || "Client"}`,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="#dc3545" stroke="white" stroke-width="3"/>
                        <text x="20" y="26" text-anchor="middle" fill="white" font-size="16" font-weight="bold">🏠</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(20, 20)
            }
        });

        // InfoWindow pour la destination
        const destinationInfoWindow = new google.maps.InfoWindow({
            content: `
                <div style="padding: 8px; max-width: 250px;">
                    <strong>🏠 Destination</strong><br>
                    <span style="color: #666;">${customerInfo.name || "Client"}</span><br>
                    <small style="color: #999;">${customerInfo.address || "Adresse de livraison"}</small>
                </div>
            `
        });

        this.destinationMarker.addListener('click', () => {
            this.closeAllInfoWindows();
            destinationInfoWindow.open(this.map, this.destinationMarker);
            this.infoWindows.push(destinationInfoWindow);
        });
    }

    async drawRoute(startLat, startLng, endLat, endLng) {
        try {
            const request = {
                origin: { lat: startLat, lng: startLng },
                destination: { lat: endLat, lng: endLng },
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                avoidHighways: false,
                avoidTolls: false
            };

            return new Promise((resolve, reject) => {
                this.directionsService.route(request, (result, status) => {
                    if (status === 'OK') {
                        // Afficher la route
                        this.routeRenderer.setDirections(result);

                        // Obtenir les détails de la route
                        const route = result.routes[0];
                        const leg = route.legs[0];

                        // Ajuster la vue pour inclure toute la route
                        const bounds = new google.maps.LatLngBounds();
                        bounds.extend({ lat: startLat, lng: startLng });
                        bounds.extend({ lat: endLat, lng: endLng });
                        
                        this.map.fitBounds(bounds, {
                            top: 50, bottom: 50, left: 50, right: 50
                        });

                        resolve({
                            duration: leg.duration.value, // en secondes
                            distance: leg.distance.value, // en mètres
                            durationText: leg.duration.text,
                            distanceText: leg.distance.text
                        });
                    } else {
                        console.error('Erreur lors du calcul de la route:', status);
                        // Fallback: tracer une ligne droite
                        console.log('🔄 Utilisation du mode fallback avec ligne droite...');
                        this.drawStraightLine(startLat, startLng, endLat, endLng);
                        
                        // Calculer une estimation basique
                        const distance = this.calculateDistance(startLat, startLng, endLat, endLng);
                        const estimatedDuration = Math.round(distance / 30 * 60); // 30 km/h en ville
                        
                        resolve({
                            duration: estimatedDuration,
                            distance: distance * 1000, // en mètres
                            durationText: `${Math.round(estimatedDuration / 60)} min`,
                            distanceText: `${distance.toFixed(1)} km`
                        });
                    }
                });
            });
        } catch (error) {
            console.error("Erreur lors du tracé de la route:", error);
            // Fallback en cas d'erreur complète
            this.drawStraightLine(startLat, startLng, endLat, endLng);
            return {
                duration: 1800, // 30 minutes par défaut
                distance: 10000, // 10 km par défaut
                durationText: "30 min",
                distanceText: "10.0 km"
            };
        }
    }

    // Méthode fallback pour tracer une ligne droite
    drawStraightLine(startLat, startLng, endLat, endLng) {
        // Supprimer toute route existante
        this.clearRoute();
        
        // Créer une polyligne simple
        const routePath = [
            { lat: startLat, lng: startLng },
            { lat: endLat, lng: endLng }
        ];

        if (this.fallbackPolyline) {
            this.fallbackPolyline.setMap(null);
        }

        this.fallbackPolyline = new google.maps.Polyline({
            path: routePath,
            geodesic: true,
            strokeColor: '#28a745',
            strokeOpacity: 0.75,
            strokeWeight: 5,
            icons: [{
                icon: {
                    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    scale: 3,
                    strokeColor: '#28a745'
                },
                offset: '50%'
            }]
        });

        this.fallbackPolyline.setMap(this.map);

        // Ajuster la vue
        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: startLat, lng: startLng });
        bounds.extend({ lat: endLat, lng: endLng });
        this.map.fitBounds(bounds, {
            top: 80, bottom: 80, left: 80, right: 80
        });

        console.log('📍 Ligne droite tracée entre les deux points');
    }

    // Calculer la distance à vol d'oiseau
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Rayon de la Terre en km
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    toRad(deg) {
        return deg * (Math.PI/180);
    }

    centerOnLocation(lat, lng, zoom = 14) {
        if (this.map) {
            this.map.panTo({ lat: lat, lng: lng });
            this.map.setZoom(zoom);
        }
    }

    clearMarkers() {
        if (this.driverMarker) {
            this.driverMarker.setMap(null);
            this.driverMarker = null;
        }
        if (this.destinationMarker) {
            this.destinationMarker.setMap(null);
            this.destinationMarker = null;
        }
        this.closeAllInfoWindows();
    }

    clearRoute() {
        if (this.routeRenderer) {
            this.routeRenderer.setDirections({ routes: [] });
        }
        if (this.fallbackPolyline) {
            this.fallbackPolyline.setMap(null);
            this.fallbackPolyline = null;
        }
    }

    closeAllInfoWindows() {
        this.infoWindows.forEach(infoWindow => {
            infoWindow.close();
        });
        this.infoWindows = [];
    }

    fitBoundsToMarkers() {
        if (!this.driverMarker && !this.destinationMarker) return;

        const bounds = new google.maps.LatLngBounds();
        
        if (this.driverMarker) {
            bounds.extend(this.driverMarker.getPosition());
        }
        if (this.destinationMarker) {
            bounds.extend(this.destinationMarker.getPosition());
        }

        this.map.fitBounds(bounds, {
            top: 80, bottom: 80, left: 80, right: 80
        });
    }

    getCurrentPosition() {
        return this.currentPosition;
    }

    // Alias pour compatibilité avec delivery-manager.js
    getCurrentGPSPosition() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const coords = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        this.currentPosition = coords;
                        resolve(coords);
                    },
                    (error) => {
                        console.warn('Géolocalisation échouée:', error);
                        // Fallback: utiliser la position par défaut de Yaoundé
                        const fallbackPosition = DELIVERY_CONFIG.GOOGLE_MAPS.DEFAULT_CENTER;
                        this.currentPosition = fallbackPosition;
                        resolve(fallbackPosition);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                console.warn('Géolocalisation non supportée');
                const fallbackPosition = DELIVERY_CONFIG.GOOGLE_MAPS.DEFAULT_CENTER;
                this.currentPosition = fallbackPosition;
                resolve(fallbackPosition);
            }
        });
    }

    destroy() {
        this.clearMarkers();
        this.clearRoute();
        if (this.routeRenderer) {
            this.routeRenderer.setMap(null);
        }
        this.initialized = false;
    }
}