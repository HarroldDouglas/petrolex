// Application principale - Orchestrateur
class CustomerApp {
    constructor() {
        this.authService = new CustomerAuthService();
        this.apiService = new CustomerApiService();
        this.ui = new CustomerUIComponents();
        this.mapService = new CustomerGoogleMapService();
        this.errorHandler = new CustomerErrorHandlingService();

        // Délégation des responsabilités
        this.authController = new AuthController(this.authService, this.ui);
        this.orderController = new OrderController(
            this.apiService,
            this.ui,
            this.errorHandler,
        );
        this.trackingController = new TrackingController(
            this.apiService,
            this.ui,
            this.mapService,
        );
        this.websocketManager = new CustomerWebSocketService(
            this.ui,
            this.trackingController,
        );

        // État de l'application
        this.currentUser = null;

        this.apiService.setAuthService(this.authService);
        
        // Initialiser l'orderManager avec le controller
        this.ui.initOrderManager(this.orderController);
        
        // Exposer globalement pour accès depuis les components
        window.components = this.ui;
        window.customerApp = this;
    }

    async init() {
        this.authService.clearExpiredToken();

        if (this.authService.isAuthenticated()) {
            this.currentUser = this.authService.getUser();
            await this.initMainApp();
        } else {
            this.showLoginForm();
        }
    }

    showLoginForm() {
        this.ui.showLoginPanel();
        this.authController.bindLoginEvents(this.handleLoginSuccess.bind(this));
    }

    async handleLoginSuccess(authData) {
        this.currentUser = authData.user;
        await this.initMainApp();
    }

    async initMainApp() {
        try {
            this.ui.showClientPanel();
            this.ui.uiManager.updateClientInfo(this.currentUser);

            await this.initializeMap();
            this.websocketManager.initialize();
            this.bindMainEvents();

            await this.orderController.loadCustomerOrders(this.currentUser);

            this.ui.uiManager.showSuccess(
                `Bienvenue ${this.currentUser.full_name || this.currentUser.email}!`,
            );
        } catch (error) {
            console.error("Error initializing main app:", error);
            this.ui.uiManager.showError(
                "Erreur lors de l'initialisation de l'application",
            );
        }
    }

    async initializeMap() {
        try {
            this.mapService.initialize("map");
            this.ui.uiManager.elements.mapStatus.textContent = "Connectée";
            this.ui.uiManager.elements.mapStatus.className = "badge bg-success";
        } catch (error) {
            this.ui.uiManager.elements.mapStatus.textContent = "Erreur";
            this.ui.uiManager.elements.mapStatus.className = "badge bg-danger";
        }
    }

    // Méthodes de compatibilité
    startTracking(orderNumber) {
        // Si orderNumber est déjà un objet orderData, le passer directement
        if (typeof orderNumber === 'object' && orderNumber.order_number) {
            return this.trackingController.startTracking(orderNumber);
        }
        
        // Sinon, extraire les données de la commande depuis la carte HTML
        const orderCard = document.querySelector(`[data-order-number="${orderNumber}"]`);
        if (!orderCard) {
            this.ui.uiManager.showError(`Carte de commande non trouvée pour ${orderNumber}`);
            return;
        }
        
        const orderData = this.orderController.extractOrderDataFromCard(orderCard);
        if (!orderData.id) {
            this.ui.uiManager.showError(`ID de commande manquant pour ${orderNumber}`);
            return;
        }
        
        return this.trackingController.startTracking(orderData);
    }

    stopTracking() {
        return this.trackingController.stopTracking();
    }

    extractOrderDataFromCard(orderCard) {
        return this.orderController.extractOrderDataFromCard(orderCard);
    }

    async loadMyOrders(page = 1) {
        console.log('📦 [CustomerApp] Chargement des commandes...');
        try {
            const response = await this.orderController.loadCustomerOrders(this.currentUser, {}, page);
            console.log('✅ [CustomerApp] Commandes reçues:', response);
            
            if (response && response.data) {
                console.log('📊 [CustomerApp] Données à afficher:', response.data.data || response.data);
                console.log('📊 [CustomerApp] orderManager:', window.components?.orderManager);
                
                // Utiliser directement l'OrderManager pour afficher les commandes
                if (window.components && window.components.orderManager) {
                    const ordersData = response.data.data || response.data;
                    const currentPage = response.data.current_page || page;
                    const totalPages = response.data.last_page || 1;
                    
                    console.log('🎨 [CustomerApp] Appel renderOrders avec:', {
                        ordersCount: ordersData.length,
                        currentPage,
                        totalPages
                    });
                    
                    window.components.orderManager.renderOrders(ordersData, currentPage, totalPages);
                } else {
                    console.error('❌ [CustomerApp] orderManager non disponible');
                }
            } else {
                console.error('❌ [CustomerApp] Aucune donnée dans la réponse');
            }
            
            return response;
        } catch (error) {
            console.error('❌ [CustomerApp] Erreur chargement commandes:', error);
            throw error;
        }
    }

    bindMainEvents() {
        this.orderController.bindEvents();
        this.trackingController.bindEvents();

        document
            .getElementById("refreshOrdersBtn")
            ?.addEventListener("click", () => {
                this.orderController.loadCustomerOrders(this.currentUser);
            });

        document.getElementById("logoutBtn")?.addEventListener("click", () => {
            this.authController.logout();
            this.cleanup();
            this.showLoginForm();
        });
    }

    cleanup() {
        this.websocketManager.disconnect();
        this.trackingController.cleanup();
        this.mapService?.cleanup();
    }
}

// Initialisation
document.addEventListener("DOMContentLoaded", async function () {
    window.customerApp = new CustomerApp();
    await window.customerApp.init();
});
