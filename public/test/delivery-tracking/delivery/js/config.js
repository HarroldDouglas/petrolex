// Delivery interface configuration
// Uses shared configuration

// Wait for SHARED_CONFIG to load
if (typeof SHARED_CONFIG !== 'undefined') {
    // Extend shared config instead of redeclaring DELIVERY_CONFIG
    if (typeof DELIVERY_CONFIG === 'undefined') {
        window.DELIVERY_CONFIG = {
            ...SHARED_CONFIG,
            // Delivery-specific settings
            MODULE: 'delivery',
            
            // Default values for testing
            DEFAULT_CREDENTIALS: {
                email: 'delivery1@test.com',
                password: 'password'
            },
            
            // Simulation configuration
            SIMULATION: {
                POSITION_UPDATE_INTERVAL: 2000, // 2 seconds
                DEFAULT_SPEED_KMH: 40,
                MIN_SPEED_KMH: 10,
                MAX_SPEED_KMH: 80
            }
        };
    } else {
        // If DELIVERY_CONFIG already exists, just add our specific settings
        Object.assign(DELIVERY_CONFIG, {
            MODULE: 'delivery',
            DEFAULT_CREDENTIALS: {
                email: 'delivery1@test.com',
                password: 'password'
            },
            SIMULATION: {
                POSITION_UPDATE_INTERVAL: 2000, // 2 seconds
                DEFAULT_SPEED_KMH: 40,
                MIN_SPEED_KMH: 10,
                MAX_SPEED_KMH: 80
            }
        });
    }

    console.log('🚚 Delivery configuration loaded:', {
        module: DELIVERY_CONFIG.MODULE,
        apiUrl: DELIVERY_CONFIG.API.BASE_URL,
        defaultEmail: DELIVERY_CONFIG.DEFAULT_CREDENTIALS.email
    });
} else {
    console.error('❌ SHARED_CONFIG not found! Check that shared-config.js is loaded before config.js');
}