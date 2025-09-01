// Classe de base pour la gestion des éléments DOM et des panneaux
class DeliveryUIBase {
    constructor() {
        this.elements = {};
        this.initElements();
    }

    initElements() {
        // Panneaux principaux
        this.elements.loginPanel = document.getElementById("loginPanel");
        this.elements.deliveryPersonPanel = document.getElementById(
            "deliveryPersonPanel",
        );
        this.elements.deliveryControls =
            document.getElementById("deliveryControls");

        // Authentification
        this.elements.loginForm = document.getElementById("loginForm");
        this.elements.deliveryPersonEmail = document.getElementById(
            "deliveryPersonEmail",
        );
        this.elements.deliveryPersonPassword = document.getElementById(
            "deliveryPersonPassword",
        );
        this.elements.loginBtn = document.getElementById("loginBtn");
        this.elements.logoutBtn = document.getElementById("logoutBtn");

        // Informations livreur
        this.elements.currentDeliveryPersonName = document.getElementById(
            "currentDeliveryPersonName",
        );
        this.elements.currentDeliveryPersonEmail = document.getElementById(
            "currentDeliveryPersonEmail",
        );
        this.elements.currentStatus = document.getElementById("currentStatus");
        this.elements.connectionStatus =
            document.getElementById("connectionStatus");

        // Commandes
        this.elements.ordersList = document.getElementById("ordersList");
        this.elements.refreshOrdersBtn =
            document.getElementById("refreshOrdersBtn");
        this.elements.statusFilter = document.getElementById("statusFilter");
        this.elements.orderNumberFilter =
            document.getElementById("orderNumberFilter");
        this.elements.pagination = document.getElementById("pagination");

        // Détails commande sélectionnée
        this.elements.selectedOrderDetails = document.getElementById(
            "selectedOrderDetails",
        );
        this.elements.selectedOrderNumber = document.getElementById(
            "selectedOrderNumber",
        );
        this.elements.selectedCustomerName = document.getElementById(
            "selectedCustomerName",
        );
        this.elements.selectedCustomerPhone = document.getElementById(
            "selectedCustomerPhone",
        );
        this.elements.selectedStartAddress = document.getElementById(
            "selectedStartAddress",
        );
        this.elements.selectedDeliveryAddress = document.getElementById(
            "selectedDeliveryAddress",
        );
        this.elements.currentSpeed = document.getElementById("currentSpeed");
        this.elements.selectedOrderStatus = document.getElementById(
            "selectedOrderStatus",
        );
        this.elements.estimatedTime = document.getElementById("estimatedTime");
        this.elements.estimatedDistance =
            document.getElementById("estimatedDistance");

        // Contrôles de livraison
        this.elements.simulationSpeed =
            document.getElementById("simulationSpeed");
        this.elements.currentTravelSpeedDisplay = document.getElementById(
            "currentTravelSpeedDisplay",
        );
        this.elements.updateSpeedBtn =
            document.getElementById("updateSpeedBtn");
        this.elements.startDeliveryBtn =
            document.getElementById("startDeliveryBtn");
        this.elements.pauseDeliveryBtn =
            document.getElementById("pauseDeliveryBtn");
        this.elements.stopDeliveryBtn =
            document.getElementById("stopDeliveryBtn");
        this.elements.completeDeliveryBtn = document.getElementById(
            "completeDeliveryBtn",
        );
        this.elements.progressPercent =
            document.getElementById("progressPercent");
        this.elements.progressBar = document.getElementById("progressBar");

        // Position actuelle
        this.elements.currentLocationSection = document.getElementById(
            "currentLocationSection",
        );
        this.elements.currentPosition =
            document.getElementById("currentPosition");
        this.elements.lastUpdate = document.getElementById("lastUpdate");
    }

    // Gestion des panneaux
    showLoginPanel() {
        this.elements.loginPanel.style.display = "block";
        this.elements.deliveryPersonPanel.style.display = "none";
        this.updateConnectionStatus(false);
    }

    showDeliveryPersonPanel() {
        this.elements.loginPanel.style.display = "none";
        this.elements.deliveryPersonPanel.style.display = "block";
        this.updateConnectionStatus(true);
    }

    showSelectedOrderDetails() {
        this.elements.selectedOrderDetails.style.display = "block";
    }

    hideSelectedOrderDetails() {
        this.elements.selectedOrderDetails.style.display = "none";
        this.elements.deliveryControls.style.display = "none";
    }

    showDeliveryControls() {
        this.elements.deliveryControls.style.display = "block";
    }

    hideDeliveryControls() {
        this.elements.deliveryControls.style.display = "none";
    }

    // Authentification
    updateConnectionStatus(connected) {
        const statusClass = connected ? "status-online" : "status-offline";
        this.elements.connectionStatus.className = `status-indicator ${statusClass}`;
    }

    updateDeliveryPersonInfo(deliveryPerson) {
        const fullName =
            [deliveryPerson.first_name, deliveryPerson.last_name]
                .filter(Boolean)
                .join(" ") || "N/A";

        this.elements.currentDeliveryPersonName.textContent = fullName;
        this.elements.currentDeliveryPersonEmail.textContent =
            deliveryPerson.email || "N/A";
    }

    // Utilitaires
    setLoadingState(element, isLoading) {
        if (typeof element === "string") {
            element = document.getElementById(element);
        }

        if (element) {
            element.disabled = isLoading;
            if (isLoading) {
                element.classList.add("loading");
                const originalText = element.innerHTML;
                element.dataset.originalText = originalText;
                element.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Chargement...';
            } else {
                element.classList.remove("loading");
                if (element.dataset.originalText) {
                    element.innerHTML = element.dataset.originalText;
                    delete element.dataset.originalText;
                }
            }
        }
    }

    clearFilters() {
        this.elements.statusFilter.value = "";
        this.elements.orderNumberFilter.value = "";
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
}

// Strategies pour l'affichage des contrôles selon l'état
class ButtonDisplayStrategy {
    configure(context = {}) {
        throw new Error('Must implement configure method');
    }
}

class ConfirmedOrderStrategy extends ButtonDisplayStrategy {
    configure(context = {}) {
        return {
            showSpeedControl: true,
            showProgressBar: false,
            buttons: {
                start: { 
                    visible: true, 
                    enabled: true, 
                    text: '<i class="fas fa-play"></i> Démarrer',
                    class: 'btn btn-success'
                },
                pause: { visible: false },
                stop: { visible: false },
                complete: { visible: false }
            }
        };
    }
}

class InProgressOrderStrategy extends ButtonDisplayStrategy {
    configure(context = {}) {
        return {
            showSpeedControl: true,
            showProgressBar: true,
            buttons: {
                start: { 
                    visible: true, 
                    enabled: true, 
                    text: '<i class="fas fa-play"></i> Continuer',
                    class: 'btn btn-primary'
                },
                pause: { visible: false },
                stop: { visible: false },
                complete: { visible: false }
            }
        };
    }
}

class ActiveTrackingStrategy extends ButtonDisplayStrategy {
    configure(context = {}) {
        const progress = context.progress || 0;
        const isPaused = context.isPaused || false;
        
        return {
            showSpeedControl: false,
            showProgressBar: true,
            buttons: {
                start: { visible: false },
                pause: { 
                    visible: !isPaused, 
                    enabled: true, 
                    text: '<i class="fas fa-pause"></i> Pause',
                    class: 'btn btn-warning'
                },
                stop: { 
                    visible: true, 
                    enabled: true, 
                    text: '<i class="fas fa-stop"></i> Arrêter',
                    class: 'btn btn-danger'
                },
                complete: { 
                    visible: progress >= 100, 
                    enabled: progress >= 100,
                    text: '<i class="fas fa-check"></i> Terminer',
                    class: 'btn btn-success'
                }
            }
        };
    }
}

class PausedTrackingStrategy extends ButtonDisplayStrategy {
    configure(context = {}) {
        const progress = context.progress || 0;
        
        return {
            showSpeedControl: false,
            showProgressBar: true,
            buttons: {
                start: { 
                    visible: true, 
                    enabled: true, 
                    text: '<i class="fas fa-play"></i> Reprendre',
                    class: 'btn btn-success'
                },
                pause: { visible: false },
                stop: { 
                    visible: true, 
                    enabled: true, 
                    text: '<i class="fas fa-stop"></i> Arrêter',
                    class: 'btn btn-danger'
                },
                complete: { 
                    visible: progress >= 100, 
                    enabled: progress >= 100,
                    text: '<i class="fas fa-check"></i> Terminer',
                    class: 'btn btn-success'
                }
            }
        };
    }
}

// Gestionnaire des contrôles avec les stratégies
class DeliveryControlsManager {
    constructor(elements) {
        this.elements = elements;
        this.strategies = {
            'confirmed': new ConfirmedOrderStrategy(),
            'in_progress': new InProgressOrderStrategy(),
            'tracking_active': new ActiveTrackingStrategy(),
            'tracking_paused': new PausedTrackingStrategy()
        };
    }
    
    updateControlsForState(stateName, context = {}) {
        const strategy = this.strategies[stateName];
        if (!strategy) {
            console.warn(`No strategy found for state: ${stateName}`);
            return;
        }
        
        const config = strategy.configure(context);
        this.applyConfiguration(config);
    }
    
    applyConfiguration(config) {
        // Afficher/masquer la section des contrôles
        this.elements.deliveryControls.style.display = 'block';
        
        // Configurer la vitesse de simulation
        const speedSection = this.elements.simulationSpeed?.closest('.mb-3');
        if (speedSection) {
            speedSection.style.display = config.showSpeedControl ? 'block' : 'none';
        }
        
        // Configurer la barre de progression
        const progressSection = this.elements.progressBar?.closest('.progress-container, .mb-3');
        if (progressSection) {
            progressSection.style.display = config.showProgressBar ? 'block' : 'none';
        }
        
        // Configurer les boutons
        Object.entries(config.buttons).forEach(([buttonName, settings]) => {
            const element = this.elements[`${buttonName}DeliveryBtn`];
            if (element && settings) {
                element.style.display = settings.visible ? 'inline-block' : 'none';
                element.disabled = !settings.enabled;
                if (settings.text) element.innerHTML = settings.text;
                if (settings.class) element.className = settings.class;
            }
        });
    }
}
