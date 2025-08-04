// Application principale pour l'interface client simplifiée
class CustomerApp {
    constructor() {
        this.authService = new AuthService();
        this.apiService = new CustomerApiService();
        this.ui = new CustomerUIComponents();
        this.mapService = new CustomerMapService();
        this.websocketService = new CustomerWebSocketService();
        
        // État de l'application
        this.currentUser = null;
        this.currentTrackingOrder = null;
        this.isTracking = false;
        this.updateInterval = null;
        
        // Configuration de la relation AuthService <-> ApiService
        this.apiService.setAuthService(this.authService);
    }

    async init() {
        console.log('Customer App initialized');
        
        // Nettoyer les tokens expirés
        this.authService.clearExpiredToken();
        
        // Vérifier si l'utilisateur est déjà connecté
        if (this.authService.isAuthenticated()) {
            this.currentUser = this.authService.getUser();
            await this.initMainApp();
        } else {
            this.showLoginForm();
        }
    }

    showLoginForm() {
        this.ui.showLoginPanel();
        this.bindLoginEvents();
    }

    bindLoginEvents() {
        // Événement de soumission du formulaire de connexion
        this.ui.elements.loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleLogin();
        });
    }

    async handleLogin() {
        const email = this.ui.elements.clientEmail.value.trim();
        const password = this.ui.elements.clientPassword.value;

        if (!email || !password) {
            this.ui.showLoginError('Veuillez remplir tous les champs');
            return;
        }

        this.ui.setLoginLoading(true);
        this.ui.hideLoginError();

        try {
            const authData = await this.authService.login(email, password);
            this.currentUser = authData.user;
            
            console.log('Login successful:', this.currentUser);
            
            // Initialiser l'application principale
            await this.initMainApp();
            
        } catch (error) {
            console.error('Login failed:', error);
            this.ui.showLoginError('Email ou mot de passe incorrect');
        } finally {
            this.ui.setLoginLoading(false);
        }
    }

    async initMainApp() {
        try {
            // Afficher le panneau principal
            this.ui.showClientPanel();
            
            // Mettre à jour les informations client
            this.ui.updateClientInfo(this.currentUser);
            
            // Initialiser les services
            await this.initializeMap();
            this.initializeWebSocket();
            this.bindMainEvents();
            
            // Charger les commandes du client connecté
            await this.loadMyOrders();
            
            this.ui.showSuccess(`Bienvenue ${this.currentUser.full_name || this.currentUser.email}!`, 'Connexion réussie');
            
            console.log('Main app initialized successfully');
            
        } catch (error) {
            console.error('Error initializing main app:', error);
            this.ui.showError('Erreur lors de l\'initialisation de l\'application');
        }
    }

    async initializeMap() {
        try {
            this.mapService.initialize('map');
            this.ui.elements.mapStatus.textContent = 'Connectée';
            this.ui.elements.mapStatus.className = 'badge bg-success';
        } catch (error) {
            console.error('Map initialization failed:', error);
            this.ui.elements.mapStatus.textContent = 'Erreur';
            this.ui.elements.mapStatus.className = 'badge bg-danger';
        }
    }

    initializeWebSocket() {
        try {
            this.websocketService.initialize();
            
            this.websocketService.on('connected', () => {
                this.updateWebSocketStatus(true);
                console.log('WebSocket connected');
            });
            
            this.websocketService.on('disconnected', () => {
                this.updateWebSocketStatus(false);
                console.log('WebSocket disconnected');
            });
            
            this.websocketService.on('locationUpdate', (data) => {
                this.handleLocationUpdate(data);
            });
            
            this.websocketService.on('statusUpdate', (data) => {
                this.handleStatusUpdate(data);
            });
            
        } catch (error) {
            console.error('WebSocket initialization failed:', error);
            this.updateWebSocketStatus(false);
        }
    }

    bindMainEvents() {
        // Déconnexion
        this.ui.elements.logoutBtn.addEventListener('click', () => {
            this.logout();
        });
        
        // Rafraîchissement des commandes
        this.ui.elements.refreshOrdersBtn.addEventListener('click', () => {
            this.loadMyOrders();
        });
        
        // Filtres de commandes
        this.ui.elements.statusFilter.addEventListener('change', () => {
            this.loadMyOrders(1);
        });
        
        this.ui.elements.orderNumberFilter.addEventListener('input',
            this.debounce(() => this.loadMyOrders(1), 500)
        );
        
        // Arrêt du tracking
        this.ui.elements.stopTrackingBtn.addEventListener('click', () => {
            this.stopTracking();
        });
    }

    async logout() {
        try {
            // Arrêter le tracking en cours
            if (this.isTracking) {
                this.stopTracking();
            }
            
            // Déconnecter les services
            this.websocketService.disconnect();
            
            // Déconnexion API
            await this.authService.logout();
            
            // Réinitialiser l'état
            this.currentUser = null;
            this.currentTrackingOrder = null;
            this.isTracking = false;
            
            // Afficher la page de connexion
            this.showLoginForm();
            
            // Nettoyer l'interface
            this.ui.elements.ordersList.innerHTML = '';
            this.ui.hideOrderTracking();
            this.mapService.clearMarkers();
            this.mapService.clearRoute();
            
        } catch (error) {
            console.error('Logout error:', error);
            // Même en cas d'erreur, forcer la déconnexion locale
            this.authService.clearExpiredToken();
            window.location.reload();
        }
    }

    async loadMyOrders(page = 1) {
        if (!this.currentUser) {
            console.error('No user connected');
            return;
        }
        
        const filters = this.ui.getFilters();
        this.ui.setLoadingState('refreshOrdersBtn', true);
        
        try {
            // Utiliser l'ID du client - d'abord essayer customer_id, puis user_id, puis id
            let customerId = this.currentUser.customer_id || this.currentUser.id;
            
            console.log('Loading orders for customer ID:', customerId, 'User:', this.currentUser);
            
            const response = await this.apiService.getCustomerOrders(customerId, filters, page);
            
            console.log('Orders response:', response);
            
            if (response.data) {
                // Les commandes sont directement dans response.data, pas dans response.data.data
                const orders = Array.isArray(response.data) ? response.data : (response.data.data || []);
                const pagination = response._metadata?.pagination || { current_page: 1, last_page: 1 };
                
                console.log('Parsed orders:', orders, 'Pagination:', pagination);
                
                this.ui.renderOrders(
                    orders,
                    pagination.current_page || 1,
                    pagination.last_page || 1
                );
                
                if (orders.length === 0 && page === 1) {
                    this.ui.showInfo('Aucune commande trouvée avec les filtres appliqués');
                } else {
                    console.log(`${orders.length} commandes trouvées et affichées`);
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
            const orderCard = document.querySelector(`[data-order-number="${orderNumber}"]`);
            if (!orderCard) {
                throw new Error('Commande non trouvée dans la liste');
            }
            
            // Extraire les informations de la commande depuis la carte
            const orderData = this.extractOrderDataFromCard(orderCard);
            
            // Récupérer les détails du tracking
            const trackingResponse = await this.apiService.getTrackingDetails(orderNumber);
            
            if (trackingResponse.data) {
                const trackingData = trackingResponse.data;
                
                this.currentTrackingOrder = orderNumber;
                this.isTracking = true;
                
                // Mettre à jour l'interface
                this.ui.updateTrackingInfo(orderData, trackingData);
                this.ui.showOrderTracking();
                this.ui.highlightSelectedOrder(orderNumber);
                this.ui.clearTrackingHistory();
                
                // S'abonner aux mises à jour WebSocket
                this.websocketService.subscribeToDeliveryTracking(orderNumber);
                
                // Afficher la position initiale si disponible
                if (trackingData.current_latitude && trackingData.current_longitude) {
                    this.mapService.updateDriverPosition(
                        trackingData.current_latitude,
                        trackingData.current_longitude,
                        { name: trackingData.driver_name }
                    );
                }
                
                // Afficher la destination si disponible
                if (trackingData.destination_latitude && trackingData.destination_longitude) {
                    this.mapService.setDestination(
                        trackingData.destination_latitude,
                        trackingData.destination_longitude,
                        { 
                            name: this.currentUser.full_name || this.currentUser.email,
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

    extractOrderDataFromCard(orderCard) {
        const orderNumber = orderCard.dataset.orderNumber;
        const orderNumberElement = orderCard.querySelector('h6');
        const details = orderCard.querySelectorAll('p');
        
        return {
            order_number: orderNumber,
            delivery_address: details[3] ? details[3].textContent.replace('Adresse: ', '') : 'N/A',
            status: CONFIG.ORDER_STATUS.PROCESSING, // Assumé en cours si on peut suivre
            customer: this.currentUser
        };
    }

    stopTracking() {
        if (!this.currentTrackingOrder) return;
        
        // Désabonner du WebSocket
        this.websocketService.unsubscribeFromDeliveryTracking(this.currentTrackingOrder);
        
        // Arrêter les mises à jour périodiques
        this.stopPeriodicUpdates();
        
        // Nettoyer l'interface
        this.ui.hideOrderTracking();
        this.mapService.clearMarkers();
        this.mapService.clearRoute();
        
        this.ui.showInfo('Suivi arrêté pour la commande ' + this.currentTrackingOrder);
        
        this.currentTrackingOrder = null;
        this.isTracking = false;
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
            const response = await this.apiService.getTrackingDetails(this.currentTrackingOrder);
            
            if (response.data) {
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
                
                this.updateLastUpdateTime();
            }
        } catch (error) {
            console.error('Error updating tracking data:', error);
        }
    }

    // Gestionnaires d'événements WebSocket
    handleLocationUpdate(data) {
        if (data.order_number === this.currentTrackingOrder) {
            // Mettre à jour la position du livreur
            this.mapService.updateDriverPosition(
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
                this.loadMyOrders(); // Recharger pour voir les changements
            }, 5000);
        }
    }

    // Utilitaires
    updateWebSocketStatus(connected) {
        this.ui.elements.websocketStatus.textContent = connected ? 'Connecté' : 'Déconnecté';
        this.ui.elements.websocketStatus.className = `badge bg-${connected ? 'success' : 'secondary'}`;
    }

    updateLastUpdateTime() {
        this.ui.elements.lastUpdateTime.textContent = new Date().toLocaleTimeString();
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialiser l'application quand le DOM est prêt
document.addEventListener('DOMContentLoaded', async function() {
    window.customerApp = new CustomerApp();
    await window.customerApp.init();
});