// Configuration globale partagée pour tous les modules de delivery tracking
// Centralise toutes les configurations pour éviter la duplication

// 🌍 Auto-détection de l'environnement Production/Développement
const isProduction = () => {
    const hostname = window.location.hostname;
    return hostname !== 'localhost' && hostname !== '127.0.0.1' && !hostname.includes('test');
};

const getEnvironmentConfig = () => {
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;
    const isSSL = protocol === 'https:';
    
    if (isProduction()) {
        console.log('🌍 [Config] Production environment detected:', hostname);
        return {
            HOST: hostname,
            API_BASE_URL: `${protocol}//${hostname}/api`,
            WS_HOST: hostname,
            WS_PORT: (typeof window !== "undefined" && window.location.protocol === "https:" ? 443 : 8080),
            FORCE_TLS: isSSL
        };
    } else {
        console.log('🛠️ [Config] Development environment detected');
        return {
            HOST: '127.0.0.1',
            API_BASE_URL: 'http://127.0.0.1:8001/api',
            WS_HOST: '127.0.0.1',
            WS_PORT: (typeof window !== "undefined" && window.location.protocol === "https:" ? 443 : 8080),
            FORCE_TLS: false
        };
    }
};

const ENV_CONFIG = getEnvironmentConfig();

const SHARED_CONFIG = {
    // Configuration API centralisée
    API: {
        BASE_URL: ENV_CONFIG.API_BASE_URL,
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
        HOST: ENV_CONFIG.WS_HOST,  // 🌍 Auto-détection production/dev
        PORT: ENV_CONFIG.WS_PORT,
        FORCE_TLS: ENV_CONFIG.FORCE_TLS,
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