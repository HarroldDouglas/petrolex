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
        if (!order) {
            console.error('❌ [App] Impossible de sélectionner la commande:', orderNumber);
            return;
        }
        
        console.log('✅ [App] Commande sélectionnée:', order);
        
        // Calculer la route pour la commande sélectionnée
        await this.deliveryManager.calculateRouteForSelectedOrder();
        
        // Si la commande est en cours ou confirmée
        if (order.status === DELIVERY_CONFIG.ORDER_STATUS.PROCESSING || 
            order.status === DELIVERY_CONFIG.ORDER_STATUS.CONFIRMED) {
            
            // Vérifier si la commande est en cours de livraison
            if (order.status === DELIVERY_CONFIG.ORDER_STATUS.PROCESSING) {
                console.log('🚚 [App] Commande en cours de livraison détectée');
                
                // Vérifier si cette commande est déjà en tracking actif
                const trackingState = this.deliveryManager.getTrackingState();
                
                if (trackingState.isTracking && 
                    trackingState.currentOrder && 
                    trackingState.currentOrder.order_number === orderNumber) {
                    // C'est la commande actuellement suivie
                    console.log('🔄 [App] Commande déjà en suivi actif');
                    this.ui.trackingUI.setDeliveryControlsState(true, trackingState.isPaused);
                    this.ui.trackingUI.updateProgress(trackingState.currentProgress || 0);
                } else {
                    // Commande en cours mais pas actuellement suivie
                    console.log('⏳ [App] Commande en cours mais pas en suivi actif');
                    
                    // Mettre à jour les boutons pour une commande en cours
                    this.ui.trackingUI.updateDeliveryButtonsForInProgressOrder();
                    
                    // IMPORTANT: Si la commande a des données de tracking, les utiliser pour l'affichage
                    if (order.trackingData) {
                        const trackingData = order.trackingData;
                        console.log('📊 [App] Données de tracking disponibles:', trackingData);
                        
                        // Mettre à jour les estimations avec les données existantes
                        if (trackingData.estimated_duration !== undefined &&
                            trackingData.distance_remaining !== undefined) {
                            this.ui.trackingUI.updateRouteEstimates(
                                trackingData.estimated_duration,
                                trackingData.distance_remaining,
                                true
                            );
                        }
                    }
                }
            } else {
                // Pour les nouvelles commandes (CONFIRMED)
                console.log('🆕 [App] Nouvelle commande confirmée');
                this.ui.trackingUI.setDeliveryControlsState(false, false);
            }
            
            // S'assurer que la section de contrôle est visible
            const controlsSection = document.getElementById('deliveryControlsSection');
            if (controlsSection) {
                controlsSection.style.display = 'block';
            }
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