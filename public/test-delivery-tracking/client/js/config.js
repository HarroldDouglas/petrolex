// Configuration globale pour l'interface client avec vraies données
const CONFIG = {
    // API Configuration
    API: {
        BASE_URL: 'http://127.0.0.1:8000/api',
        ENDPOINTS: {
            // Auth endpoints
            LOGIN: '/login',
            LOGOUT: '/logout',
            
            // Customer endpoints (vraies routes API)
            CUSTOMERS: '/customers',
            CUSTOMER_ORDERS: '/customers/{id}/orders',
            
            // Tracking endpoints  
            TRACKING_DETAILS: '/tracking/delivery/{orderId}'
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

    // UI Configuration
    UI: {
        ANIMATION_DURATION: 1000,
        UPDATE_INTERVAL: 5000, // 5 seconds
        DEFAULT_PAGINATION: 10
    },

    // WebSocket Configuration pour Laravel Reverb
    WEBSOCKET: {
        APP_KEY: 'your-app-key', // Doit correspondre à REVERB_APP_KEY dans .env
        HOST: '127.0.0.1',
        PORT: 8080,
        FORCE_TLS: false,
        ENABLED_TRANSPORTS: ['ws', 'wss'],
        // Configuration spécifique pour Laravel Reverb
        PUSHER_APP_ID: 'your-app-id',
        PUSHER_APP_KEY: 'your-app-key',
        PUSHER_APP_SECRET: 'your-app-secret',
        PUSHER_APP_CLUSTER: 'mt1'
    }
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}