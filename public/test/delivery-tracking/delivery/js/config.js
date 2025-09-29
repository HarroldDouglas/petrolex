// Configuration pour l'interface livreur
// Utilise la configuration partagée

// Attendre que SHARED_CONFIG soit chargé
if (typeof SHARED_CONFIG !== 'undefined') {
    // Étendre la configuration partagée au lieu de redéclarer DELIVERY_CONFIG
    if (typeof DELIVERY_CONFIG === 'undefined') {
        window.DELIVERY_CONFIG = {
            ...SHARED_CONFIG,
            // Spécifique au livreur
            MODULE: 'delivery',
            
            // Valeurs par défaut pour les tests
            DEFAULT_CREDENTIALS: {
                email: 'delivery1@test.com',
                password: 'password'
            },
            
            // Configuration de simulation
            SIMULATION: {
                POSITION_UPDATE_INTERVAL: 2000, // 2 secondes
                DEFAULT_SPEED_KMH: 40,
                MIN_SPEED_KMH: 10,
                MAX_SPEED_KMH: 80
            }
        };
    } else {
        // Si DELIVERY_CONFIG existe déjà, juste ajouter nos spécificités
        Object.assign(DELIVERY_CONFIG, {
            MODULE: 'delivery',
            DEFAULT_CREDENTIALS: {
                email: 'delivery1@test.com',
                password: 'password'
            },
            SIMULATION: {
                POSITION_UPDATE_INTERVAL: 2000, // 2 secondes
                DEFAULT_SPEED_KMH: 40,
                MIN_SPEED_KMH: 10,
                MAX_SPEED_KMH: 80
            }
        });
    }

    console.log('🚚 Configuration livreur chargée:', {
        module: DELIVERY_CONFIG.MODULE,
        apiUrl: DELIVERY_CONFIG.API.BASE_URL,
        defaultEmail: DELIVERY_CONFIG.DEFAULT_CREDENTIALS.email
    });
} else {
    console.error('❌ SHARED_CONFIG non trouvé ! Vérifiez que shared-config.js est chargé avant config.js');
}