// Composants UI pour la simulation livreur
class DeliveryUIComponents {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.setupPanel = document.getElementById('setupPanel');
        this.elements.deliveryPanel = document.getElementById('deliveryPanel');
        this.elements.simulationControls = document.getElementById('simulationControls');
        
        // Formulaires
        this.elements.driverSetup = document.getElementById('driverSetup');
        this.elements.createDeliveryForm = document.getElementById('createDeliveryForm');
        
        // Configuration livreur
        this.elements.driverName = document.getElementById('driverName');
        this.elements.driverPhone = document.getElementById('driverPhone');
        this.elements.startLat = document.getElementById('startLat');
        this.elements.startLng = document.getElementById('startLng');
        
        // Création de livraison
        this.elements.customerName = document.getElementById('customerName');
        this.elements.destinationAddress = document.getElementById('destinationAddress');
        this.elements.destLat = document.getElementById('destLat');
        this.elements.destLng = document.getElementById('destLng');
        
        // Affichage d'informations
        this.elements.connectionStatus = document.getElementById('connectionStatus');
        this.elements.currentDriverName = document.getElementById('currentDriverName');
        this.elements.currentPosition = document.getElementById('currentPosition');
        this.elements.currentStatus = document.getElementById('currentStatus');
        this.elements.deliveriesList = document.getElementById('deliveriesList');
        
        // Détails de l'ordre sélectionné
        this.elements.selectedOrderDetails = document.getElementById('selectedOrderDetails');
        this.elements.selectedOrderNumber = document.getElementById('selectedOrderNumber');
        this.elements.selectedCustomerName = document.getElementById('selectedCustomerName');
        this.elements.selectedDestination = document.getElementById('selectedDestination');
        this.elements.selectedStatus = document.getElementById('selectedStatus');
        this.elements.estimatedTime = document.getElementById('estimatedTime');
        this.elements.estimatedDistance = document.getElementById('estimatedDistance');
        
        // Mode de transport
        this.elements.transportModeSection = document.getElementById('transportModeSection');
        this.elements.transportWalking = document.getElementById('transportWalking');
        this.elements.transportDriving = document.getElementById('transportDriving');
        this.elements.simulationSpeedInfo = document.getElementById('simulationSpeedInfo');
        this.elements.routeTypeInfo = document.getElementById('routeTypeInfo');
        
        // Contrôles de simulation
        this.elements.simulationSpeed = document.getElementById('simulationSpeed');
        this.elements.startSimulation = document.getElementById('startSimulation');
        this.elements.pauseSimulation = document.getElementById('pauseSimulation');
        this.elements.stopSimulation = document.getElementById('stopSimulation');
        this.elements.progressPercent = document.getElementById('progressPercent');
        this.elements.progressBar = document.getElementById('progressBar');
    }

    showDeliveryPanel() {
        this.elements.setupPanel.style.display = 'none';
        this.elements.deliveryPanel.style.display = 'block';
    }

    showSetupPanel() {
        this.elements.setupPanel.style.display = 'block';
        this.elements.deliveryPanel.style.display = 'none';
    }

    showSimulationControls() {
        this.elements.simulationControls.style.display = 'block';
    }

    hideSimulationControls() {
        this.elements.simulationControls.style.display = 'none';
    }

    updateConnectionStatus(connected) {
        const statusClass = connected ? 'status-online' : 'status-offline';
        this.elements.connectionStatus.className = `status-indicator ${statusClass}`;
    }

    updateDriverInfo(name, position) {
        this.elements.currentDriverName.textContent = name;
        this.elements.currentPosition.textContent = `${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}`;
    }

    updateDriverStatus(status, statusClass = 'secondary') {
        this.elements.currentStatus.textContent = status;
        this.elements.currentStatus.className = `badge bg-${statusClass}`;
    }

    updateProgress(percent) {
        const roundedPercent = Math.round(percent);
        this.elements.progressPercent.textContent = `${roundedPercent}%`;
        this.elements.progressBar.style.width = `${percent}%`;
    }

    setSimulationControlsState(isRunning, isPaused) {
        this.elements.startSimulation.disabled = isRunning && !isPaused;
        this.elements.pauseSimulation.disabled = !isRunning || isPaused;
        this.elements.stopSimulation.disabled = !isRunning;
        
        if (isPaused) {
            this.elements.startSimulation.textContent = 'Reprendre';
        } else {
            this.elements.startSimulation.textContent = 'Démarrer livraison';
        }
    }

    renderDeliveries(deliveries, driverName) {
        const list = this.elements.deliveriesList;
        list.innerHTML = '';

        const myDeliveries = deliveries.filter(d => d.driver_name === driverName);

        if (myDeliveries.length === 0) {
            list.innerHTML = '<p class="text-muted">Aucune livraison</p>';
            return;
        }

        myDeliveries.forEach(delivery => {
            const div = document.createElement('div');
            div.className = 'border rounded p-2 mb-2';
            div.innerHTML = `
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${delivery.order_number}</strong><br>
                        <small>Client: ${delivery.customer_name}</small><br>
                        <small>Destination: ${delivery.destination_address}</small>
                    </div>
                    <div>
                        <span class="badge bg-${this.getStatusColor(delivery.status)}">
                            ${CONFIG.STATUS.TRANSLATIONS[delivery.status] || delivery.status}
                        </span>
                        ${delivery.status === 'pending' ? 
                            `<button class="btn btn-sm btn-success ms-1" onclick="deliveryApp.selectDelivery('${delivery.order_number}')">
                                Sélectionner
                            </button>` 
                            : ''}
                    </div>
                </div>
            `;
            list.appendChild(div);
        });
    }

    getStatusColor(status) {
        return CONFIG.STATUS.COLORS[status] || 'secondary';
    }

    populateFormWithDefaults() {
        this.elements.driverName.value = CONFIG.DRIVER.DEFAULT_NAME;
        this.elements.driverPhone.value = CONFIG.DRIVER.DEFAULT_PHONE;
        this.elements.startLat.value = CONFIG.DRIVER.DEFAULT_POSITION.lat;
        this.elements.startLng.value = CONFIG.DRIVER.DEFAULT_POSITION.lng;
        
        this.elements.customerName.value = CONFIG.DELIVERY.DEFAULT_CUSTOMER;
        this.elements.destinationAddress.value = CONFIG.DELIVERY.DEFAULT_ADDRESS;
        this.elements.destLat.value = CONFIG.DELIVERY.DEFAULT_DESTINATION.lat;
        this.elements.destLng.value = CONFIG.DELIVERY.DEFAULT_DESTINATION.lng;
        
        this.elements.simulationSpeed.value = CONFIG.SIMULATION.DEFAULT_SPEED;
    }

    updateMapPosition(lat, lng) {
        this.elements.startLat.value = lat.toFixed(6);
        this.elements.startLng.value = lng.toFixed(6);
    }

    getDriverFormData() {
        return {
            name: this.elements.driverName.value,
            phone: this.elements.driverPhone.value,
            lat: parseFloat(this.elements.startLat.value),
            lng: parseFloat(this.elements.startLng.value)
        };
    }

    getDeliveryFormData() {
        return {
            customer_name: this.elements.customerName.value,
            destination_address: this.elements.destinationAddress.value,
            destination_lat: parseFloat(this.elements.destLat.value),
            destination_lng: parseFloat(this.elements.destLng.value)
        };
    }

    getSimulationSpeed() {
        return parseInt(this.elements.simulationSpeed.value);
    }

    clearDeliveryForm() {
        this.elements.createDeliveryForm.reset();
        this.populateFormWithDefaults();
    }

    showError(message, title = 'Erreur') {
        console.error(title + ':', message);
        this.showNotification('error', title, message, 5000);
    }

    showSuccess(message, title = 'Succès') {
        console.log(title + ':', message);
        this.showNotification('success', title, message, 4000);
    }

    showNotification(type, title, message, duration = 4000) {
        // Créer l'élément de notification
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

        // Ajouter au body
        document.body.appendChild(notification);

        // Auto-suppression après la durée spécifiée
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

    setLoadingState(isLoading, buttonId = null) {
        if (buttonId) {
            const button = document.getElementById(buttonId);
            if (button) {
                button.disabled = isLoading;
                if (isLoading) {
                    button.classList.add('loading');
                } else {
                    button.classList.remove('loading');
                }
            }
        }
    }

    showSelectedOrderDetails(delivery) {
        this.elements.selectedOrderDetails.style.display = 'block';
        this.elements.selectedOrderNumber.textContent = delivery.order_number;
        this.elements.selectedCustomerName.textContent = delivery.customer_name;
        this.elements.selectedDestination.textContent = delivery.destination_address;
        
        // Mettre à jour le badge de statut
        const statusColor = this.getStatusColor(delivery.status);
        this.elements.selectedStatus.textContent = CONFIG.STATUS.TRANSLATIONS[delivery.status] || delivery.status;
        this.elements.selectedStatus.className = `badge bg-${statusColor}`;
        
        // Réinitialiser les estimations
        this.elements.estimatedTime.textContent = 'Calcul...';
        this.elements.estimatedDistance.textContent = 'Calcul...';
        
        // Afficher la section du mode de transport
        this.showTransportModeSection();
    }

    hideSelectedOrderDetails() {
        this.elements.selectedOrderDetails.style.display = 'none';
        this.hideTransportModeSection();
    }

    showTransportModeSection() {
        this.elements.transportModeSection.style.display = 'block';
        this.setupTransportModeListeners();
        this.updateTransportInfo(); // Mettre à jour les infos initiales
    }

    hideTransportModeSection() {
        this.elements.transportModeSection.style.display = 'none';
    }

    setupTransportModeListeners() {
        // Éviter les multiples listeners
        if (this.transportListenersSetup) return;
        
        [this.elements.transportWalking, this.elements.transportDriving].forEach(radio => {
            radio.addEventListener('change', () => {
                this.updateTransportInfo();
                // Déclencher un recalcul de route si nécessaire
                if (window.deliveryApp && window.deliveryApp.selectedDelivery) {
                    window.deliveryApp.recalculateRouteForTransportMode();
                }
            });
        });
        
        this.transportListenersSetup = true;
    }

    updateTransportInfo() {
        const selectedMode = this.getSelectedTransportMode();
        const modeConfig = CONFIG.SIMULATION.TRANSPORT_MODES[selectedMode];
        
        if (modeConfig) {
            // Mettre à jour les textes d'information selon le mode sélectionné
            if (selectedMode === 'driving') {
                this.elements.simulationSpeedInfo.textContent = 'Très rapide';
                this.elements.routeTypeInfo.textContent = 'Moto/Route';
            } else {
                this.elements.simulationSpeedInfo.textContent = 'Normale';
                this.elements.routeTypeInfo.textContent = 'Piétonne';
            }
            
            // Animation de changement - utiliser l'ID de l'input radio au lieu d'un sélecteur CSS inexistant
            const selectedInput = selectedMode === 'driving' ? this.elements.transportDriving : this.elements.transportWalking;
            const transportOption = selectedInput.closest('.transport-option');
            
            if (transportOption) {
                transportOption.classList.add('switching');
                setTimeout(() => {
                    transportOption.classList.remove('switching');
                }, 300);
            }
        }
    }

    getSelectedTransportMode() {
        return this.elements.transportWalking.checked ? 'walking' : 'driving';
    }

    updateRouteEstimates(time, distance, isRealTime = false) {
        if (time !== null && distance !== null) {
            const timeText = isRealTime ? `${time} min restant` : `${time} min`;
            const distanceText = isRealTime ? `${distance} km restant` : `${distance} km`;
            
            this.elements.estimatedTime.textContent = timeText;
            this.elements.estimatedDistance.textContent = distanceText;
            
            // Animation pour les mises à jour en temps réel
            if (isRealTime) {
                [this.elements.estimatedTime, this.elements.estimatedDistance].forEach(el => {
                    el.style.animation = 'none';
                    setTimeout(() => {
                        el.style.animation = 'gentle-pulse 0.5s ease-in-out';
                    }, 10);
                });
            }
        } else {
            this.elements.estimatedTime.textContent = 'Non disponible';
            this.elements.estimatedDistance.textContent = 'Non disponible';
        }
    }

    highlightSelectedDelivery(orderNumber) {
        // Supprimer la surbrillance de tous les éléments
        document.querySelectorAll('#deliveriesList .delivery-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Ajouter la classe delivery-item et selected à l'élément sélectionné
        document.querySelectorAll('#deliveriesList .border').forEach(item => {
            item.classList.add('delivery-item');
            if (item.innerHTML.includes(orderNumber)) {
                item.classList.add('selected');
                
                // Scroll vers l'élément sélectionné
                item.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }
        });
    }
}

// Gestionnaire de formulaires pour le livreur
class DeliveryFormHandler {
    constructor(ui, callbacks = {}) {
        this.ui = ui;
        this.callbacks = callbacks;
        this.bindEvents();
    }

    bindEvents() {
        // Configuration du livreur
        this.ui.elements.driverSetup.addEventListener('submit', (e) => {
            this.handleDriverSetup(e);
        });

        // Création de livraison
        this.ui.elements.createDeliveryForm.addEventListener('submit', (e) => {
            this.handleCreateDelivery(e);
        });

        // Contrôles de simulation
        this.ui.elements.startSimulation.addEventListener('click', () => {
            this.handleStartSimulation();
        });

        this.ui.elements.pauseSimulation.addEventListener('click', () => {
            this.handlePauseSimulation();
        });

        this.ui.elements.stopSimulation.addEventListener('click', () => {
            this.handleStopSimulation();
        });
    }

    async handleDriverSetup(e) {
        e.preventDefault();
        
        const driverData = this.ui.getDriverFormData();
        
        if (!this.validateDriverData(driverData)) {
            return;
        }

        try {
            if (this.callbacks.onDriverSetup) {
                await this.callbacks.onDriverSetup(driverData);
            }
        } catch (error) {
            this.ui.showError('Erreur lors de la configuration du livreur: ' + error.message);
        }
    }

    async handleCreateDelivery(e) {
        e.preventDefault();
        
        const deliveryData = this.ui.getDeliveryFormData();
        const driverData = this.ui.getDriverFormData();
        
        const completeData = {
            ...deliveryData,
            driver_name: driverData.name,
            driver_phone: driverData.phone
        };

        if (!this.validateDeliveryData(completeData)) {
            return;
        }

        this.ui.setLoadingState(true, 'createDeliveryBtn');

        try {
            if (this.callbacks.onCreateDelivery) {
                await this.callbacks.onCreateDelivery(completeData);
            }
        } catch (error) {
            this.ui.showError('Erreur lors de la création de la livraison: ' + error.message);
        } finally {
            this.ui.setLoadingState(false, 'createDeliveryBtn');
        }
    }

    handleStartSimulation() {
        if (this.callbacks.onStartSimulation) {
            const speed = this.ui.getSimulationSpeed();
            this.callbacks.onStartSimulation(speed);
        }
    }

    handlePauseSimulation() {
        if (this.callbacks.onPauseSimulation) {
            this.callbacks.onPauseSimulation();
        }
    }

    handleStopSimulation() {
        if (this.callbacks.onStopSimulation) {
            this.callbacks.onStopSimulation();
        }
    }

    validateDriverData(data) {
        if (!data.name || data.name.trim() === '') {
            this.ui.showError('Le nom du livreur est requis');
            return false;
        }
        
        if (!data.phone || data.phone.trim() === '') {
            this.ui.showError('Le téléphone du livreur est requis');
            return false;
        }
        
        if (isNaN(data.lat) || isNaN(data.lng)) {
            this.ui.showError('Position GPS invalide');
            return false;
        }
        
        return true;
    }

    validateDeliveryData(data) {
        if (!data.customer_name || data.customer_name.trim() === '') {
            this.ui.showError('Le nom du client est requis');
            return false;
        }
        
        if (!data.destination_address || data.destination_address.trim() === '') {
            this.ui.showError('L\'adresse de destination est requise');
            return false;
        }
        
        if (isNaN(data.destination_lat) || isNaN(data.destination_lng)) {
            this.ui.showError('Coordonnées de destination invalides');
            return false;
        }
        
        return true;
    }
}

// Gestionnaire d'événements pour les actions spécifiques au livreur
class DeliveryEventHandler {
    constructor(callbacks = {}) {
        this.callbacks = callbacks;
    }

    selectDelivery(orderNumber) {
        if (this.callbacks.onSelectDelivery) {
            this.callbacks.onSelectDelivery(orderNumber);
        }
    }

    refreshDeliveries() {
        if (this.callbacks.onRefreshDeliveries) {
            this.callbacks.onRefreshDeliveries();
        }
    }

    handleMapClick(lat, lng) {
        if (this.callbacks.onMapClick) {
            this.callbacks.onMapClick(lat, lng);
        }
    }
}