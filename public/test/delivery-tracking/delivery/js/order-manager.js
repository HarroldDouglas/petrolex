class OrderManager {
    constructor(apiService, ui, sessionManager) {
        this.apiService = apiService;
        this.ui = ui;
        this.sessionManager = sessionManager;
        this.selectedOrder = null;
        this.selectedOrderId = null;
        this.selectedOrderData = null;
        this.currentPage = 1;
        this.currentFilters = {};
        this.searchTimeout = null;
        this.cachedOrders = []; // Cache des commandes chargées
        this.deliveryManager = null;
        this.trackingService = null; // Sera initialisé par setTrackingService
    }

    // NOUVEAU: Méthode pour définir le service de tracking
    setTrackingService(trackingService) {
        this.trackingService = trackingService;
    }

    // NOUVEAU: Méthode pour définir le gestionnaire de livraison
    setDeliveryManager(deliveryManager) {
        this.deliveryManager = deliveryManager;
    }

    async loadOrders(page = 1) {
        const deliveryPerson = this.sessionManager.getCurrentDeliveryPerson();
        if (!deliveryPerson) return;
        
        this.currentPage = page;
        this.currentFilters = this.ui.getFilters();
        this.ui.setLoadingState('refreshOrdersBtn', true);
        
        try {
            const deliveryPersonId = deliveryPerson.delivery_person_id || deliveryPerson.id;
            const response = await this.apiService.getOrders(deliveryPersonId, this.currentFilters, page);
            
            if (response._metadata?.success && response.data) {
                const orders = response.data.data || response.data || [];
                const pagination = response.data;
                
                // Mettre en cache les commandes chargées ET les order_id
                this.cachedOrders = orders;
                this.apiService.cacheOrderIds(orders);
                
                this.ui.renderOrders(orders, pagination.current_page || 1, pagination.last_page || 1);
                
                if (orders.length === 0 && page === 1) {
                    this.ui.showInfo('Aucune commande trouvée avec les filtres appliqués');
                }
            } else {
                throw new Error(response._metadata?.message || 'Impossible de charger les commandes');
            }
        } catch (error) {
            this.ui.showError(error.message || 'Erreur lors du chargement des commandes');
        } finally {
            this.ui.setLoadingState('refreshOrdersBtn', false);
        }
    }

    async selectOrder(orderId) {
        if (this.trackingService && this.trackingService.getTrackingState().isTracking) {
            this.ui.showError('Une livraison est déjà en cours. Terminez-la d\'abord.');
            return;
        }
        
        try {
            if (this.selectedOrderId && this.selectedOrderId !== orderId) {
                this.clearSelectedOrder();
            }
            
            this.selectedOrderId = orderId;
            
            this.ui.setLoadingState('orderDetails', true);
            const response = await this.apiService.getOrder(orderId);
            
            if (!response || !response.data) {
                throw new Error('Impossible de charger les détails de la commande');
            }
            
            const orderData = response.data;
            this.selectedOrderData = orderData;
            this.selectedOrder = orderData;
            
            if (orderData.status === DELIVERY_CONFIG.ORDER_STATUS.IN_PROGRESS) {
                try {
                    const trackingResponse = await this.apiService.getOrderTracking(orderId);
                    if (trackingResponse && trackingResponse.data) {
                        orderData.trackingData = trackingResponse.data;
                        
                        const trackingData = trackingResponse.data;
                        if (trackingData.status === DELIVERY_CONFIG.ORDER_STATUS.IN_PROGRESS || trackingData.status === 'started') {
                            // Initialiser l'affichage des estimations avec les données existantes
                            if (trackingData.progress_percentage !== undefined && 
                                trackingData.distance_remaining !== undefined && 
                                trackingData.estimated_duration !== undefined) {
                                    
                                this.ui.updateProgress(trackingData.progress_percentage);
                                this.ui.updateRouteEstimates(
                                    trackingData.estimated_duration,
                                    trackingData.distance_remaining,
                                    true
                                );
                            }
                        }
                    }
                } catch (trackingError) {
                    console.warn('Tracking data not available for in-progress order:', trackingError.message);
                }
            } else {
                // Pour les commandes confirmées, pas de tracking à récupérer
                console.log(`📦 Commande ${orderData.order_number} sélectionnée (statut: ${orderData.status}) - pas de tracking requis`);
            }
            
            this.ui.updateSelectedOrderDetails(orderData);
            this.ui.highlightSelectedOrder(orderId);
            
            if (this.deliveryManager) {
                await this.deliveryManager.calculateRouteForSelectedOrder();
            }
            
            return orderData;
        } catch (error) {
            this.ui.showError(`Erreur lors du chargement de la commande: ${error.message}`);
            this.clearSelectedOrder();
            return null;
        } finally {
            this.ui.setLoadingState('orderDetails', false);
        }
    }

    setupEventHandlers() {
        this.ui.elements.refreshOrdersBtn.addEventListener('click', () => this.loadOrders());
        
        this.ui.elements.statusFilter.addEventListener('change', () => {
            this.currentPage = 1;
            this.loadOrders();
        });
        
        this.ui.elements.orderNumberFilter.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.loadOrders();
            }, 500);
        });
    }

    getSelectedOrder() {
        return this.selectedOrder;
    }

    clearSelectedOrder() {
        this.selectedOrder = null;
        this.ui.hideSelectedOrderDetails();
    }
}