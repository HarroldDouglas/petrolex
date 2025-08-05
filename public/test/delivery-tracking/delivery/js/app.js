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
            map: new DeliveryPersonMapService(),
            tracking: null
        };
        this.ui = new DeliveryPersonUIComponents();
    }

    initManagers() {
        this.sessionManager = new SessionManager(this.services.api, this.ui);
        this.orderManager = new OrderManager(this.services.api, this.ui, this.sessionManager);
        
        this.services.map.initialize('map');
        this.services.tracking = new DeliveryTrackingService(this.services.api, this.services.map, this.ui, this.orderManager);
        
        this.deliveryManager = new DeliveryManager(
            this.services.map, 
            this.services.tracking, 
            this.ui, 
            this.orderManager
        );
        
        // Injecter les dépendances entre orderManager, trackingService et deliveryManager
        this.orderManager.setTrackingService(this.services.tracking);
        this.orderManager.setDeliveryManager(this.deliveryManager);
        
        // AJOUT: Connecter orderManager à l'UI de tracking pour l'accès aux données de commande
        this.ui.trackingUI.setOrderManager(this.orderManager);
    }

    setupEventHandlers() {
        this.ui.elements.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });
        
        this.ui.elements.logoutBtn.addEventListener('click', () => {
            if (this.deliveryManager.getTrackingState().isTracking) {
                this.deliveryManager.stopDelivery();
            }
            this.sessionManager.logout();
        });

        this.ui.elements.completeDeliveryBtn.addEventListener('click', () => {
            this.deliveryManager.completeDelivery();
        });

        this.orderManager.setupEventHandlers();
        this.deliveryManager.setupEventHandlers();
        
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
        
        // Injecter la facade dans le DeliveryManager pour les transitions d'état
        this.deliveryManager.setControlsFacade(this.controlsFacade);
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
}

document.addEventListener('DOMContentLoaded', () => {
    window.deliveryPersonApp = new DeliveryPersonApp();
});