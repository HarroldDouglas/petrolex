// Configuration globale partagée pour tous les modules de delivery tracking
// Centralise toutes les configurations pour éviter la duplication

const SHARED_CONFIG = {
    // Configuration API centralisée
    API: {
        BASE_URL: 'http://127.0.0.1:8001/api',
        TIMEOUT: 30000,
        RETRY_ATTEMPTS: 3,
        RETRY_DELAY: 1000,
        ENDPOINTS: {
            // Auth endpoints
            LOGIN: '/login',
            LOGOUT: '/logout',
            
            // Customer endpoints
            CUSTOMERS: '/customers',
            MY_ORDERS: '/my/orders',
            
            // Delivery endpoints
            DELIVERY_ORDERS: '/delivery/orders',
            DELIVERY_PERSON_ORDERS: '/delivery-persons/{id}/orders',
            DELIVERY_TRACKING: '/delivery/tracking',
            UPDATE_LOCATION: '/delivery/update-location',
            
            // Tracking endpoints  
            TRACKING_DETAILS: '/tracking/delivery/{orderId}',
            ORDER_STATUS_UPDATE: '/orders/{orderId}/status'
        }
    },

    // Configuration Google Maps (remplace Mapbox)
    GOOGLE_MAPS: {
        API_KEY: 'AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI',
        DEFAULT_CENTER: { lat: 3.848, lng: 11.502 }, // Yaoundé, Cameroun
        DEFAULT_ZOOM: 12,
        // Configuration spécifique pour le Cameroun
        COUNTRY_BOUNDS: {
            north: 13.0,
            south: 1.6,
            east: 16.2,
            west: 8.5
        }
    },

    // Configuration Mapbox (temporaire - à migrer vers Google Maps)
    MAPBOX: {
        ACCESS_TOKEN: 'pk.eyJ1IjoidGVzdCIsImEiOiJjbG5kanQ3emoxa3EzMmpxdXhvZzVuM25tIn0.test', // Token temporaire
        STYLE: 'mapbox://styles/mapbox/streets-v11',
        DEFAULT_CENTER: [11.502, 3.848], // Yaoundé, Cameroun [lng, lat]
        DEFAULT_ZOOM: 12,
        // Mode fallback - utiliser Google Maps si Mapbox échoue
        USE_GOOGLE_MAPS_FALLBACK: true
    },

    // Configuration des statuts de commandes
    ORDER_STATUS: {
        PENDING: 'pending',
        PAID: 'paid',
        PROCESSING: 'processing',
        DELIVERED: 'delivered',
        CANCELLED: 'cancelled',
        
        // Helper methods pour validation
        isTrackable: function(status) { 
            return status === this.PROCESSING; 
        },
        isActive: function(status) { 
            return [this.PAID, this.PROCESSING].includes(status); 
        },
        isFinal: function(status) { 
            return [this.DELIVERED, this.CANCELLED].includes(status); 
        }
    },

    // Labels et couleurs des statuts
    STATUS: {
        TRANSLATIONS: {
            'pending': 'En attente',
            'paid': 'Payée',
            'processing': 'En cours de livraison',
            'delivered': 'Livrée',
            'cancelled': 'Annulée'
        },
        COLORS: {
            'pending': 'warning',
            'paid': 'success',
            'processing': 'primary',
            'delivered': 'info',
            'cancelled': 'danger'
        }
    },

    // Configuration UI
    UI: {
        ANIMATION_DURATION: 1000,
        UPDATE_INTERVAL: 5000, // 5 seconds
        DEFAULT_PAGINATION: 10,
        NOTIFICATION_DURATION: 5000
    },

    // Configuration WebSocket pour Laravel Reverb
    WEBSOCKET: {
        ENABLED: true, // Réactivé avec config Laravel Reverb
        APP_KEY: 'petro-key-12345',
        APP_SECRET: 'petro-secret-67890',
        HOST: '127.0.0.1',  // 🔧 CORRECTION: utiliser 127.0.0.1 comme dans Reverb
        PORT: 8080,
        FORCE_TLS: false,
        ENABLED_TRANSPORTS: ['websocket', 'polling'],
        // Configuration spécifique pour Laravel Reverb
        PUSHER_APP_ID: 'petro-app',
        PUSHER_APP_KEY: 'petro-key-12345',
        PUSHER_APP_SECRET: 'petro-secret-67890',
        PUSHER_APP_CLUSTER: 'mt1'
    },

    // Configuration de géolocalisation
    GEOLOCATION: {
        ENABLED: true,
        HIGH_ACCURACY: true,
        TIMEOUT: 10000,
        MAXIMUM_AGE: 60000,
        UPDATE_INTERVAL: 5000 // 5 secondes pour le tracking en temps réel
    },

    // Configuration de cache
    CACHE: {
        DEFAULT_TTL: 300000, // 5 minutes
        AUTH_TTL: 7200000,   // 2 heures
        ORDERS_TTL: 60000,   // 1 minute
        TRACKING_TTL: 5000   // 5 secondes
    }
};

// Créer des alias pour la compatibilité avec les fichiers existants
const CUSTOMER_CONFIG = {
    ...SHARED_CONFIG,
    // Spécifique au client
    MODULE: 'customer'
};

const DELIVERY_CONFIG = {
    ...SHARED_CONFIG,
    // Spécifique au livreur
    MODULE: 'delivery'
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SHARED_CONFIG, CUSTOMER_CONFIG, DELIVERY_CONFIG };
}

// Rendre disponible globalement
window.SHARED_CONFIG = SHARED_CONFIG;
window.CUSTOMER_CONFIG = CUSTOMER_CONFIG;
window.DELIVERY_CONFIG = DELIVERY_CONFIG;

console.log('🔧 Configuration globale chargée:', {
    apiUrl: SHARED_CONFIG.API.BASE_URL,
    googleMapsKey: SHARED_CONFIG.GOOGLE_MAPS.API_KEY ? '✅ Configuré' : '❌ Manquant',
    websocketPort: SHARED_CONFIG.WEBSOCKET.PORT
});