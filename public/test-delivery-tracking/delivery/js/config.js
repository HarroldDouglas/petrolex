// Configuration globale pour la simulation livreur
const CONFIG = {
    // API Configuration
    API: {
        BASE_URL: 'http://127.0.0.1:8000/api',
        ENDPOINTS: {
            DELIVERY_ACTIVE: '/test/delivery/active',
            DELIVERY_CREATE: '/test/delivery',
            DELIVERY_GET: '/test/delivery/{id}',
            DELIVERY_START: '/test/delivery/{id}/start',
            DELIVERY_POSITION: '/test/delivery/{id}/position',
            DELIVERY_STATUS: '/test/delivery/{id}/status'
        }
    },

    // Mapbox Configuration
    MAPBOX: {
        ACCESS_TOKEN: 'pk.eyJ1IjoiaGFycm9sZHdhZm8iLCJhIjoiY21kcjkwenJxMGVtYzJsczY0aXgzbGN6OCJ9.WGRlvNUJFaEFbmvoeTTGlQ',
        STYLE: 'mapbox://styles/mapbox/streets-v11',
        DEFAULT_CENTER: [2.3522, 48.8566], // Paris
        DEFAULT_ZOOM: 12
    },

    // Simulation Configuration
    SIMULATION: {
        MIN_SPEED: 1,
        MAX_SPEED: 10,
        DEFAULT_SPEED: 3,
        BASE_INTERVAL: 1000, // milliseconds
        ROUTE_STEP_MULTIPLIER: 2,
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

    // Driver Configuration
    DRIVER: {
        DEFAULT_NAME: 'Michel Dupont',
        DEFAULT_PHONE: '+33612345678',
        DEFAULT_POSITION: {
            lat: 48.8566,
            lng: 2.3522
        }
    },

    // Delivery Configuration
    DELIVERY: {
        DEFAULT_CUSTOMER: 'Jean Martin',
        DEFAULT_ADDRESS: 'Tour Eiffel, Paris',
        DEFAULT_DESTINATION: {
            lat: 48.8584,
            lng: 2.2945
        }
    },

    // Status Configuration
    STATUS: {
        TRANSLATIONS: {
            'pending': 'En attente',
            'assigned': 'Assignée',
            'started': 'Démarrée',
            'in_progress': 'En cours',
            'delivered': 'Livrée',
            'cancelled': 'Annulée'
        },
        COLORS: {
            'pending': 'secondary',
            'assigned': 'primary',
            'started': 'warning',
            'in_progress': 'info',
            'delivered': 'success',
            'cancelled': 'danger'
        },
        DRIVER_STATES: {
            'FREE': 'Libre',
            'ASSIGNED': 'Assigné',
            'DELIVERING': 'En livraison',
            'OFFLINE': 'Hors ligne'
        }
    },

    // UI Configuration
    UI: {
        ANIMATION_DURATION: 1000,
        PROGRESS_UPDATE_INTERVAL: 500,
        MAP_UPDATE_INTERVAL: 1000
    }
};

// Export pour utilisation dans d'autres modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}