// Configuration globale pour le système de tracking réel
const CONFIG = {
    // API Configuration
    API: {
        BASE_URL: 'http://127.0.0.1:8000/api',
        ENDPOINTS: {
            // Auth
            LOGIN: '/login',
            
            // Customer endpoints
            CUSTOMERS: '/customers',
            CUSTOMER_ORDERS: '/customers/{id}/orders',
            
            // Delivery person endpoints  
            DELIVERY_PERSON_ORDERS: '/delivery-persons/{id}/orders',
            
            // Tracking endpoints (updated)
            TRACKING_ACTIVE: '/tracking/delivery/active',
            TRACKING_START: '/tracking/delivery/{orderNumber}/start',
            TRACKING_POSITION: '/tracking/delivery/{orderNumber}/position',
            TRACKING_DETAILS: '/tracking/delivery/{orderNumber}'
        }
    },

    // Mapbox Configuration
    MAPBOX: {
        ACCESS_TOKEN: 'pk.eyJ1IjoiaGFycm9sZHdhZm8iLCJhIjoiY21kcjkwenJxMGVtYzJsczY0aXgzbGN6OCJ9.WGRlvNUJFaEFbmvoeTTGlQ',
        STYLE: 'mapbox://styles/mapbox/streets-v11',
        DEFAULT_CENTER: [11.502, 3.848], // Yaoundé, Cameroun (lng, lat)
        DEFAULT_ZOOM: 12
    },

    // Order Status Configuration (matching backend)
    ORDER_STATUS: {
        CONFIRMED: 'confirmed',
        PROCESSING: 'in_progress', 
        DELIVERED: 'delivered',
        CANCELLED: 'cancelled',
        PENDING: 'pending'
    },

    // Status Labels and Colors
    STATUS: {
        TRANSLATIONS: {
            'confirmed': 'Confirmée',
            'in_progress': 'En cours de livraison',
            'delivered': 'Livrée',
            'cancelled': 'Annulée',
            'pending': 'En attente'
        },
        COLORS: {
            'confirmed': 'dark',
            'in_progress': 'primary',
            'delivered': 'success',
            'cancelled': 'danger', 
            'pending': 'warning'
        }
    },

    // Simulation Configuration
    SIMULATION: {
        MIN_SPEED: 1,
        MAX_SPEED: 10,
        DEFAULT_SPEED: 3,
        BASE_INTERVAL: 1000,
        ROUTE_STEP_MULTIPLIER: 2,
        POSITION_UPDATE_INTERVAL: 10000, // 10 seconds
        TRANSPORT_MODES: {
            walking: {
                name: 'À pied',
                icon: '🚶‍♂️',
                speedMultiplier: 1,
                maxSpeed: 5,
                mapboxProfile: 'walking',
                description: 'Plus lent mais précis'
            },
            driving: {
                name: 'À moto',
                icon: '🏍️',
                speedMultiplier: 3,
                maxSpeed: 15,
                mapboxProfile: 'driving',
                description: 'Rapide et efficace'
            }
        }
    },

    // UI Configuration
    UI: {
        ANIMATION_DURATION: 1000,
        PROGRESS_UPDATE_INTERVAL: 500,
        MAP_UPDATE_INTERVAL: 1000,
        DEFAULT_PAGINATION: 10
    },

    // WebSocket Configuration
    WEBSOCKET: {
        APP_KEY: 'local',
        HOST: '127.0.0.1',
        PORT: 8080,
        FORCE_TLS: false,
        ENABLED_TRANSPORTS: ['ws', 'wss']
    }
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}