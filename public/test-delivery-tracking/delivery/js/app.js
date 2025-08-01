// Application principale pour l'interface livreur avec authentification réelle
class DeliveryPersonApp {
    constructor() {
        this.services = {
            api: new DeliveryPersonApiService(),
            map: new DeliveryPersonMapService(),
            tracking: null
        };
        
        this.ui = new DeliveryPersonUIComponents();
        this.currentDeliveryPerson = null;
        this.selectedOrder = null;
        this.currentPage = 1;
        this.currentFilters = {};
        
        this.init();
    }

    init() {
        // Initialiser les services
        this.initializeServices();
        
        // Configurer les gestionnaires d'événements
        this.setupEventHandlers();
        
        // Vérifier si un token existe déjà
        this.checkExistingSession();
        
        console.log('Delivery Person App initialized');
    }

    initializeServices() {
        // Initialiser la carte
        this.services.map.initialize('map');
        
        // Initialiser le service de tracking
        this.services.tracking = new DeliveryTrackingService(this.services.api, this.services.map);
        
        // Configurer les callbacks de tracking
        this.services.tracking.on('trackingStarted', (data) => {
            this.handleTrackingStarted(data);
        });
        
        this.services.tracking.on('progressUpdate', (data) => {
            this.handleProgressUpdate(data);
        });
        
        this.services.tracking.on('trackingCompleted', (data) => {
            this.handleTrackingCompleted(data);
        });
        
        this.services.tracking.on('trackingStopped', () => {
            this.handleTrackingStopped();
        });
        
        this.services.tracking.on('trackingPaused', () => {
            this.handleTrackingPaused();
        });
        
        this.services.tracking.on('trackingResumed', () => {
            this.handleTrackingResumed();
        });
    }

    setupEventHandlers() {
        // Authentification
        this.ui.elements.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });
        
        this.ui.elements.logoutBtn.addEventListener('click', () => {
            this.handleLogout();
        });
        
        // Gestion des commandes
        this.ui.elements.refreshOrdersBtn.addEventListener('click', () => {
            this.loadOrders();
        });
        
        this.ui.elements.statusFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadOrders();
        });
        
        this.ui.elements.orderNumberFilter.addEventListener('input', () => {
            // Débounce la recherche
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.loadOrders();
            }, 500);
        });
        
        // Mode de transport
        this.ui.elements.transportWalking.addEventListener('change', () => {
            this.ui.updateTransportInfo();
            this.calculateRouteForSelectedOrder();
        });
        
        this.ui.elements.transportDriving.addEventListener('change', () => {
            this.ui.updateTransportInfo();
            this.calculateRouteForSelectedOrder();
        });
        
        // Contrôles de livraison
        this.ui.elements.startDeliveryBtn.addEventListener('click', () => {
            this.startDelivery();
        });
        
        this.ui.elements.pauseDeliveryBtn.addEventListener('click', () => {
            this.pauseDelivery();
        });
        
        this.ui.elements.stopDeliveryBtn.addEventListener('click', () => {
            this.stopDelivery();
        });
        
        // Exposer les méthodes globalement
        window.deliveryPersonApp = this;
    }

    async checkExistingSession() {
        const token = localStorage.getItem('delivery_person_token');
        const deliveryPersonData = JSON.parse(localStorage.getItem('delivery_person_data') || 'null');
        const sessionExpiry = localStorage.getItem('delivery_person_session_expiry');
        
        // Vérifier si la session a expiré (2 heures = 7200000 ms)
        if (sessionExpiry && Date.now() > parseInt(sessionExpiry)) {
            this.clearSession();
            this.ui.showLoginPanel();
            this.ui.showInfo('Session expirée. Veuillez vous reconnecter.');
            return;
        }
        
        if (token && deliveryPersonData) {
            try {
                // Tester si le token est encore valide avec un appel simple
                const testResponse = await this.services.api.request('/tracking/delivery/active');
                
                if (testResponse._metadata?.success !== false) {
                    // Le token est valide, restaurer la session
                    this.currentDeliveryPerson = deliveryPersonData;
                    this.ui.updateDeliveryPersonInfo(deliveryPersonData);
                    this.ui.showDeliveryPersonPanel();
                    this.ui.updateConnectionStatus(true);
                    
                    // Prolonger la session (2 heures supplémentaires)
                    this.extendSession();
                    
                    await this.loadOrders();
                    this.ui.showSuccess(`Reconnexion automatique réussie! Bonjour ${deliveryPersonData.first_name} 👋`);
                    return;
                }
            } catch (error) {
                console.warn('Session expired or invalid:', error);
            }
        }
        
        // Pas de session valide
        this.clearSession();
        this.ui.showLoginPanel();
    }

    // Nouvelle méthode pour créer une session avec expiration
    createSession(deliveryPersonData, token) {
        const expiryTime = Date.now() + (2 * 60 * 60 * 1000); // 2 heures en millisecondes
        
        localStorage.setItem('delivery_person_token', token);
        localStorage.setItem('delivery_person_data', JSON.stringify(deliveryPersonData));
        localStorage.setItem('delivery_person_session_expiry', expiryTime.toString());
        localStorage.setItem('delivery_person_login_time', new Date().toISOString());
        
        console.log('Session créée, expire le:', new Date(expiryTime).toLocaleString());
    }
    
    // Prolonger la session de 2 heures
    extendSession() {
        const newExpiryTime = Date.now() + (2 * 60 * 60 * 1000);
        localStorage.setItem('delivery_person_session_expiry', newExpiryTime.toString());
        console.log('Session prolongée jusqu\'au:', new Date(newExpiryTime).toLocaleString());
    }
    
    // Nettoyer complètement la session
    clearSession() {
        localStorage.removeItem('delivery_person_token');
        localStorage.removeItem('delivery_person_data');
        localStorage.removeItem('delivery_person_session_expiry');
        localStorage.removeItem('delivery_person_login_time');
        this.services.api.clearToken();
    }

    async handleLogin() {
        const email = this.ui.elements.deliveryPersonEmail.value.trim();
        const password = this.ui.elements.deliveryPersonPassword.value;
        
        if (!email || !password) {
            this.ui.showError('Veuillez remplir tous les champs');
            return;
        }
        
        this.ui.setLoadingState('loginBtn', true);
        
        try {
            const response = await this.services.api.login(email, password);
            
            console.log('Login response:', response); // Debug
            
            // Vérifier que c'est bien un livreur
            if (response.user && response.user.roles && response.user.roles.includes('delivery_person')) {
                this.currentDeliveryPerson = response.user;
                
                // Créer une session avec expiration de 2 heures
                this.createSession(response.user, response.access_token);
                
                this.ui.updateDeliveryPersonInfo(response.user);
                this.ui.showDeliveryPersonPanel();
                this.ui.showSuccess(`Connexion réussie! Session valide pendant 2 heures 🕐`);
                
                // Charger les commandes
                await this.loadOrders();
                
                // Effacer le formulaire
                this.ui.elements.loginForm.reset();
            } else {
                throw new Error('Ce compte n\'est pas associé à un livreur');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.ui.showError(error.message || 'Erreur de connexion');
        } finally {
            this.ui.setLoadingState('loginBtn', false);
        }
    }

    handleLogout() {
        // Nettoyer la session
        this.clearSession();
        
        this.currentDeliveryPerson = null;
        this.selectedOrder = null;
        
        // Arrêter le tracking si en cours
        if (this.services.tracking.getTrackingState().isTracking) {
            this.services.tracking.stopTracking();
        }
        
        this.ui.showLoginPanel();
        this.ui.hideSelectedOrderDetails();
        this.ui.hideDeliveryControls();
        this.ui.updateConnectionStatus(false);
        this.ui.showSuccess('Déconnexion réussie. Session effacée.');
    }

    async loadOrders(page = 1) {
        if (!this.currentDeliveryPerson) return;
        
        this.currentPage = page;
        this.currentFilters = this.ui.getFilters();
        
        this.ui.setLoadingState('refreshOrdersBtn', true);
        
        try {
            // Utiliser delivery_person_id au lieu de user.id
            const deliveryPersonId = this.currentDeliveryPerson.delivery_person_id || this.currentDeliveryPerson.id;
            
            const response = await this.services.api.getOrders(
                deliveryPersonId,
                this.currentFilters,
                page
            );
            
            // Adapter la vérification pour le format de réponse avec _metadata
            if (response._metadata?.success && response.data) {
                const orders = response.data.data || response.data || [];
                const pagination = response.data;
                
                this.ui.renderOrders(
                    orders,
                    pagination.current_page || 1,
                    pagination.last_page || 1
                );
                
                if (orders.length === 0 && page === 1) {
                    this.ui.showInfo('Aucune commande trouvée avec les filtres appliqués');
                }
            } else {
                throw new Error(response._metadata?.message || 'Impossible de charger les commandes');
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            this.ui.showError(error.message || 'Erreur lors du chargement des commandes');
        } finally {
            this.ui.setLoadingState('refreshOrdersBtn', false);
        }
    }

    async selectOrder(orderNumber) {
        try {
            // Charger les détails de la commande
            const response = await this.services.api.getOrders(
                this.currentDeliveryPerson.id,
                { order_number: orderNumber }
            );
            
            if (response.success && response.data && response.data.data.length > 0) {
                const order = response.data.data[0];
                this.selectedOrder = order;
                
                // Afficher les détails
                this.ui.updateSelectedOrderDetails(order);
                this.ui.highlightSelectedOrder(orderNumber);
                
                // Calculer la route si la commande est confirmée
                if (order.status === CONFIG.ORDER_STATUS.CONFIRMED) {
                    await this.calculateRouteForSelectedOrder();
                    this.ui.showDeliveryControls();
                    this.ui.showInfo('Commande sélectionnée! Vous pouvez maintenant démarrer la livraison.');
                } else if (order.status === CONFIG.ORDER_STATUS.PROCESSING) {
                    this.ui.showInfo('Cette commande est déjà en cours de livraison');
                } else {
                    this.ui.showInfo('Cette commande ne peut plus être modifiée');
                }
            } else {
                throw new Error('Commande non trouvée');
            }
        } catch (error) {
            console.error('Error selecting order:', error);
            this.ui.showError(error.message || 'Erreur lors de la sélection de la commande');
        }
    }

    async calculateRouteForSelectedOrder() {
        if (!this.selectedOrder || !this.selectedOrder.delivery_address_latitude || !this.selectedOrder.delivery_address_longitude) {
            return;
        }
        
        try {
            const currentPosition = await this.services.map.getCurrentGPSPosition();
            const transportMode = this.ui.getSelectedTransportMode();
            
            const routeInfo = await this.services.map.drawRoute(
                currentPosition,
                {
                    lat: this.selectedOrder.delivery_address_latitude,
                    lng: this.selectedOrder.delivery_address_longitude
                },
                transportMode
            );
            
            if (routeInfo) {
                this.ui.updateRouteEstimates(routeInfo.duration, routeInfo.distance);
            }
        } catch (error) {
            console.error('Error calculating route:', error);
            this.ui.updateRouteEstimates(null, null);
        }
    }

    async startDelivery() {
        if (!this.selectedOrder) {
            this.ui.showError('Veuillez sélectionner une commande');
            return;
        }
        
        const trackingState = this.services.tracking.getTrackingState();
        
        if (trackingState.isPaused) {
            // Reprendre la livraison
            this.services.tracking.resumeTracking();
            return;
        }
        
        if (trackingState.isTracking) {
            this.ui.showError('Une livraison est déjà en cours');
            return;
        }
        
        this.ui.setLoadingState('startDeliveryBtn', true);
        
        try {
            const speed = this.ui.getSimulationSpeed();
            await this.services.tracking.startTracking(this.selectedOrder.order_number, speed);
            
            this.ui.showSuccess('Livraison démarrée!');
        } catch (error) {
            console.error('Error starting delivery:', error);
            this.ui.showError(error.message || 'Erreur lors du démarrage de la livraison');
        } finally {
            this.ui.setLoadingState('startDeliveryBtn', false);
        }
    }

    pauseDelivery() {
        this.services.tracking.pauseTracking();
    }

    stopDelivery() {
        if (confirm('Êtes-vous sûr de vouloir arrêter cette livraison ?')) {
            this.services.tracking.stopTracking();
        }
    }

    // Callbacks de tracking
    handleTrackingStarted(data) {
        const state = this.services.tracking.getTrackingState();
        this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
        this.ui.updateProgress(0);
        
        // Mettre à jour le statut de la commande localement
        if (this.selectedOrder) {
            this.selectedOrder.status = CONFIG.ORDER_STATUS.PROCESSING;
            this.ui.updateSelectedOrderDetails(this.selectedOrder);
        }
        
        this.ui.showInfo('Tracking démarré! La position sera mise à jour en temps réel.');
    }

    handleProgressUpdate(data) {
        this.ui.updateProgress(data.progress);
        this.ui.updateCurrentPosition(data.position);
        
        // Calculer le temps et distance restant si possible
        if (this.selectedOrder && data.progress > 0) {
            const remainingPercent = 100 - data.progress;
            // Estimer le temps restant basé sur la progression
            const estimatedTimeElement = this.ui.elements.estimatedTime.textContent;
            if (estimatedTimeElement && estimatedTimeElement !== 'Non disponible') {
                const totalTime = parseInt(estimatedTimeElement);
                if (!isNaN(totalTime)) {
                    const remainingTime = Math.round(totalTime * (remainingPercent / 100));
                    this.ui.updateRouteEstimates(remainingTime, null, true);
                }
            }
        }
    }

    handleTrackingCompleted(data) {
        this.ui.updateProgress(100);
        this.ui.showSuccess('Livraison terminée avec succès!');
        
        // Mettre à jour le statut de la commande
        if (this.selectedOrder) {
            this.selectedOrder.status = CONFIG.ORDER_STATUS.DELIVERED;
            this.ui.updateSelectedOrderDetails(this.selectedOrder);
        }
        
        setTimeout(() => {
            this.resetDelivery();
            this.loadOrders(); // Recharger pour voir les changements
        }, 3000);
    }

    handleTrackingStopped() {
        this.resetDelivery();
        this.ui.showInfo('Livraison arrêtée');
    }

    handleTrackingPaused() {
        const state = this.services.tracking.getTrackingState();
        this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
        this.ui.showInfo('Livraison mise en pause');
    }

    handleTrackingResumed() {
        const state = this.services.tracking.getTrackingState();
        this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
        this.ui.showInfo('Livraison reprise');
    }

    resetDelivery() {
        const state = this.services.tracking.getTrackingState();
        this.ui.setDeliveryControlsState(state.isTracking, state.isPaused);
        this.ui.updateProgress(0);
        this.ui.hideDeliveryControls();
        this.selectedOrder = null;
    }

    // Méthodes utilitaires
    getCurrentPosition() {
        return this.services.map.getCurrentPosition();
    }

    getCurrentDeliveryPerson() {
        return this.currentDeliveryPerson;
    }

    getSelectedOrder() {
        return this.selectedOrder;
    }
}

// Initialiser l'application quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    window.deliveryPersonApp = new DeliveryPersonApp();
});