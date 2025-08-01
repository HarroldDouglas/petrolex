// Composants UI pour l'interface client avec vraies données
class CustomerUIComponents {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.customerSelectionPanel = document.getElementById('customerSelectionPanel');
        this.elements.selectedCustomerPanel = document.getElementById('selectedCustomerPanel');
        this.elements.selectedOrderPanel = document.getElementById('selectedOrderPanel');
        this.elements.trackingHistoryPanel = document.getElementById('trackingHistoryPanel');
        
        // Sélection client
        this.elements.customerSearchInput = document.getElementById('customerSearchInput');
        this.elements.customersList = document.getElementById('customersList');
        this.elements.customerPagination = document.getElementById('customerPagination');
        this.elements.changeCustomerBtn = document.getElementById('changeCustomerBtn');
        
        // Informations client sélectionné
        this.elements.selectedCustomerName = document.getElementById('selectedCustomerName');
        this.elements.selectedCustomerEmail = document.getElementById('selectedCustomerEmail');
        this.elements.selectedCustomerPhone = document.getElementById('selectedCustomerPhone');
        
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
    showCustomerSelection() {
        this.elements.customerSelectionPanel.style.display = 'block';
        this.elements.selectedCustomerPanel.style.display = 'none';
        this.updateConnectionStatus(false);
    }

    showSelectedCustomer() {
        this.elements.customerSelectionPanel.style.display = 'none';
        this.elements.selectedCustomerPanel.style.display = 'block';
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

    // Statut de connexion
    updateConnectionStatus(connected) {
        const statusClass = connected ? 'status-online' : 'status-offline';
        this.elements.connectionStatus.className = `status-indicator ${statusClass}`;
    }

    updateWebSocketStatus(connected) {
        const badge = this.elements.websocketStatus;
        if (connected) {
            badge.textContent = 'Connecté';
            badge.className = 'badge bg-success';
        } else {
            badge.textContent = 'Déconnecté';
            badge.className = 'badge bg-secondary';
        }
    }

    updateLastUpdateTime() {
        this.elements.lastUpdateTime.textContent = new Date().toLocaleTimeString();
    }

    // Gestion des clients
    renderCustomers(customers, currentPage = 1, totalPages = 1) {
        const list = this.elements.customersList;
        list.innerHTML = '';

        if (!customers || customers.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3">Aucun client trouvé</div>';
            return;
        }

        customers.forEach(customer => {
            const customerCard = this.createCustomerCard(customer);
            list.appendChild(customerCard);
        });

        this.updateCustomerPagination(currentPage, totalPages);
    }

    createCustomerCard(customer) {
        const div = document.createElement('div');
        div.className = 'customer-card mb-2 p-3 border rounded';
        div.dataset.customerId = customer.id;

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-primary">${customer.name || 'N/A'}</h6>
                    <p class="mb-1 text-sm"><strong>Email:</strong> ${customer.email || 'N/A'}</p>
                    <p class="mb-0 text-sm"><strong>Téléphone:</strong> ${customer.phone || 'N/A'}</p>
                </div>
                <div class="text-end">
                    <button class="btn btn-sm btn-primary" onclick="window.customerApp.selectCustomer(${customer.id})">
                        <i class="fas fa-user-check"></i> Sélectionner
                    </button>
                </div>
            </div>
        `;

        return div;
    }

    updateCustomerPagination(currentPage, totalPages) {
        const pagination = this.elements.customerPagination;
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        const nav = document.createElement('nav');
        nav.innerHTML = `
            <ul class="pagination pagination-sm">
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadCustomers(${currentPage - 1})">Précédent</a>
                </li>
                ${this.generatePageNumbers(currentPage, totalPages)}
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadCustomers(${currentPage + 1})">Suivant</a>
                </li>
            </ul>
        `;
        pagination.appendChild(nav);
    }

    generatePageNumbers(currentPage, totalPages) {
        let pages = '';
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);

        for (let i = start; i <= end; i++) {
            pages += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadCustomers(${i})">${i}</a>
                </li>
            `;
        }
        return pages;
    }

    // Informations client sélectionné
    updateSelectedCustomerInfo(customer) {
        this.elements.selectedCustomerName.textContent = customer.name || 'N/A';
        this.elements.selectedCustomerEmail.textContent = customer.email || 'N/A';
        this.elements.selectedCustomerPhone.textContent = customer.phone || 'N/A';
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

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-primary">${order.order_number}</h6>
                    <p class="mb-1 text-sm"><strong>Date:</strong> ${this.formatDate(order.created_at)}</p>
                    <p class="mb-1 text-sm"><strong>Montant:</strong> ${order.total_amount || 0}€</p>
                    <p class="mb-0 text-sm"><strong>Adresse:</strong> ${order.delivery_address || 'N/A'}</p>
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
}