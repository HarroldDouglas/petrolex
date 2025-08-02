// Service pour la gestion de la carte Mapbox du livreur
class DeliveryPersonMapService {
    constructor() {
        this.map = null;
        this.markers = new Map();
        this.layers = new Map();
        this.initialized = false;
        this.currentPosition = null;
        this.mapConfig = {
            style: CONFIG.MAPBOX.STYLE,
            center: CONFIG.MAPBOX.DEFAULT_CENTER,
            zoom: CONFIG.MAPBOX.DEFAULT_ZOOM,
        };
    }

    // Initialisation
    async initialize(containerId) {
        if (this.initialized) {
            console.warn("Map already initialized");
            return this.map;
        }

        return new Promise((resolve, reject) => {
            mapboxgl.accessToken = CONFIG.MAPBOX.ACCESS_TOKEN;

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
    updateDriverPosition(lat, lng, popupContent = null, speed = null) {
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
        return marker;
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
        await this.removeRoute(routeId);

        try {
            const routeData = await this.fetchRouteData(
                startCoords,
                endCoords,
                transportMode,
            );
            if (!routeData) return null;

            this.addRouteToMap(routeId, routeData.geometry);
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
            CONFIG.SIMULATION.TRANSPORT_MODES[transportMode]?.mapboxProfile ||
            "driving";
        const url = `https://api.mapbox.com/directions/v5/mapbox/${profile}/${startCoords.lng},${startCoords.lat};${endCoords.lng},${endCoords.lat}?geometries=geojson&access_token=${CONFIG.MAPBOX.ACCESS_TOKEN}`;

        const response = await fetch(url);
        const result = await response.json();

        return result.routes?.[0] || null;
    }

    addRouteToMap(routeId, geometry) {
        const sourceId = `${routeId}-source`;
        const layerId = `${routeId}-layer`;

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

            if (this.map.getLayer(layerId)) {
                this.map.removeLayer(layerId);
            }
            if (this.map.getSource(sourceId)) {
                this.map.removeSource(sourceId);
            }

            this.layers.delete(routeId);
        }
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
            lat: CONFIG.MAPBOX.DEFAULT_CENTER[1],
            lng: CONFIG.MAPBOX.DEFAULT_CENTER[0],
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
