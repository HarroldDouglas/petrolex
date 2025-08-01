// Application principale pour l'interface livreur avec authentification réelle
class DeliveryPersonApp {
    constructor() {
        this.initServices();
        this.initManagers();
        this.setupEventHandlers();
        this.init();
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
        this.services.tracking = new DeliveryTrackingService(this.services.api, this.services.map);
        
        this.deliveryManager = new DeliveryManager(
            this.services.map, 
            this.services.tracking, 
            this.ui, 
            this.orderManager
        );
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

        this.orderManager.setupEventHandlers();
        this.deliveryManager.setupEventHandlers();
        
        window.deliveryPersonApp = this;
    }

    async init() {
        const deliveryPerson = await this.sessionManager.checkExistingSession();
        if (deliveryPerson) {
            await this.orderManager.loadOrders();
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
        const order = await this.orderManager.selectOrder(orderNumber);
        if (order && order.status === CONFIG.ORDER_STATUS.CONFIRMED) {
            await this.deliveryManager.calculateRouteForSelectedOrder();
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