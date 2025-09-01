class SessionManager {
    constructor(apiService, ui) {
        this.apiService = apiService;
        this.ui = ui;
        this.currentDeliveryPerson = null;
    }

    async checkExistingSession() {
        const token = localStorage.getItem('delivery_person_token');
        const deliveryPersonData = JSON.parse(localStorage.getItem('delivery_person_data') || 'null');
        const sessionExpiry = localStorage.getItem('delivery_person_session_expiry');
        
        if (sessionExpiry && Date.now() > parseInt(sessionExpiry)) {
            this.clearSession();
            this.ui.showLoginPanel();
            this.ui.showInfo('Session expirée. Veuillez vous reconnecter.');
            return null;
        }
        
        if (token && deliveryPersonData) {
            try {
                const testResponse = await this.apiService.request('/auth/check');
                if (testResponse._metadata?.success !== false) {
                    this.currentDeliveryPerson = deliveryPersonData;
                    this.ui.updateDeliveryPersonInfo(deliveryPersonData);
                    this.ui.showDeliveryPersonPanel();
                    this.ui.updateConnectionStatus(true);
                    this.extendSession();
                    this.ui.showSuccess(`Reconnexion automatique réussie! Bonjour ${deliveryPersonData.first_name} 👋`);
                    return deliveryPersonData;
                }
            } catch (error) {
                console.warn('Session expired or invalid:', error);
            }
        }
        
        this.clearSession();
        this.ui.showLoginPanel();
        return null;
    }

    async login(email, password) {
        if (!email || !password) {
            throw new Error('Veuillez remplir tous les champs');
        }
        
        const response = await this.apiService.login(email, password);
        
        if (response.user && response.user.roles && response.user.roles.includes('delivery_person')) {
            this.currentDeliveryPerson = response.user;
            this.createSession(response.user, response.access_token);
            this.ui.updateDeliveryPersonInfo(response.user);
            this.ui.showDeliveryPersonPanel();
            this.ui.showSuccess(`Connexion réussie! Session valide pendant 2 heures 🕐`);
            return response.user;
        } else {
            throw new Error('Ce compte n\'est pas associé à un livreur');
        }
    }

    logout() {
        this.clearSession();
        this.currentDeliveryPerson = null;
        this.ui.showLoginPanel();
        this.ui.hideSelectedOrderDetails();
        this.ui.hideDeliveryControls();
        this.ui.updateConnectionStatus(false);
        this.ui.showSuccess('Déconnexion réussie. Session effacée.');
    }

    createSession(deliveryPersonData, token) {
        const expiryTime = Date.now() + (2 * 60 * 60 * 1000);
        localStorage.setItem('delivery_person_token', token);
        localStorage.setItem('delivery_person_data', JSON.stringify(deliveryPersonData));
        localStorage.setItem('delivery_person_session_expiry', expiryTime.toString());
        localStorage.setItem('delivery_person_login_time', new Date().toISOString());
    }
    
    extendSession() {
        const newExpiryTime = Date.now() + (2 * 60 * 60 * 1000);
        localStorage.setItem('delivery_person_session_expiry', newExpiryTime.toString());
    }
    
    clearSession() {
        localStorage.removeItem('delivery_person_token');
        localStorage.removeItem('delivery_person_data');
        localStorage.removeItem('delivery_person_session_expiry');
        localStorage.removeItem('delivery_person_login_time');
        this.apiService.clearToken();
    }

    getCurrentDeliveryPerson() {
        return this.currentDeliveryPerson;
    }
}