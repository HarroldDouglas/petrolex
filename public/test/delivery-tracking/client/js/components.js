// Orchestrateur principal des composants UI client
// Importe et coordonne tous les composants
// Classe principale qui orchestre tous les composants


class CustomerUIComponents {
    constructor() {
        this.uiManager = new UIManager();
        this.orderManager = null; // Sera initialisé plus tard avec le controller
        this.trackingManager = new TrackingManager(this.uiManager);
        this.loginForm = null;

        this.init();
    }

    init() {
        this.uiManager.initElements();
        this.bindGlobalEvents();
    }
    
    // Méthode pour initialiser l'orderManager avec le controller
    initOrderManager(orderController) {
        if (!this.orderManager && orderController) {
            this.orderManager = new CustomerOrderManager(orderController, this.uiManager);
            console.log('📦 [Components] OrderManager initialisé');
        }
    }

    bindGlobalEvents() {
        // Events globaux de l'application
        document.addEventListener("DOMContentLoaded", () => {
            this.showLoginPanel();
        });

        // Gestion de la déconnexion
        if (this.uiManager.elements.logoutBtn) {
            this.uiManager.elements.logoutBtn.addEventListener("click", () => {
                this.logout();
            });
        }
    }

    // === MÉTHODES DE NAVIGATION ===

    showLoginPanel() {
        this.uiManager.showLoginPanel();
        if (!this.loginForm) {
            this.loginForm = new LoginForm("loginPanel", (authData) => {
                this.onLoginSuccess(authData);
            });
        }
    }

    async showClientPanel() {
        this.uiManager.showClientPanel();
        if (this.loginForm) {
            this.loginForm.hide();
        }
        
        // NE PAS charger les commandes ici - ça sera fait par initMainApp()
        console.log('🎯 [Components] showClientPanel - Panel affiché, chargement des commandes délégué à initMainApp()');
    }

    async onLoginSuccess(authData) {
        console.log("🔐 [Components] Connexion réussie:", authData.user);
        
        // IMPORTANT : Appeler handleLoginSuccess() de CustomerApp pour initialiser l'app complète
        if (window.customerApp && window.customerApp.handleLoginSuccess) {
            console.log('🎯 [Components] Appel de handleLoginSuccess() pour initialiser l\'app complète');
            await window.customerApp.handleLoginSuccess(authData);
        } else {
            console.error('❌ [Components] customerApp.handleLoginSuccess non disponible');
            // Fallback si customerApp n'est pas disponible
            this.uiManager.updateClientInfo(authData.user);
            await this.showClientPanel();
        }
    }

    logout() {
        // Nettoyage
        this.trackingManager.clearTracking();
        this.orderManager.clearOrders();
        this.uiManager.clearClientInfo();

        // Retour à l'écran de connexion
        this.showLoginPanel();

        // Notification
        this.uiManager.showInfo(
            "Vous avez été déconnecté avec succès",
            "Déconnexion",
        );
    }

    // === DÉLÉGATION AUX COMPOSANTS SPÉCIALISÉS ===

    // Gestion des commandes
    renderOrders(orders, currentPage, totalPages) {
        return this.orderManager.renderOrders(orders, currentPage, totalPages);
    }

    getFilters() {
        return this.orderManager.getFilters();
    }

    clearOrderFilters() {
        return this.orderManager.clearFilters();
    }

    highlightSelectedOrder(orderNumber) {
        return this.orderManager.highlightSelected(orderNumber);
    }

    // Gestion du tracking
    showOrderTracking(orderData) {
        this.trackingManager.showTracking();
        if (orderData) {
            this.trackingManager.updateInfo(orderData);
        }
    }

    hideOrderTracking() {
        return this.trackingManager.hideTracking();
    }

    updateTrackingInfo(order, trackingData) {
        return this.trackingManager.updateInfo(order, trackingData);
    }

    updateTrackingStats(data) {
        return this.trackingManager.updateTrackingDisplay(data);
    }

    updateTrackingProgress(percent) {
        // La progression est gérée directement via les éléments DOM
        if (this.trackingManager.elements.trackingProgress) {
            this.trackingManager.elements.trackingProgress.textContent = `${Math.round(percent)}%`;
        }
        if (this.trackingManager.elements.trackingProgressBar) {
            this.trackingManager.elements.trackingProgressBar.style.width = `${percent}%`;
        }
    }

    addTrackingHistoryItem(message, type) {
        return this.trackingManager.addToHistory(message, type);
    }

    clearTrackingHistory() {
        if (this.trackingManager.elements.trackingHistory) {
            this.trackingManager.elements.trackingHistory.innerHTML = '';
        }
    }

    // Gestion UI générale
    showError(message, title) {
        return this.uiManager.showError(message, title);
    }

    showSuccess(message, title) {
        return this.uiManager.showSuccess(message, title);
    }

    showInfo(message, title) {
        return this.uiManager.showInfo(message, title);
    }

    updateConnectionStatus(connected) {
        return this.uiManager.updateConnectionStatus(connected);
    }

    setLoadingState(element, isLoading) {
        return this.uiManager.setLoadingState(element, isLoading);
    }

    updateClientInfo(user) {
        return this.uiManager.updateClientInfo(user);
    }
}
