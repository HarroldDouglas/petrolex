// Application principale pour l'interface livreur avec authentification réelle
class DeliveryPersonApp {
    constructor() {
        this.initServices();
        this.initManagers();
        this.setupEventHandlers();
        this.init();
        
        // Initialisation de la Facade pour simplifier la gestion des contrôles
        this.controlsFacade = null; // Sera initialisé après les autres services
    }

    initServices() {
        this.services = {
            api: new DeliveryPersonApiService(),
            map: new DeliveryGoogleMapService(),
            tracking: null
        };
        this.ui = new DeliveryPersonUIComponents();
        this.websocketManager = new DeliveryWebSocketManager();
    }

    initManagers() {
        this.sessionManager = new SessionManager(this.services.api, this.ui);
        this.orderManager = new OrderManager(this.services.api, this.ui, this.sessionManager);
        
        // Initialiser deliveryManager à null temporairement
        this.deliveryManager = null;
        
        // Attendre que Google Maps soit chargé
        this.waitForGoogleMaps().then(() => {
            try {
                this.services.map.initialize('map');
                // Update map status to success
                this.ui.updateMapStatus(true);
                console.log('✅ Google Maps initialisé avec succès');
            } catch (error) {
                console.error('❌ Erreur lors de l\'initialisation Google Maps:', error);
                this.ui.updateMapStatus(false);
            }
            this.services.tracking = new DeliveryTrackingService(this.services.api, this.services.map, this.ui, this.orderManager);
            
            this.deliveryManager = new DeliveryManager(
                this.services.map, 
                this.services.tracking, 
                this.ui, 
                this.orderManager
            );
            
            // Maintenant qu'on a deliveryManager, configurer les dépendances
            this.orderManager.setTrackingService(this.services.tracking);
            this.orderManager.setDeliveryManager(this.deliveryManager);
            
            // Configurer les event handlers du deliveryManager
            this.deliveryManager.setupEventHandlers();
            
            // Injecter la facade dans le DeliveryManager pour les transitions d'état
            if (this.controlsFacade) {
                this.deliveryManager.setControlsFacade(this.controlsFacade);
            }

            // Connect WebSocket and link to deliveryManager
            this.websocketManager.setDeliveryManager(this.deliveryManager);
            // TODO: Fix Reverb library import before enabling WebSocket
            // this.websocketManager.connect().catch(error => {
            //     console.error('WebSocket connection failed:', error);
            // });
        });
        
        // AJOUT: Connecter orderManager à l'UI de tracking pour l'accès aux données de commande
        this.ui.trackingUI.setOrderManager(this.orderManager);
    }

    setupEventHandlers() {
        this.ui.elements.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });
        
        this.ui.elements.logoutBtn.addEventListener('click', () => {
            if (this.deliveryManager && this.deliveryManager.getTrackingState().isTracking) {
                this.deliveryManager.stopDelivery();
            }
            this.sessionManager.logout();
        });

        this.ui.elements.completeDeliveryBtn.addEventListener('click', () => {
            if (this.deliveryManager) {
                this.deliveryManager.completeDelivery();
            }
        });

        this.orderManager.setupEventHandlers();
        // deliveryManager.setupEventHandlers() sera appelé dans initManagers() après l'initialisation
        
        window.deliveryPersonApp = this;
    }

    async init() {
        const deliveryPerson = await this.sessionManager.checkExistingSession();
        if (deliveryPerson) {
            await this.orderManager.loadOrders();
        }
        
        // Initialiser la facade après tous les autres services
        this.controlsFacade = new DeliveryControlsFacade(
            this.ui, 
            this.orderManager, 
            this.deliveryManager
        );
        
        // Injecter la facade dans le DeliveryManager SEULEMENT s'il existe
        if (this.deliveryManager) {
            this.deliveryManager.setControlsFacade(this.controlsFacade);
        }
    }

    async handleLogin() {
        const email = this.ui.elements.deliveryPersonEmail.value.trim();
        const password = this.ui.elements.deliveryPersonPassword.value;
        
        this.ui.setLoadingState('loginBtn', true);
        
        try {
            await this.sessionManager.login(email, password);
            await this.orderManager.loadOrders();
            this.ui.elements.loginForm.reset();
        } catch (error) {
            this.ui.showError(error.message || 'Erreur de connexion');
        } finally {
            this.ui.setLoadingState('loginBtn', false);
        }
    }

    async selectOrder(orderNumber) {
        // 🎯 ULTRA SIMPLE maintenant grâce à la Facade !
        // Une seule ligne remplace toute la logique complexe
        await this.controlsFacade.handleOrderSelection(orderNumber);
        
        console.log(`✅ [App] Commande sélectionnée:`, this.orderManager.getSelectedOrder());
        
        // Vérifier si c'est une nouvelle commande confirmée
        const selectedOrder = this.orderManager.getSelectedOrder();
        if (selectedOrder && selectedOrder.status === DELIVERY_CONFIG.ORDER_STATUS.CONFIRMED) {
            console.log('🆕 [App] Nouvelle commande confirmée');
        }
    }

    loadOrders(page = 1) {
        return this.orderManager.loadOrders(page);
    }

    getCurrentDeliveryPerson() {
        return this.sessionManager.getCurrentDeliveryPerson();
    }

    getSelectedOrder() {
        return this.orderManager.getSelectedOrder();
    }
    
    // Attendre que Google Maps soit chargé
    waitForGoogleMaps() {
        return new Promise((resolve) => {
            if (window.google && window.google.maps) {
                resolve();
            } else if (window.googleMapsLoaded) {
                resolve();
            } else {
                const checkGoogleMaps = () => {
                    if (window.google && window.google.maps) {
                        resolve();
                    } else {
                        setTimeout(checkGoogleMaps, 100);
                    }
                };
                checkGoogleMaps();
            }
        });
    }
    
    // Callback pour quand Google Maps est prêt
    onGoogleMapsReady() {
        console.log("🗺️ Google Maps prêt pour l'interface delivery");
        if (this.services && this.services.map && !this.services.map.initialized) {
            try {
                this.services.map.initialize('map');
                this.ui.updateMapStatus(true);
                console.log('✅ Google Maps initialisé via callback');
            } catch (error) {
                console.error('❌ Erreur lors de l\'initialisation Google Maps via callback:', error);
                this.ui.updateMapStatus(false);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.deliveryPersonApp = new DeliveryPersonApp();
});