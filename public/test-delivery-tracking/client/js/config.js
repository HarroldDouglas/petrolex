// Configuration globale pour la simulation client
const CONFIG = {
    // API Configuration
    API: {
        BASE_URL: 'http://127.0.0.1:8000/api',
        ENDPOINTS: {
            DELIVERY_ACTIVE: '/test/delivery/active',
            DELIVERY_GET: '/test/delivery',
            DELIVERY_CREATE: '/test/delivery'
        }
    },

    // Mapbox Configuration
    MAPBOX: {
        ACCESS_TOKEN: 'pk.eyJ1IjoiaGFycm9sZHdhZm8iLCJhIjoiY21kcjkwenJxMGVtYzJsczY0aXgzbGN6OCJ9.WGRlvNUJFaEFbmvoeTTGlQ',
        STYLE: 'mapbox://styles/mapbox/streets-v11',
        DEFAULT_CENTER: [2.3522, 48.8566], // Paris
        DEFAULT_ZOOM: 12
    },

    // WebSocket Configuration
    WEBSOCKET: {
        PUSHER_APP_KEY: 'local',
        PUSHER_HOST: '127.0.0.1',
        PUSHER_PORT: 8080,
        PUSHER_FORCE_TLS: false,
        ENABLED_TRANSPORTS: ['ws', 'wss'],
        CLUSTER: 'mt1'
    },

    // UI Configuration
    UI: {
        ANIMATION_DURATION: 1000,
        REFRESH_INTERVAL: 5000,
        HISTORY_MAX_ITEMS: 20
    },

    // Status Configuration
    STATUS: {
        TRANSLATIONS: {
            'pending': 'En attente',
            'assigned': 'Livreur assigné',
            'picked_up': 'Récupérée',
            'in_transit': 'En cours de livraison',
            'delivered': 'Livrée',
            'cancelled': 'Annulée'
        },
        PROGRESS: {
            'pending': 10,
            'assigned': 25,
            'picked_up': 50,
            'in_transit': 75,
            'delivered': 100,
            'cancelled': 0
        },
        BADGE_COLORS: {
            'pending': 'secondary',
            'assigned': 'primary',
            'picked_up': 'warning',
            'in_transit': 'info',
            'delivered': 'success',
            'cancelled': 'danger'
        }
    }
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}