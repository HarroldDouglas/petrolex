// Service pour la gestion de la carte Mapbox du livreur
class DeliveryPersonMapService {
    constructor() {
        this.map = null;
        this.markers = new Map();
        this.layers = new Map();
        this.initialized = false;
        this.currentPosition = null;
        this.mapConfig = {
            style: DELIVERY_CONFIG.MAPBOX.STYLE,
            center: DELIVERY_CONFIG.MAPBOX.DEFAULT_CENTER,
            zoom: DELIVERY_CONFIG.MAPBOX.DEFAULT_ZOOM,
        };
    }

    // Initialisation
    async initialize(containerId) {
        if (this.initialized) {
            console.warn("Map already initialized");
            return this.map;
        }

        return new Promise((resolve, reject) => {
            mapboxgl.accessToken = DELIVERY_CONFIG.MAPBOX.ACCESS_TOKEN;

            this.map = new mapboxgl.Map({
                container: containerId,
                ...this.mapConfig,
            });

            this.map.on("load", () => {
                this.initialized = true;
                console.log("Delivery person map initialized successfully");
                resolve(this.map);
            });

            this.map.on("error", (error) => {
                console.error("Map initialization error:", error);
                reject(error);
            });
        });
    }

    // Gestion des marqueurs optimisée
    updateDriverPosition(lat, lng, popupContent = null, speed = null, shouldUpdateRoute = true) {
        const markerId = "driver";
        this.removeMarker(markerId);

        const marker = this.createMarker({
            id: markerId,
            color: "#1E88E5",
            coordinates: [lng, lat],
            popupContent:
                popupContent || this.getDefaultDriverPopup(lat, lng, speed),
        });

        this.markers.set(markerId, marker);
        this.currentPosition = { lat, lng };

        // Centrer la carte sur le conducteur
        this.map.setCenter([lng, lat]);

        // 🔧 CORRECTION: Redessiner la route depuis la nouvelle position si une destination existe
        if (shouldUpdateRoute && this.getDestinationPosition()) {
            const destination = this.getDestinationPosition();
            this.updateRouteFromCurrentPosition(destination);
        }

        return marker;
    }

    setDestination(lat, lng, info = {}) {
        const markerId = "destination";
        this.removeMarker(markerId);

        const marker = this.createMarker({
            id: markerId,
            color: "#E53935",
            coordinates: [lng, lat],
            popupContent: this.buildDestinationPopup(info),
        });

        this.markers.set(markerId, marker);
        this.destinationPosition = { lat, lng }; // 🔧 AJOUT: Stocker la position de destination
        return marker;
    }

    // 🔧 NOUVELLE MÉTHODE: Redessiner la route depuis la position actuelle
    async updateRouteFromCurrentPosition(destination) {
        if (!this.currentPosition || !destination) {
            return;
        }

        const routeId = "delivery-route";
        
        try {
            // Supprimer l'ancienne route
            await this.removeRoute(routeId);
            
            // Attendre un peu pour s'assurer que la suppression est terminée
            await new Promise(resolve => setTimeout(resolve, 50));
            
            // Calculer la nouvelle route depuis la position actuelle
            const routeData = await this.fetchRouteData(
                { lat: this.currentPosition.lat, lng: this.currentPosition.lng },
                { lat: destination.lat, lng: destination.lng },
                "driving"
            );
            
            if (routeData) {
                this.addRouteToMap(routeId, routeData.geometry);
                console.log(`🔄 Route mise à jour depuis position actuelle: ${this.currentPosition.lat}, ${this.currentPosition.lng}`);
            }
            
        } catch (error) {
            console.error("Erreur lors de la mise à jour de la route:", error);
        }
    }

    // 🔧 NOUVELLE MÉTHODE: Récupérer la position de destination
    getDestinationPosition() {
        return this.destinationPosition || null;
    }

    createMarker({ id, color, coordinates, popupContent }) {
        const marker = new mapboxgl.Marker({ color }).setLngLat(coordinates);

        if (popupContent) {
            marker.setPopup(new mapboxgl.Popup().setHTML(popupContent));
        }

        marker.addTo(this.map);
        return marker;
    }

    removeMarker(markerId) {
        if (this.markers.has(markerId)) {
            this.markers.get(markerId).remove();
            this.markers.delete(markerId);
        }
    }

    // Gestion des routes
    async drawRoute(startCoords, endCoords, transportMode = "driving") {
        const routeId = "delivery-route";
        
        try {
            // CORRECTION : S'assurer que la suppression est terminée avant d'ajouter une nouvelle route
            await this.removeRoute(routeId);
            
            // Une courte pause pour s'assurer que Mapbox a bien enregistré la suppression
            await new Promise(resolve => setTimeout(resolve, 50));
            
            const routeData = await this.fetchRouteData(
                startCoords,
                endCoords,
                transportMode,
            );
            if (!routeData) return null;

            this.addRouteToMap(routeId, routeData.geometry);
            
            // AJOUT: Ajouter explicitement les marqueurs après avoir dessiné la route
            this.updateDriverPosition(startCoords.lat, startCoords.lng, "Position de départ");
            this.setDestination(endCoords.lat, endCoords.lng, {
                customer: "Client",
                address: "Adresse de livraison"
            });
            
            this.fitBounds(routeData.geometry.coordinates);

            return {
                duration: Math.round(routeData.duration / 60),
                distance: (routeData.distance / 1000).toFixed(1),
                geometry: routeData.geometry,
            };
        } catch (error) {
            console.error("Error drawing route:", error);
            return null;
        }
    }

    async fetchRouteData(startCoords, endCoords, transportMode) {
        const profile =
            DELIVERY_CONFIG.SIMULATION.TRANSPORT_MODES[transportMode]?.mapboxProfile ||
            "driving";
        const url = `https://api.mapbox.com/directions/v5/mapbox/${profile}/${startCoords.lng},${startCoords.lat};${endCoords.lng},${endCoords.lat}?geometries=geojson&access_token=${DELIVERY_CONFIG.MAPBOX.ACCESS_TOKEN}`;

        const response = await fetch(url);
        const result = await response.json();

        return result.routes?.[0] || null;
    }

    addRouteToMap(routeId, geometry) {
        const sourceId = `${routeId}-source`;
        const layerId = `${routeId}-layer`;

        // CORRECTION : Vérifier d'abord si la source existe déjà et la supprimer si nécessaire
        if (this.map.getSource(sourceId)) {
            // Si une source avec cet ID existe déjà, il faut d'abord supprimer sa couche
            if (this.map.getLayer(layerId)) {
                this.map.removeLayer(layerId);
            }
            this.map.removeSource(sourceId);
            console.log(`Source existante ${sourceId} supprimée avant l'ajout`);
        }

        this.map.addSource(sourceId, {
            type: "geojson",
            data: {
                type: "Feature",
                properties: {},
                geometry: geometry,
            },
        });

        this.map.addLayer({
            id: layerId,
            type: "line",
            source: sourceId,
            layout: {
                "line-join": "round",
                "line-cap": "round",
            },
            paint: {
                "line-color": "#1E88E5",
                "line-width": 4,
                "line-opacity": 0.8,
            },
        });

        this.layers.set(routeId, { sourceId, layerId });
    }

    async removeRoute(routeId) {
        if (this.layers.has(routeId)) {
            const { sourceId, layerId } = this.layers.get(routeId);
            
            return new Promise(resolve => {
                try {
                    // Supprimer la couche si elle existe
                    if (this.map.getLayer(layerId)) {
                        this.map.removeLayer(layerId);
                        console.log(`Layer ${layerId} supprimée avec succès`);
                    }
                    
                    // Supprimer la source si elle existe
                    if (this.map.getSource(sourceId)) {
                        this.map.removeSource(sourceId);
                        console.log(`Source ${sourceId} supprimée avec succès`);
                    }
                    
                    this.layers.delete(routeId);
                } catch (error) {
                    console.warn(`Erreur lors de la suppression de la route ${routeId}:`, error);
                }
                
                // Résoudre la promesse après une courte pause pour s'assurer que Mapbox a bien enregistré les modifications
                setTimeout(resolve, 10);
            });
        }
        return Promise.resolve();
    }

    // Utilitaires
    fitBounds(coordinates) {
        if (coordinates?.length > 0) {
            const bounds = new mapboxgl.LngLatBounds();
            coordinates.forEach((coord) => bounds.extend(coord));
            this.map.fitBounds(bounds, { padding: 50 });
        }
    }

    async getCurrentGPSPosition() {
        return new Promise((resolve) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) =>
                        resolve({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        }),
                    () => resolve(this.getDefaultPosition()),
                );
            } else {
                resolve(this.getDefaultPosition());
            }
        });
    }

    getDefaultPosition() {
        return {
            lat: DELIVERY_CONFIG.MAPBOX.DEFAULT_CENTER[1],
            lng: DELIVERY_CONFIG.MAPBOX.DEFAULT_CENTER[0],
        };
    }

    getCurrentPosition() {
        return this.currentPosition;
    }

    // Popups
    getDefaultDriverPopup(lat, lng, speed) {
        return `
            <strong>Position actuelle</strong><br>
            Coordonnées: ${lat.toFixed(4)}, ${lng.toFixed(4)}<br>
            ${speed ? `Vitesse: ${speed} km/h` : ""}
        `;
    }

    buildDestinationPopup(info) {
        return `
            <strong>Destination</strong><br>
            ${info.customer || "Client"}<br>
            ${info.address || "Adresse de livraison"}<br>
            ${info.phone ? `📞 ${info.phone}` : ""}
        `;
    }

    // Nettoyage
    destroy() {
        this.markers.forEach((marker) => marker.remove());
        this.markers.clear();
        this.layers.clear();

        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        this.initialized = false;
        this.currentPosition = null;
    }
}
