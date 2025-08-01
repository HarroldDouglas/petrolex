// Composants UI pour l'interface livreur avec authentification réelle
class DeliveryPersonUIComponents {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.loginPanel = document.getElementById('loginPanel');
        this.elements.deliveryPersonPanel = document.getElementById('deliveryPersonPanel');
        this.elements.deliveryControls = document.getElementById('deliveryControls');
        
        // Authentification
        this.elements.loginForm = document.getElementById('loginForm');
        this.elements.deliveryPersonEmail = document.getElementById('deliveryPersonEmail');
        this.elements.deliveryPersonPassword = document.getElementById('deliveryPersonPassword');
        this.elements.loginBtn = document.getElementById('loginBtn');
        this.elements.logoutBtn = document.getElementById('logoutBtn');
        
        // Informations livreur
        this.elements.currentDeliveryPersonName = document.getElementById('currentDeliveryPersonName');
        this.elements.currentDeliveryPersonEmail = document.getElementById('currentDeliveryPersonEmail');
        this.elements.currentStatus = document.getElementById('currentStatus');
        this.elements.connectionStatus = document.getElementById('connectionStatus');
        
        // Commandes
        this.elements.ordersList = document.getElementById('ordersList');
        this.elements.refreshOrdersBtn = document.getElementById('refreshOrdersBtn');
        this.elements.statusFilter = document.getElementById('statusFilter');
        this.elements.orderNumberFilter = document.getElementById('orderNumberFilter');
        this.elements.pagination = document.getElementById('pagination');
        
        // Détails commande sélectionnée
        this.elements.selectedOrderDetails = document.getElementById('selectedOrderDetails');
        this.elements.selectedOrderNumber = document.getElementById('selectedOrderNumber');
        this.elements.selectedCustomerName = document.getElementById('selectedCustomerName');
        this.elements.selectedCustomerPhone = document.getElementById('selectedCustomerPhone');
        this.elements.selectedDeliveryAddress = document.getElementById('selectedDeliveryAddress');
        this.elements.selectedOrderStatus = document.getElementById('selectedOrderStatus');
        this.elements.estimatedTime = document.getElementById('estimatedTime');
        this.elements.estimatedDistance = document.getElementById('estimatedDistance');
        
        // Mode de transport
        this.elements.transportModeSection = document.getElementById('transportModeSection');
        this.elements.transportWalking = document.getElementById('transportWalking');
        this.elements.transportDriving = document.getElementById('transportDriving');
        this.elements.simulationSpeedInfo = document.getElementById('simulationSpeedInfo');
        this.elements.routeTypeInfo = document.getElementById('routeTypeInfo');
        
        // Contrôles de livraison
        this.elements.simulationSpeed = document.getElementById('simulationSpeed');
        this.elements.startDeliveryBtn = document.getElementById('startDeliveryBtn');
        this.elements.pauseDeliveryBtn = document.getElementById('pauseDeliveryBtn');
        this.elements.stopDeliveryBtn = document.getElementById('stopDeliveryBtn');
        this.elements.progressPercent = document.getElementById('progressPercent');
        this.elements.progressBar = document.getElementById('progressBar');
        
        // Position actuelle
        this.elements.currentLocationSection = document.getElementById('currentLocationSection');
        this.elements.currentPosition = document.getElementById('currentPosition');
        this.elements.lastUpdate = document.getElementById('lastUpdate');
    }

    // Gestion des panneaux
    showLoginPanel() {
        this.elements.loginPanel.style.display = 'block';
        this.elements.deliveryPersonPanel.style.display = 'none';
        this.updateConnectionStatus(false);
    }

    showDeliveryPersonPanel() {
        this.elements.loginPanel.style.display = 'none';
        this.elements.deliveryPersonPanel.style.display = 'block';
        this.updateConnectionStatus(true);
    }

    showSelectedOrderDetails() {
        this.elements.selectedOrderDetails.style.display = 'block';
        this.elements.transportModeSection.style.display = 'block';
    }

    hideSelectedOrderDetails() {
        this.elements.selectedOrderDetails.style.display = 'none';
        this.elements.transportModeSection.style.display = 'none';
        this.elements.deliveryControls.style.display = 'none';
    }

    showDeliveryControls() {
        this.elements.deliveryControls.style.display = 'block';
    }

    hideDeliveryControls() {
        this.elements.deliveryControls.style.display = 'none';
    }

    // Authentification
    updateConnectionStatus(connected) {
        const statusClass = connected ? 'status-online' : 'status-offline';
        this.elements.connectionStatus.className = `status-indicator ${statusClass}`;
    }

    updateDeliveryPersonInfo(deliveryPerson) {
        this.elements.currentDeliveryPersonName.textContent = deliveryPerson.name || 'N/A';
        this.elements.currentDeliveryPersonEmail.textContent = deliveryPerson.email || 'N/A';
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

        this.updatePagination(currentPage, totalPages);
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
                    <p class="mb-1 text-sm"><strong>Client:</strong> ${order.customer?.name || 'N/A'}</p>
                    <p class="mb-1 text-sm"><strong>Téléphone:</strong> ${order.customer?.phone || 'N/A'}</p>
                    <p class="mb-1 text-sm"><strong>Adresse:</strong> ${order.delivery_address || 'N/A'}</p>
                    <p class="mb-0 text-sm"><strong>Montant:</strong> ${order.total_amount || 0}€</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-${statusColor} mb-2">${statusLabel}</span>
                    ${this.getOrderActions(order)}
                </div>
            </div>
        `;

        return div;
    }

    getOrderActions(order) {
        switch (order.status) {
            case CONFIG.ORDER_STATUS.CONFIRMED:
                return `<button class="btn btn-sm btn-success w-100" onclick="window.deliveryPersonApp.selectOrder('${order.order_number}')">
                    <i class="fas fa-play"></i> Sélectionner
                </button>`;
            case CONFIG.ORDER_STATUS.PROCESSING:
                return `<button class="btn btn-sm btn-warning w-100" onclick="window.deliveryPersonApp.selectOrder('${order.order_number}')">
                    <i class="fas fa-eye"></i> Voir détails
                </button>`;
            default:
                return `<button class="btn btn-sm btn-outline-secondary w-100" disabled>
                    <i class="fas fa-check"></i> Terminée
                </button>`;
        }
    }

    updatePagination(currentPage, totalPages) {
        const pagination = this.elements.pagination;
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        const nav = document.createElement('nav');
        nav.innerHTML = `
            <ul class="pagination pagination-sm">
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${currentPage - 1})">Précédent</a>
                </li>
                ${this.generatePageNumbers(currentPage, totalPages)}
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${currentPage + 1})">Suivant</a>
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
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${i})">${i}</a>
                </li>
            `;
        }
        return pages;
    }

    // Détails de la commande sélectionnée
    updateSelectedOrderDetails(order) {
        this.elements.selectedOrderNumber.textContent = order.order_number;
        this.elements.selectedCustomerName.textContent = order.customer?.name || 'N/A';
        this.elements.selectedCustomerPhone.textContent = order.customer?.phone || 'N/A';
        this.elements.selectedDeliveryAddress.textContent = order.delivery_address || 'N/A';
        
        const statusColor = CONFIG.STATUS.COLORS[order.status] || 'secondary';
        const statusLabel = CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
        this.elements.selectedOrderStatus.textContent = statusLabel;
        this.elements.selectedOrderStatus.className = `badge bg-${statusColor}`;
        
        this.showSelectedOrderDetails();
    }

    // Estimations de route
    updateRouteEstimates(time, distance, isRealTime = false) {
        if (time !== null && distance !== null) {
            const timeText = isRealTime ? `${time} min restant` : `${time} min`;
            const distanceText = isRealTime ? `${distance} km restant` : `${distance} km`;
            
            this.elements.estimatedTime.textContent = timeText;
            this.elements.estimatedDistance.textContent = distanceText;
        } else {
            this.elements.estimatedTime.textContent = 'Non disponible';
            this.elements.estimatedDistance.textContent = 'Non disponible';
        }
    }

    // Mode de transport
    getSelectedTransportMode() {
        return this.elements.transportWalking.checked ? 'walking' : 'driving';
    }

    updateTransportInfo() {
        const selectedMode = this.getSelectedTransportMode();
        
        if (selectedMode === 'driving') {
            this.elements.simulationSpeedInfo.textContent = 'Très rapide';
            this.elements.routeTypeInfo.textContent = 'Moto/Route';
        } else {
            this.elements.simulationSpeedInfo.textContent = 'Normale';
            this.elements.routeTypeInfo.textContent = 'Piétonne';
        }
    }

    // Contrôles de livraison
    updateProgress(percent) {
        const roundedPercent = Math.round(percent);
        this.elements.progressPercent.textContent = `${roundedPercent}%`;
        this.elements.progressBar.style.width = `${percent}%`;
    }

    setDeliveryControlsState(isTracking, isPaused) {
        this.elements.startDeliveryBtn.disabled = isTracking && !isPaused;
        this.elements.pauseDeliveryBtn.disabled = !isTracking || isPaused;
        this.elements.stopDeliveryBtn.disabled = !isTracking;
        
        if (isPaused) {
            this.elements.startDeliveryBtn.innerHTML = '<i class="fas fa-play"></i> Reprendre';
        } else {
            this.elements.startDeliveryBtn.innerHTML = '<i class="fas fa-play"></i> Démarrer Livraison';
        }
    }

    getSimulationSpeed() {
        return parseInt(this.elements.simulationSpeed.value);
    }

    // Position actuelle
    updateCurrentPosition(position) {
        if (position) {
            this.elements.currentPosition.textContent = `${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}`;
            this.elements.lastUpdate.textContent = new Date().toLocaleTimeString();
            this.elements.currentLocationSection.style.display = 'block';
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

    // Utilitaires
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

    clearFilters() {
        this.elements.statusFilter.value = '';
        this.elements.orderNumberFilter.value = '';
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
}