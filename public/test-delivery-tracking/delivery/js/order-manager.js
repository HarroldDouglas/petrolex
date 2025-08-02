class OrderManager {
    constructor(apiService, ui, sessionManager) {
        this.apiService = apiService;
        this.ui = ui;
        this.sessionManager = sessionManager;
        this.selectedOrder = null;
        this.currentPage = 1;
        this.currentFilters = {};
        this.searchTimeout = null;
        this.cachedOrders = []; // Cache des commandes chargées
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

    async selectOrder(orderNumber) {
        try {
            // D'abord chercher dans le cache des commandes déjà chargées
            let order = this.cachedOrders.find(o => o.order_number === orderNumber);
            
            if (!order) {
                // Si pas trouvé dans le cache, faire un appel API avec le bon ID
                const deliveryPerson = this.sessionManager.getCurrentDeliveryPerson();
                if (!deliveryPerson) return;
                
                const deliveryPersonId = deliveryPerson.delivery_person_id || deliveryPerson.id;
                const response = await this.apiService.getOrders(deliveryPersonId, { order_number: orderNumber });
                
                if (response._metadata?.success && response.data) {
                    const orders = response.data.data || response.data || [];
                    if (orders.length > 0) {
                        order = orders[0];
                    }
                }
            }
            
            if (order) {
                this.selectedOrder = order;
                this.ui.updateSelectedOrderDetails(order);
                this.ui.highlightSelectedOrder(orderNumber);
                
                if (order.status === CONFIG.ORDER_STATUS.CONFIRMED) {
                    this.ui.showDeliveryControls();
                    this.ui.showInfo('Commande sélectionnée! Vous pouvez maintenant démarrer la livraison.');
                } else if (order.status === CONFIG.ORDER_STATUS.PROCESSING) {
                    this.ui.showInfo('Cette commande est déjà en cours de livraison');
                } else {
                    this.ui.showInfo('Cette commande ne peut plus être modifiée');
                }
                
                return order;
            } else {
                throw new Error('Commande non trouvée');
            }
        } catch (error) {
            this.ui.showError(error.message || 'Erreur lors de la sélection de la commande');
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