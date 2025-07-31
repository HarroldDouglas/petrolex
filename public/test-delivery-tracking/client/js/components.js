// Composants UI pour la gestion de l'interface
class UIComponents {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.setupPanel = document.getElementById('setupPanel');
        this.elements.trackingPanel = document.getElementById('trackingPanel');
        
        // Formulaires
        this.elements.trackingForm = document.getElementById('trackingForm');
        this.elements.orderNumber = document.getElementById('orderNumber');
        
        // Affichage des informations
        this.elements.currentOrderNumber = document.getElementById('currentOrderNumber');
        this.elements.connectionStatus = document.getElementById('connectionStatus');
        this.elements.deliveryStatus = document.getElementById('deliveryStatus');
        this.elements.deliveryStatusIndicator = document.getElementById('deliveryStatusIndicator');
        this.elements.progressBar = document.getElementById('progressBar');
        this.elements.etaDisplay = document.getElementById('etaDisplay');
        this.elements.distanceDisplay = document.getElementById('distanceDisplay');
        
        // Informations du livreur
        this.elements.driverInfo = document.getElementById('driverInfo');
        this.elements.driverName = document.getElementById('driverName');
        this.elements.driverPhone = document.getElementById('driverPhone');
        
        // Listes et historique
        this.elements.availableOrders = document.getElementById('availableOrders');
        this.elements.deliveryHistory = document.getElementById('deliveryHistory');
    }

    showTrackingPanel() {
        this.elements.setupPanel.style.display = 'none';
        this.elements.trackingPanel.style.display = 'block';
    }

    showSetupPanel() {
        this.elements.setupPanel.style.display = 'block';
        this.elements.trackingPanel.style.display = 'none';
    }

    updateConnectionStatus(isConnected) {
        const statusClass = isConnected ? 'status-delivered' : 'status-pending';
        this.elements.connectionStatus.className = `status-indicator ${statusClass}`;
    }

    updateOrderNumber(orderNumber) {
        this.elements.currentOrderNumber.textContent = orderNumber;
    }

    updateDeliveryStatus(status) {
        const translatedStatus = CONFIG.STATUS.TRANSLATIONS[status] || status;
        this.elements.deliveryStatus.textContent = translatedStatus;
        this.elements.deliveryStatusIndicator.className = `status-indicator status-${status}`;
        
        // Mettre à jour la barre de progression
        const progress = CONFIG.STATUS.PROGRESS[status] || 0;
        this.elements.progressBar.style.width = progress + '%';
        this.elements.progressBar.setAttribute('aria-valuenow', progress);
    }

    updateDriverInfo(driverData) {
        if (driverData.driver_name) {
            this.elements.driverInfo.style.display = 'block';
            this.elements.driverName.textContent = driverData.driver_name;
            this.elements.driverPhone.textContent = driverData.driver_phone || 'Non renseigné';
        } else {
            this.elements.driverInfo.style.display = 'none';
        }
    }

    updateETA(duration, distance) {
        this.elements.etaDisplay.textContent = duration ? `${duration} min` : '--';
        this.elements.distanceDisplay.textContent = distance ? `${distance} km` : '--';
    }

    addToHistory(message) {
        const historyContainer = this.elements.deliveryHistory;
        const div = document.createElement('div');
        div.className = 'border-bottom pb-1 mb-1';
        div.innerHTML = `<small class="text-muted">${message}</small>`;
        
        historyContainer.insertBefore(div, historyContainer.firstChild);
        
        // Limiter le nombre d'éléments dans l'historique
        while (historyContainer.children.length > CONFIG.UI.HISTORY_MAX_ITEMS) {
            historyContainer.removeChild(historyContainer.lastChild);
        }
    }

    clearHistory() {
        this.elements.deliveryHistory.innerHTML = '';
    }

    renderAvailableOrders(deliveries) {
        const container = this.elements.availableOrders;
        container.innerHTML = `
            <button class="btn btn-sm btn-outline-primary mb-2" onclick="clientApp.refreshOrders()">
                🔄 Actualiser
            </button>
        `;

        if (deliveries.length === 0) {
            container.innerHTML += '<p class="text-muted">Aucune commande active</p>';
            return;
        }

        deliveries.forEach(delivery => {
            const div = document.createElement('div');
            div.className = 'border rounded p-2 mb-2';
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small><strong>${delivery.order_number || 'N/A'}</strong></small><br>
                        <small class="text-muted">${delivery.customer_name || 'Client'}</small><br>
                        <small class="badge bg-${this.getStatusBadgeColor(delivery.status)}">
                            ${CONFIG.STATUS.TRANSLATIONS[delivery.status] || delivery.status}
                        </small>
                    </div>
                    <button class="btn btn-sm btn-success" onclick="clientApp.selectOrder('${delivery.order_number}')">
                        👁️ Suivre
                    </button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    getStatusBadgeColor(status) {
        return CONFIG.STATUS.BADGE_COLORS[status] || 'secondary';
    }

    showError(message, title = 'Erreur') {
        console.error(title + ':', message);
        alert(message); // En production, utiliser une modal plus sophistiquée
    }

    showSuccess(message, title = 'Succès') {
        console.log(title + ':', message);
        // En production, afficher une notification toast
    }

    setLoadingState(isLoading, elementId = null) {
        if (elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.disabled = isLoading;
                if (isLoading) {
                    element.classList.add('loading');
                } else {
                    element.classList.remove('loading');
                }
            }
        }
    }
}

// Gestionnaire de formulaires
class FormHandler {
    constructor(ui, callbacks = {}) {
        this.ui = ui;
        this.callbacks = callbacks;
        this.bindEvents();
    }

    bindEvents() {
        // Formulaire de suivi
        this.ui.elements.trackingForm.addEventListener('submit', (e) => {
            this.handleTrackingSubmit(e);
        });
    }

    async handleTrackingSubmit(e) {
        e.preventDefault();
        
        const orderNumber = this.ui.elements.orderNumber.value.trim();
        
        if (!orderNumber) {
            this.ui.showError('Veuillez saisir un numéro de commande');
            return;
        }

        this.ui.setLoadingState(true, 'trackingSubmitBtn');
        
        try {
            if (this.callbacks.onTrackingStart) {
                await this.callbacks.onTrackingStart(orderNumber);
            }
        } catch (error) {
            this.ui.showError('Erreur lors du démarrage du suivi: ' + error.message);
        } finally {
            this.ui.setLoadingState(false, 'trackingSubmitBtn');
        }
    }

    resetForm() {
        this.ui.elements.orderNumber.value = '';
    }
}

// Gestionnaire d'événements pour les actions utilisateur
class EventHandler {
    constructor(callbacks = {}) {
        this.callbacks = callbacks;
    }

    callDriver() {
        if (this.callbacks.onCallDriver) {
            this.callbacks.onCallDriver();
        }
    }

    resetTracking() {
        if (this.callbacks.onResetTracking) {
            this.callbacks.onResetTracking();
        }
    }

    selectOrder(orderNumber) {
        if (this.callbacks.onSelectOrder) {
            this.callbacks.onSelectOrder(orderNumber);
        }
    }

    refreshOrders() {
        if (this.callbacks.onRefreshOrders) {
            this.callbacks.onRefreshOrders();
        }
    }
}