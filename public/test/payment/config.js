/**
 * Configuration for Payment Test Interface
 * 
 * Modify these settings to customize the test environment
 */

window.PAYMENT_TEST_CONFIG = {
    // API Endpoints (updated for new unified API)
    endpoints: {
        mtn: {
            initiate: '/api/payment/test/mtn',
            status: '/api/payment/test/mtn/status',
            cancel: '/api/payment/test/mtn/cancel'
        },
        orange: {
            initiate: '/api/payment/test/orange',
            status: '/api/payment/test/orange/status', 
            cancel: '/api/payment/test/orange/cancel'
        }
    },

    // Test configuration
    testing: {
        // Enable/disable simulation mode
        simulationMode: true,
        
        // Default test amounts
        defaultAmounts: [500, 1000, 2500, 5000, 10000],
        
        // Minimum transaction amount
        minAmount: 100,
        
        // Maximum transaction amount  
        maxAmount: 1000000,
        
        // Simulation delays (in milliseconds)
        delays: {
            initiation: 2000,    // Time for payment initiation
            confirmation: 5000   // Time for pending confirmation
        },

        // Success rates for random scenarios
        successRates: {
            mtn: 0.75,      // 75% success rate for MTN
            orange: 0.70    // 70% success rate for Orange
        }
    },

    // Test phone numbers for different scenarios
    testNumbers: {
        mtn: {
            success: '677000001',
            pending: '677000002',
            failure: '677000003',
            timeout: '677000004',
            invalid: '677000005'
        },
        orange: {
            success: '699000001', 
            pending: '699000002',
            failure: '699000003',
            timeout: '699000004',
            invalid: '699000005'
        }
    },

    // Error messages and codes
    errorMessages: {
        mtn: {
            insufficient_funds: 'Solde insuffisant sur le compte MTN',
            invalid_number: 'Numéro MTN invalide',
            service_unavailable: 'Service MTN temporairement indisponible',
            transaction_declined: 'Transaction refusée par MTN',
            timeout: 'Délai d\'attente dépassé - MTN'
        },
        orange: {
            insufficient_funds: 'Solde insuffisant sur le compte Orange Money',
            invalid_number: 'Numéro Orange Money invalide', 
            service_unavailable: 'Service Orange Money temporairement indisponible',
            transaction_declined: 'Transaction refusée par Orange Money',
            timeout: 'Délai d\'attente dépassé - Orange Money'
        },
        general: {
            network_error: 'Erreur de connexion réseau',
            invalid_amount: 'Montant invalide',
            missing_parameters: 'Paramètres manquants',
            internal_error: 'Erreur interne du système'
        }
    },

    // UI Configuration
    ui: {
        // Console settings
        console: {
            maxLines: 100,          // Maximum lines in console
            autoScroll: true,       // Auto-scroll to bottom
            showTimestamps: true,   // Show timestamps
            colorCoding: true       // Enable color coding
        },
        
        // Form settings
        form: {
            validateOnInput: true,  // Real-time validation
            showTestHints: true,    // Show test number hints
            autoFillTestData: false // Auto-fill with test data
        },

        // Statistics
        stats: {
            showPercentages: true,  // Show success/failure percentages
            resetOnClear: true      // Reset stats when clearing logs
        }
    },

    // Environment settings
    environment: {
        // Current environment
        current: 'development', // 'development', 'staging', 'production'
        
        // Show debug information
        debug: true,
        
        // Log to browser console
        logToConsole: true,
        
        // Save logs to localStorage
        persistLogs: false
    }
};

/**
 * Helper functions for configuration
 */
window.PaymentTestUtils = {
    // Get test number for specific scenario
    getTestNumber: function(provider, scenario) {
        const config = window.PAYMENT_TEST_CONFIG;
        return config.testNumbers[provider] && config.testNumbers[provider][scenario];
    },

    // Check if number is a test number
    isTestNumber: function(provider, number) {
        const config = window.PAYMENT_TEST_CONFIG;
        const testNumbers = config.testNumbers[provider];
        return testNumbers && Object.values(testNumbers).includes(number);
    },

    // Get error message by code
    getErrorMessage: function(provider, errorCode) {
        const config = window.PAYMENT_TEST_CONFIG;
        return config.errorMessages[provider] && config.errorMessages[provider][errorCode] || 
               config.errorMessages.general[errorCode] ||
               'Erreur inconnue';
    },

    // Validate phone number format
    validatePhoneNumber: function(provider, number) {
        const patterns = {
            mtn: /^6[5-9][0-9]{7}$/,      // MTN: 65-69 + 7 digits
            orange: /^6[0-4][0-9]{7}$/     // Orange: 60-64 + 7 digits  
        };
        
        return patterns[provider] && patterns[provider].test(number);
    },

    // Format amount with currency
    formatAmount: function(amount) {
        return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
    },

    // Generate unique transaction ID
    generateTransactionId: function(provider = '') {
        const prefix = provider.toUpperCase() || 'TXN';
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 5).toUpperCase();
        return `${prefix}_${timestamp}_${random}`;
    }
};