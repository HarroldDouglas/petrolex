const DELIVERY_CONFIG = {
    API: {
        BASE_URL: "http://127.0.0.1:8000/api",
        ENDPOINTS: {
            // Auth
            LOGIN: "/login",
            AUTH_CHECK: "/auth/check",

            // Customer endpoints
            CUSTOMERS: "/customers",
            CUSTOMER_ORDERS: "/customers/{id}/orders",

            // Delivery person endpoints
            DELIVERY_PERSON_ORDERS: "/delivery-persons/{id}/orders",

            // Tracking endpoints (standardisés avec orderId)
            TRACKING_START: "/tracking/delivery/{orderId}/start",
            TRACKING_POSITION: "/tracking/delivery/{orderId}/position",
            TRACKING_DETAILS: "/tracking/delivery/{orderId}",
        },
    },

    // Mapbox Configuration
    MAPBOX: {
        ACCESS_TOKEN: "pk.eyJ1IjoiaGFycm9sZHdhZm8iLCJhIjoiY21kcjkwenJxMGVtYzJsczY0aXgzbGN6OCJ9.WGRlvNUJFaEFbmvoeTTGlQ",
        STYLE: "mapbox://styles/mapbox/streets-v11",
        DEFAULT_CENTER: [11.502, 3.848], // Yaoundé, Cameroun (lng, lat)
        DEFAULT_ZOOM: 12,
    },

    ORDER_STATUS: {
        // Statuts principaux
        PENDING: "pending",
        CONFIRMED: "confirmed",
        IN_PROGRESS: "in_progress",
        DELIVERED: "delivered",
        CANCELLED: "cancelled",

        // Helper methods pour validation
        isTrackable: function (status) {
            return status === this.IN_PROGRESS;
        },
        isActive: function (status) {
            return [this.CONFIRMED, this.IN_PROGRESS].includes(status);
        },
        isFinal: function (status) {
            return [this.DELIVERED, this.CANCELLED].includes(status);
        },
    },

    // Status Labels and Colors
    STATUS: {
        TRANSLATIONS: {
            pending: "En attente",
            confirmed: "Confirmée",
            in_progress: "En cours de livraison",
            delivered: "Livrée",
            cancelled: "Annulée",
        },
        COLORS: {
            pending: "warning",
            confirmed: "dark",
            in_progress: "primary",
            delivered: "success",
            cancelled: "danger",
        },
    },

    // Simulation Configuration
    SIMULATION: {
        MIN_SPEED: 1,
        MAX_SPEED: 10,
        DEFAULT_SPEED: 3,
        BASE_INTERVAL: 1000,
        ROUTE_STEP_MULTIPLIER: 20,
        POSITION_UPDATE_INTERVAL: 10000, // 10 seconds
        TRANSPORT_MODES: {
            walking: {
                name: "À pied",
                icon: "🚶‍♂️",
                speedMultiplier: 1,
                maxSpeed: 5,
                mapboxProfile: "walking",
                description: "Plus lent mais précis",
            },
            driving: {
                name: "À moto",
                icon: "🏍️",
                speedMultiplier: 3,
                maxSpeed: 15,
                mapboxProfile: "driving",
                description: "Rapide et efficace",
            },
        },
    },

    // UI Configuration
    UI: {
        ANIMATION_DURATION: 1000,
        PROGRESS_UPDATE_INTERVAL: 500,
        MAP_UPDATE_INTERVAL: 1000,
        DEFAULT_PAGINATION: 10,
    },

    // WebSocket Configuration
    WEBSOCKET: {
        ENABLED: true,
        APP_KEY: "local",
        HOST: "127.0.0.1",
        PORT: 8080,
        FORCE_TLS: false,
        ENABLED_TRANSPORTS: ["ws", "wss"],
    },
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DELIVERY_CONFIG;
}