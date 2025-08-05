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
            console.warn("Map already initialized");
            return;
        }

        mapboxgl.accessToken = CUSTOMER_CONFIG.MAPBOX.ACCESS_TOKEN;

        this.map = new mapboxgl.Map({
            container: containerId,
            style: CUSTOMER_CONFIG.MAPBOX.STYLE,
            center: CUSTOMER_CONFIG.MAPBOX.DEFAULT_CENTER,
            zoom: CUSTOMER_CONFIG.MAPBOX.DEFAULT_ZOOM,
        });

        this.map.on("load", () => {
            this.addRouteSource();
            this.initialized = true;
        });

        this.map.on("error", (e) => {
            console.error("Map error:", e);
        });

        return this.map;
    }

    addRouteSource() {
        if (!this.map.getSource("route")) {
            this.map.addSource("route", {
                type: "geojson",
                data: {
                    type: "Feature",
                    properties: {},
                    geometry: {
                        type: "LineString",
                        coordinates: [],
                    },
                },
            });

            this.map.addLayer({
                id: "route",
                type: "line",
                source: "route",
                layout: {
                    "line-join": "round",
                    "line-cap": "round",
                },
                paint: {
                    "line-color": "#3887be",
                    "line-width": 5,
                    "line-opacity": 0.75,
                },
            });
        }
    }

    updateDriverPosition(lat, lng, driverInfo = {}) {
        if (this.driverMarker) {
            this.driverMarker.remove();
        }

        this.driverMarker = new mapboxgl.Marker({ color: "#1E88E5" })
            .setLngLat([lng, lat])
            .setPopup(
                new mapboxgl.Popup().setHTML(`
                <strong>Livreur</strong><br>
                ${driverInfo.name || "En cours..."}<br>
                Position actuelle
            `),
            )
            .addTo(this.map);

        this.map.flyTo({
            center: [lng, lat],
            zoom: 14,
            duration: CUSTOMER_CONFIG.UI.ANIMATION_DURATION,
        });
    }

    setDestination(lat, lng, customerInfo = {}) {
        if (this.destinationMarker) {
            this.destinationMarker.remove();
        }

        this.destinationMarker = new mapboxgl.Marker({ color: "#E53935" })
            .setLngLat([lng, lat])
            .setPopup(
                new mapboxgl.Popup().setHTML(`
                <strong>Destination</strong><br>
                ${customerInfo.name || "Client"}<br>
                ${customerInfo.address || "Adresse de livraison"}
            `),
            )
            .addTo(this.map);
    }

    async drawRoute(start, end) {
        try {
            const query = await fetch(
                `https://api.mapbox.com/directions/v5/mapbox/driving/${start[0]},${start[1]};${end[0]},${end[1]}?steps=true&geometries=geojson&access_token=${CUSTOMER_CONFIG.MAPBOX.ACCESS_TOKEN}`,
            );
            const json = await query.json();

            if (json.routes && json.routes.length > 0) {
                const route = json.routes[0];

                if (this.map.getSource("route")) {
                    this.map.getSource("route").setData({
                        type: "Feature",
                        properties: {},
                        geometry: route.geometry,
                    });
                } else {
                    this.addRouteSource();

                    if (this.map.getSource("route")) {
                        this.map.getSource("route").setData({
                            type: "Feature",
                            properties: {},
                            geometry: route.geometry,
                        });
                    }
                }

                const bounds = new mapboxgl.LngLatBounds();
                bounds.extend([start[0], start[1]]);
                bounds.extend([end[0], end[1]]);

                this.map.fitBounds(bounds, {
                    padding: { top: 50, bottom: 50, left: 50, right: 50 },
                    duration: CUSTOMER_CONFIG.UI.ANIMATION_DURATION,
                });

                return {
                    duration: route.duration,
                    distance: route.distance,
                };
            }
        } catch (error) {
            console.error("Erreur lors du tracé de la route:", error);
            return null;
        }
    }

    centerOnLocation(lat, lng, zoom = 14) {
        if (this.map) {
            this.map.flyTo({
                center: [lng, lat],
                zoom: zoom,
                duration: CUSTOMER_CONFIG.UI.ANIMATION_DURATION,
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
