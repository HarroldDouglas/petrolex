// Application principale pour l'interface client avec vraies données
class CustomerApp {
    constructor() {
        this.services = {
            api: new CustomerApiService(),
            map: new CustomerMapService(),
            websocket: new CustomerWebSocketService()
        };
        
        this.ui = new CustomerUIComponents();
        this.selectedCustomer = null;
        this.selectedOrder = null;
        this.currentTrackingOrder = null;
        this.currentPage = 1;
        this.currentFilters = {};
        this.updateInterval = null;
        
        this.init();
    }

    init() {
        // Initialiser les services
        this.initializeServices();
        
        // Configurer les gestionnaires d'événements
        this.setupEventHandlers();
        
        // Charger les clients par défaut
        this.loadCustomers();
        
        console.log('Customer App initialized');
    }

    initializeServices() {
        // Initialiser la carte
        this.services.map.initialize('map');
        
        // Initialiser WebSocket
        this.services.websocket.initialize();
        
        // Configurer les callbacks WebSocket
        this.services.websocket.on('connected', () => {
            this.ui.updateWebSocketStatus(true);
            this.ui.showSuccess('Connexion WebSocket établie');
        });
        
        this.services.websocket.on('disconnected', () => {
            this.ui.updateWebSocketStatus(false);
            this.ui.showError('Connexion WebSocket perdue');
        });
        
        this.services.websocket.on('error', (error) => {
            this.ui.updateWebSocketStatus(false);
            this.ui.showError('Erreur WebSocket: ' + error.message);
        });
        
        this.services.websocket.on('locationUpdate', (data) => {
            this.handleLocationUpdate(data);
        });
        
        this.services.websocket.on('statusUpdate', (data) => {
            this.handleStatusUpdate(data);
        });
        
        this.services.websocket.on('deliveryCompleted', (data) => {
            this.handleDeliveryCompleted(data);
        });
    }

    setupEventHandlers() {
        // Recherche de clients
        this.ui.elements.customerSearchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.loadCustomers();
            }, 500);
        });
        
        // Changement de client
        this.ui.elements.changeCustomerBtn.addEventListener('click', () => {
            this.changeCustomer();
        });
        
        // Actualisation des commandes
        this.ui.elements.refreshOrdersBtn.addEventListener('click', () => {
            this.loadOrders();
        });
        
        // Filtres des commandes
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
        
        // Arrêter le tracking
        this.ui.elements.stopTrackingBtn.addEventListener('click', () => {
            this.stopTracking();
        });
        
        // Exposer les méthodes globalement
        window.customerApp = this;
    }

    async loadCustomers(page = 1) {
        this.currentPage = page;
        const search = this.ui.elements.customerSearchInput.value.trim();
        
        this.ui.setLoadingState('customerSearchInput', true);
        
        try {
            const response = await this.services.api.getCustomers(search, page);
            
            if (response.success && response.data) {
                const customers = response.data.data || [];
                const pagination = response.data;
                
                this.ui.renderCustomers(
                    customers,
                    pagination.current_page || 1,
                    pagination.last_page || 1
                );
                
                if (customers.length === 0 && page === 1) {
                    this.ui.showInfo('Aucun client trouvé avec ce critère de recherche');
                }
            } else {
                throw new Error('Impossible de charger les clients');
            }
        } catch (error) {
            console.error('Error loading customers:', error);
            this.ui.showError(error.message || 'Erreur lors du chargement des clients');
        } finally {
            this.ui.setLoadingState('customerSearchInput', false);
        }
    }

    async selectCustomer(customerId) {
        try {
            // Récupérer les détails du client depuis la liste actuelle
            const customerCards = document.querySelectorAll('.customer-card');
            let customerData = null;
            
            customerCards.forEach(card => {
                if (card.dataset.customerId == customerId) {
                    const name = card.querySelector('h6').textContent;
                    const email = card.querySelector('p:nth-child(2)').textContent.replace('Email: ', '');
                    const phone = card.querySelector('p:nth-child(3)').textContent.replace('Téléphone: ', '');
                    
                    customerData = {
                        id: customerId,
                        name: name,
                        email: email,
                        phone: phone
                    };
                }
            });
            
            if (customerData) {
                this.selectedCustomer = customerData;
                this.ui.updateSelectedCustomerInfo(customerData);
                this.ui.showSelectedCustomer();
                
                // Charger les commandes du client
                await this.loadOrders();
                
                this.ui.showSuccess(`Client "${customerData.name}" sélectionné`);
            } else {
                throw new Error('Client non trouvé');
            }
        } catch (error) {
            console.error('Error selecting customer:', error);
            this.ui.showError(error.message || 'Erreur lors de la sélection du client');
        }
    }

    changeCustomer() {
        // Arrêter le tracking en cours
        if (this.currentTrackingOrder) {
            this.stopTracking();
        }
        
        this.selectedCustomer = null;
        this.selectedOrder = null;
        this.ui.showCustomerSelection();
        this.ui.hideOrderTracking();
        this.services.map.clearMarkers();
        this.services.map.clearRoute();
    }

    async loadOrders(page = 1) {
        if (!this.selectedCustomer) return;
        
        this.currentPage = page;
        this.currentFilters = this.ui.getFilters();
        
        this.ui.setLoadingState('refreshOrdersBtn', true);
        
        try {
            const response = await this.services.api.getCustomerOrders(
                this.selectedCustomer.id,
                this.currentFilters,
                page
            );
            
            if (response.success && response.data) {
                const orders = response.data.data || [];
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
                throw new Error('Impossible de charger les commandes');
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            this.ui.showError(error.message || 'Erreur lors du chargement des commandes');
        } finally {
            this.ui.setLoadingState('refreshOrdersBtn', false);
        }
    }

    async startTracking(orderNumber) {
        if (this.currentTrackingOrder === orderNumber) {
            this.ui.showInfo('Suivi déjà actif pour cette commande');
            return;
        }
        
        // Arrêter le tracking précédent
        if (this.currentTrackingOrder) {
            this.stopTracking();
        }
        
        try {
            // Récupérer les détails de la commande depuis la liste
            const orderCards = document.querySelectorAll('.order-card');
            let orderData = null;
            
            orderCards.forEach(card => {
                if (card.dataset.orderNumber === orderNumber) {
                    const orderNumber = card.querySelector('h6').textContent;
                    const details = card.querySelectorAll('p');
                    
                    orderData = {
                        order_number: orderNumber,
                        delivery_address: details[3] ? details[3].textContent.replace('Adresse: ', '') : 'N/A',
                        status: CONFIG.ORDER_STATUS.PROCESSING, // On suppose que c'est en cours
                        customer: this.selectedCustomer
                    };
                }
            });
            
            if (!orderData) {
                throw new Error('Commande non trouvée');
            }
            
            // Récupérer les détails du tracking
            const trackingResponse = await this.services.api.getTrackingDetails(orderNumber);
            
            if (trackingResponse.success && trackingResponse.data) {
                const trackingData = trackingResponse.data;
                
                this.currentTrackingOrder = orderNumber;
                this.selectedOrder = orderData;
                
                // Mettre à jour l'interface
                this.ui.updateTrackingInfo(orderData, trackingData);
                this.ui.showOrderTracking();
                this.ui.highlightSelectedOrder(orderNumber);
                this.ui.clearTrackingHistory();
                
                // S'abonner aux mises à jour WebSocket
                this.services.websocket.subscribeToDeliveryTracking(orderNumber);
                
                // Afficher la position initiale si disponible
                if (trackingData.current_latitude && trackingData.current_longitude) {
                    this.services.map.updateDriverPosition(
                        trackingData.current_latitude,
                        trackingData.current_longitude,
                        { name: trackingData.driver_name }
                    );
                }
                
                // Afficher la destination si disponible
                if (trackingData.destination_latitude && trackingData.destination_longitude) {
                    this.services.map.setDestination(
                        trackingData.destination_latitude,
                        trackingData.destination_longitude,
                        { 
                            name: this.selectedCustomer.name,
                            address: orderData.delivery_address
                        }
                    );
                }
                
                // Démarrer les mises à jour automatiques
                this.startPeriodicUpdates();
                
                this.ui.addTrackingHistoryItem('Suivi démarré pour la commande ' + orderNumber);
                this.ui.showSuccess('Suivi en temps réel activé');
                
            } else {
                throw new Error('Impossible de récupérer les détails du tracking');
            }
        } catch (error) {
            console.error('Error starting tracking:', error);
            this.ui.showError(error.message || 'Erreur lors du démarrage du suivi');
        }
    }

    stopTracking() {
        if (!this.currentTrackingOrder) return;
        
        // Désabonner du WebSocket
        this.services.websocket.unsubscribeFromDeliveryTracking(this.currentTrackingOrder);
        
        // Arrêter les mises à jour périodiques
        this.stopPeriodicUpdates();
        
        // Nettoyer l'interface
        this.ui.hideOrderTracking();
        this.services.map.clearMarkers();
        this.services.map.clearRoute();
        
        this.ui.showInfo('Suivi arrêté pour la commande ' + this.currentTrackingOrder);
        
        this.currentTrackingOrder = null;
        this.selectedOrder = null;
    }

    startPeriodicUpdates() {
        this.stopPeriodicUpdates();
        
        this.updateInterval = setInterval(async () => {
            if (this.currentTrackingOrder) {
                await this.updateTrackingData();
            }
        }, CONFIG.UI.UPDATE_INTERVAL);
    }

    stopPeriodicUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    async updateTrackingData() {
        if (!this.currentTrackingOrder) return;
        
        try {
            const response = await this.services.api.getTrackingDetails(this.currentTrackingOrder);
            
            if (response.success && response.data) {
                const data = response.data;
                
                // Mettre à jour les statistiques
                this.ui.updateTrackingStats({
                    eta: data.estimated_time_remaining,
                    distance: data.distance_remaining
                });
                
                // Mettre à jour la progression si disponible
                if (data.progress_percentage !== undefined) {
                    this.ui.updateTrackingProgress(data.progress_percentage);
                }
                
                this.ui.updateLastUpdateTime();
            }
        } catch (error) {
            console.error('Error updating tracking data:', error);
        }
    }

    // Gestionnaires d'événements WebSocket
    handleLocationUpdate(data) {
        if (data.order_number === this.currentTrackingOrder) {
            // Mettre à jour la position du livreur
            this.services.map.updateDriverPosition(
                data.latitude,
                data.longitude,
                { name: data.driver_name }
            );
            
            // Mettre à jour les statistiques
            this.ui.updateTrackingStats({
                eta: data.eta,
                distance: data.distance_remaining
            });
            
            // Mettre à jour la progression
            if (data.progress_percentage !== undefined) {
                this.ui.updateTrackingProgress(data.progress_percentage);
            }
            
            this.ui.addTrackingHistoryItem(`Position mise à jour - ${data.latitude.toFixed(4)}, ${data.longitude.toFixed(4)}`);
        }
    }

    handleStatusUpdate(data) {
        if (data.order_number === this.currentTrackingOrder) {
            // Mettre à jour le statut de la commande
            const statusColor = CONFIG.STATUS.COLORS[data.status] || 'secondary';
            const statusLabel = CONFIG.STATUS.TRANSLATIONS[data.status] || data.status;
            
            this.ui.elements.trackingOrderStatus.textContent = statusLabel;
            this.ui.elements.trackingOrderStatus.className = `badge bg-${statusColor}`;
            
            this.ui.addTrackingHistoryItem(`Statut mis à jour: ${statusLabel}`);
            
            // Si la livraison est terminée, arrêter le suivi
            if (data.status === CONFIG.ORDER_STATUS.DELIVERED) {
                this.handleDeliveryCompleted(data);
            }
        }
    }

    handleDeliveryCompleted(data) {
        if (data.order_number === this.currentTrackingOrder) {
            this.ui.updateTrackingProgress(100);
            this.ui.addTrackingHistoryItem('🎉 Livraison terminée avec succès!');
            this.ui.showSuccess('Livraison terminée!');
            
            // Arrêter le suivi après un délai
            setTimeout(() => {
                this.stopTracking();
                this.loadOrders(); // Recharger pour voir les changements
            }, 5000);
        }
    }

    // Méthodes utilitaires
    getCurrentCustomer() {
        return this.selectedCustomer;
    }

    getCurrentTrackingOrder() {
        return this.currentTrackingOrder;
    }

    isTracking() {
        return this.currentTrackingOrder !== null;
    }

    getWebSocketStatus() {
        return this.services.websocket.isConnected();
    }
}

// Initialiser l'application quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    window.customerApp = new CustomerApp();
});