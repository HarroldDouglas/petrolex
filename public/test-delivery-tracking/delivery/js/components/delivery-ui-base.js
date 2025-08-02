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
