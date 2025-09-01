const CUSTOMER_CONFIG = {
    API: {
        BASE_URL: 'http://127.0.0.1:8000/api',
        TIMEOUT: 30000,
        RETRY_ATTEMPTS: 3,
        RETRY_DELAY: 1000,
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

    ORDER_STATUS: {
        PENDING: 'pending',
        CONFIRMED: 'confirmed',
        IN_PROGRESS: 'in_progress',
        DELIVERED: 'delivered',
        CANCELLED: 'cancelled',
        
        // Helper methods pour validation
        isTrackable: function(status) { 
            return status === this.IN_PROGRESS; 
        },
        isActive: function(status) { 
            return [this.CONFIRMED, this.IN_PROGRESS].includes(status); 
        },
        isFinal: function(status) { 
            return [this.DELIVERED, this.CANCELLED].includes(status); 
        }
    },

    // Status Labels and Colors
    STATUS: {
        TRANSLATIONS: {
            'pending': 'En attente',
            'confirmed': 'Confirmée',
            'in_progress': 'En cours de livraison',
            'delivered': 'Livrée',
            'cancelled': 'Annulée'
        },
        COLORS: {
            'pending': 'warning',
            'confirmed': 'dark',
            'in_progress': 'primary',
            'delivered': 'success',
            'cancelled': 'danger'
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
        ENABLED: true,
        APP_KEY: 'local-key', // Clé Reverb correcte
        APP_SECRET: 'local-secret',
        HOST: '127.0.0.1',
        PORT: 8080,
        FORCE_TLS: false,
        ENABLED_TRANSPORTS: ['websocket', 'polling'],
        // Configuration spécifique pour Laravel Reverb
        PUSHER_APP_ID: 'local',
        PUSHER_APP_KEY: 'local-key',
        PUSHER_APP_SECRET: 'local-secret',
        PUSHER_APP_CLUSTER: 'mt1'
    }
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CUSTOMER_CONFIG;
}