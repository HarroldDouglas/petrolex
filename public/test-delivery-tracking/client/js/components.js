// Composants UI pour l'interface client simplifiée
class CustomerUIComponents {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.loginPanel = document.getElementById('loginPanel');
        this.elements.clientPanel = document.getElementById('clientPanel');
        this.elements.selectedOrderPanel = document.getElementById('selectedOrderPanel');
        this.elements.trackingHistoryPanel = document.getElementById('trackingHistoryPanel');
        
        // Formulaire de connexion
        this.elements.loginForm = document.getElementById('loginForm');
        this.elements.clientEmail = document.getElementById('clientEmail');
        this.elements.clientPassword = document.getElementById('clientPassword');
        this.elements.loginBtn = document.getElementById('loginBtn');
        this.elements.loginError = document.getElementById('loginError');
        
        // Informations client connecté
        this.elements.currentClientName = document.getElementById('currentClientName');
        this.elements.currentClientEmail = document.getElementById('currentClientEmail');
        this.elements.logoutBtn = document.getElementById('logoutBtn');
        
        // Commandes
        this.elements.refreshOrdersBtn = document.getElementById('refreshOrdersBtn');
        this.elements.statusFilter = document.getElementById('statusFilter');
        this.elements.orderNumberFilter = document.getElementById('orderNumberFilter');
        this.elements.ordersList = document.getElementById('ordersList');
        this.elements.ordersPagination = document.getElementById('ordersPagination');
        
        // Tracking
        this.elements.trackingOrderNumber = document.getElementById('trackingOrderNumber');
        this.elements.trackingOrderStatus = document.getElementById('trackingOrderStatus');
        this.elements.trackingDriverName = document.getElementById('trackingDriverName');
        this.elements.trackingDeliveryAddress = document.getElementById('trackingDeliveryAddress');
        this.elements.trackingETA = document.getElementById('trackingETA');
        this.elements.trackingDistance = document.getElementById('trackingDistance');
        this.elements.trackingProgress = document.getElementById('trackingProgress');
        this.elements.trackingProgressBar = document.getElementById('trackingProgressBar');
        this.elements.stopTrackingBtn = document.getElementById('stopTrackingBtn');
        this.elements.trackingHistory = document.getElementById('trackingHistory');
        
        // Statut de connexion
        this.elements.connectionStatus = document.getElementById('connectionStatus');
        this.elements.websocketStatus = document.getElementById('websocketStatus');
        this.elements.apiStatus = document.getElementById('apiStatus');
        this.elements.mapStatus = document.getElementById('mapStatus');
        this.elements.lastUpdateTime = document.getElementById('lastUpdateTime');
    }

    // Gestion des panneaux
    showLoginPanel() {
        this.elements.loginPanel.style.display = 'block';
        this.elements.clientPanel.style.display = 'none';
        this.updateConnectionStatus(false);
    }

    showClientPanel() {
        this.elements.loginPanel.style.display = 'none';
        this.elements.clientPanel.style.display = 'block';
        this.updateConnectionStatus(true);
    }

    showOrderTracking() {
        this.elements.selectedOrderPanel.style.display = 'block';
        this.elements.trackingHistoryPanel.style.display = 'block';
    }

    hideOrderTracking() {
        this.elements.selectedOrderPanel.style.display = 'none';
        this.elements.trackingHistoryPanel.style.display = 'none';
    }

    // Gestion du statut de connexion
    updateConnectionStatus(connected) {
        if (this.elements.connectionStatus) {
            if (connected) {
                this.elements.connectionStatus.className = 'status-indicator status-online';
            } else {
                this.elements.connectionStatus.className = 'status-indicator status-offline';
            }
        }
    }

    // Gestion de l'authentification
    showLoginError(message) {
        this.elements.loginError.textContent = message;
        this.elements.loginError.style.display = 'block';
    }

    hideLoginError() {
        this.elements.loginError.style.display = 'none';
    }

    setLoginLoading(isLoading) {
        this.elements.loginBtn.disabled = isLoading;
        if (isLoading) {
            this.elements.loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';
        } else {
            this.elements.loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
        }
    }

    // Informations client connecté
    updateClientInfo(user) {
        const fullName = user.full_name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Client';
        this.elements.currentClientName.textContent = fullName;
        this.elements.currentClientEmail.textContent = user.email || '';
    }

    // Gestion des commandes
    renderOrders(orders, currentPage = 1, totalPages = 1) {
        const list = this.elements.ordersList;
        list.innerHTML = '';

        if (!orders || orders.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3">Aucune commande trouvée</div>';
            return;
        }

        orders.forEach(order => {
            const orderCard = this.createOrderCard(order);
            list.appendChild(orderCard);
        });

        this.updateOrdersPagination(currentPage, totalPages);
    }

    createOrderCard(order) {
        const div = document.createElement('div');
        div.className = 'order-card mb-2 p-3 border rounded';
        div.dataset.orderNumber = order.order_number;

        const statusColor = CONFIG.STATUS.COLORS[order.status] || 'secondary';
        const statusLabel = CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;

        // Extraire l'adresse correctement
        const deliveryAddress = order.delivery_address?.name || order.delivery_address?.address || 'Adresse non définie';

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-primary">${order.order_number}</h6>
                    <p class="mb-1 text-sm"><strong>Date:</strong> ${this.formatDate(order.order_date)}</p>
                    <p class="mb-1 text-sm"><strong>Montant:</strong> ${order.total_amount || 0}€</p>
                    <p class="mb-0 text-sm"><strong>Adresse:</strong> ${deliveryAddress}</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-${statusColor} mb-2">${statusLabel}</span>
                    ${this.getOrderTrackingAction(order)}
                </div>
            </div>
        `;

        return div;
    }

    getOrderTrackingAction(order) {
        switch (order.status) {
            case CONFIG.ORDER_STATUS.PROCESSING:
                return `<button class="btn btn-sm btn-success w-100" onclick="window.customerApp.startTracking('${order.order_number}')">
                    <i class="fas fa-map-marker-alt"></i> Suivre
                </button>`;
            case CONFIG.ORDER_STATUS.CONFIRMED:
                return `<button class="btn btn-sm btn-warning w-100" disabled>
                    <i class="fas fa-clock"></i> En attente
                </button>`;
            case CONFIG.ORDER_STATUS.DELIVERED:
                return `<button class="btn btn-sm btn-outline-success w-100" disabled>
                    <i class="fas fa-check"></i> Livrée
                </button>`;
            default:
                return `<button class="btn btn-sm btn-outline-secondary w-100" disabled>
                    ${statusLabel}
                </button>`;
        }
    }

    updateOrdersPagination(currentPage, totalPages) {
        const pagination = this.elements.ordersPagination;
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        const nav = document.createElement('nav');
        nav.innerHTML = `
            <ul class="pagination pagination-sm">
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadOrders(${currentPage - 1})">Précédent</a>
                </li>
                ${this.generatePageNumbers(currentPage, totalPages)}
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadOrders(${currentPage + 1})">Suivant</a>
                </li>
            </ul>
        `;
        pagination.appendChild(nav);
    }

    // Tracking
    updateTrackingInfo(order, trackingData = {}) {
        this.elements.trackingOrderNumber.textContent = order.order_number;
        
        const statusColor = CONFIG.STATUS.COLORS[order.status] || 'secondary';
        const statusLabel = CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
        this.elements.trackingOrderStatus.textContent = statusLabel;
        this.elements.trackingOrderStatus.className = `badge bg-${statusColor}`;
        
        this.elements.trackingDriverName.textContent = trackingData.driver_name || order.delivery_person?.name || '-';
        this.elements.trackingDeliveryAddress.textContent = order.delivery_address || 'N/A';
        
        this.updateTrackingStats(trackingData);
    }

    updateTrackingStats(data) {
        if (data.eta) {
            this.elements.trackingETA.textContent = `${data.eta} min`;
        } else {
            this.elements.trackingETA.textContent = '-';
        }
        
        if (data.distance) {
            this.elements.trackingDistance.textContent = `${data.distance} km`;
        } else {
            this.elements.trackingDistance.textContent = '-';
        }
    }

    updateTrackingProgress(percent) {
        const roundedPercent = Math.round(percent);
        this.elements.trackingProgress.textContent = `${roundedPercent}%`;
        this.elements.trackingProgressBar.style.width = `${percent}%`;
    }

    addTrackingHistoryItem(message, type = 'info') {
        const history = this.elements.trackingHistory;
        
        const item = document.createElement('div');
        item.className = 'history-item fade-in';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <span>${message}</span>
                <span class="time">${new Date().toLocaleTimeString()}</span>
            </div>
        `;
        
        history.insertBefore(item, history.firstChild);
        
        // Limiter le nombre d'éléments dans l'historique
        const items = history.querySelectorAll('.history-item');
        if (items.length > 20) {
            items[items.length - 1].remove();
        }
        
        this.updateLastUpdateTime();
    }

    clearTrackingHistory() {
        this.elements.trackingHistory.innerHTML = '';
    }

    // Utilitaires
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    getFilters() {
        const filters = {};
        
        if (this.elements.statusFilter.value) {
            filters.status = this.elements.statusFilter.value;
        }
        
        if (this.elements.orderNumberFilter.value.trim()) {
            filters.order_number = this.elements.orderNumberFilter.value.trim();
        }
        
        return filters;
    }

    clearFilters() {
        this.elements.statusFilter.value = '';
        this.elements.orderNumberFilter.value = '';
    }

    highlightSelectedOrder(orderNumber) {
        // Supprimer la surbrillance de tous les éléments
        document.querySelectorAll('.order-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Ajouter la surbrillance à l'élément sélectionné
        const selectedCard = document.querySelector(`[data-order-number="${orderNumber}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
            selectedCard.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }
    }

    // Notifications
    showError(message, title = 'Erreur') {
        console.error(title + ':', message);
        this.showNotification('error', title, message, 5000);
    }

    showSuccess(message, title = 'Succès') {
        console.log(title + ':', message);
        this.showNotification('success', title, message, 4000);
    }

    showInfo(message, title = 'Information') {
        console.info(title + ':', message);
        this.showNotification('info', title, message, 3000);
    }

    showNotification(type, title, message, duration = 4000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type} fade-in`;
        notification.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <strong style="display: block; margin-bottom: 5px;">${title}</strong>
                    <div style="font-size: 0.9em;">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: inherit; font-size: 1.2em; cursor: pointer; margin-left: 10px;">&times;</button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification && notification.parentElement) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }

    // Utilitaires de chargement
    setLoadingState(element, isLoading) {
        if (typeof element === 'string') {
            element = document.getElementById(element);
        }
        
        if (element) {
            element.disabled = isLoading;
            if (isLoading) {
                element.classList.add('loading');
                const originalText = element.innerHTML;
                element.dataset.originalText = originalText;
                element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
            } else {
                element.classList.remove('loading');
                if (element.dataset.originalText) {
                    element.innerHTML = element.dataset.originalText;
                    delete element.dataset.originalText;
                }
            }
        }
    }

    // Méthodes utilitaires pour la pagination
    generatePageNumbers(currentPage, totalPages) {
        const pages = [];
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadMyOrders(${i})">${i}</a>
                </li>
            `);
        }

        return pages.join('');
    }

    // Mettre à jour le temps de dernière mise à jour
    updateLastUpdateTime() {
        if (this.elements.lastUpdateTime) {
            this.elements.lastUpdateTime.textContent = new Date().toLocaleTimeString();
        }
    }
}

// Composant pour le formulaire de connexion client
class LoginForm {
    constructor(containerId, onLoginSuccess) {
        this.container = document.getElementById(containerId);
        this.onLoginSuccess = onLoginSuccess;
        this.authService = new AuthService();
        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="login-overlay">
                <div class="login-container">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3 class="mb-0">
                                <i class="fas fa-user-circle me-2"></i>
                                Connexion Client
                            </h3>
                            <p class="text-muted mt-2">Connectez-vous pour suivre vos livraisons</p>
                        </div>
                        <div class="card-body">
                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>
                                        Email
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email"
                                        required
                                        placeholder="votre.email@exemple.com"
                                    >
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>
                                        Mot de passe
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password" 
                                        name="password"
                                        required
                                        placeholder="Votre mot de passe"
                                    >
                                </div>
                                <div class="d-grid">
                                    <button 
                                        type="submit" 
                                        class="btn btn-primary btn-lg"
                                        id="loginBtn"
                                    >
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Se connecter
                                    </button>
                                </div>
                            </form>
                            <div id="loginError" class="alert alert-danger mt-3" style="display: none;"></div>
                            <div id="loginLoading" class="text-center mt-3" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Connexion en cours...</span>
                                </div>
                                <p class="mt-2 text-muted">Connexion en cours...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                .login-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                }
                
                .login-container {
                    width: 100%;
                    max-width: 400px;
                    padding: 20px;
                }
                
                .login-container .card {
                    border: none;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                }
                
                .login-container .card-header {
                    background: transparent;
                    border-bottom: 1px solid #eee;
                    padding: 25px 25px 20px;
                }
                
                .login-container .card-body {
                    padding: 25px;
                }
                
                .login-container .form-control {
                    border-radius: 10px;
                    padding: 12px 15px;
                    border: 1px solid #ddd;
                }
                
                .login-container .form-control:focus {
                    border-color: #667eea;
                    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                }
                
                .login-container .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    border-radius: 10px;
                    padding: 12px;
                    font-weight: 600;
                }
                
                .login-container .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
            </style>
        `;
    }

    bindEvents() {
        const form = this.container.querySelector('#loginForm');
        const emailInput = this.container.querySelector('#email');
        const passwordInput = this.container.querySelector('#password');
        const loginBtn = this.container.querySelector('#loginBtn');
        const errorDiv = this.container.querySelector('#loginError');
        const loadingDiv = this.container.querySelector('#loginLoading');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            if (!email || !password) {
                this.showError('Veuillez remplir tous les champs');
                return;
            }

            this.showLoading(true);
            this.hideError();

            try {
                const authData = await this.authService.login(email, password);
                console.log('Login successful:', authData.user);
                
                // Callback de succès
                if (this.onLoginSuccess) {
                    this.onLoginSuccess(authData);
                }
                
            } catch (error) {
                console.error('Login failed:', error);
                this.showError('Email ou mot de passe incorrect');
            } finally {
                this.showLoading(false);
            }
        });

        // Gestion de l'Enter
        [emailInput, passwordInput].forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    form.dispatchEvent(new Event('submit'));
                }
            });
        });
    }

    showError(message) {
        const errorDiv = this.container.querySelector('#loginError');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    hideError() {
        const errorDiv = this.container.querySelector('#loginError');
        errorDiv.style.display = 'none';
    }

    showLoading(show) {
        const loadingDiv = this.container.querySelector('#loginLoading');
        const loginBtn = this.container.querySelector('#loginBtn');
        const form = this.container.querySelector('#loginForm');
        
        if (show) {
            loadingDiv.style.display = 'block';
            loginBtn.disabled = true;
            form.style.opacity = '0.7';
        } else {
            loadingDiv.style.display = 'none';
            loginBtn.disabled = false;
            form.style.opacity = '1';
        }
    }

    hide() {
        this.container.style.display = 'none';
    }

    show() {
        this.container.style.display = 'block';
    }
}