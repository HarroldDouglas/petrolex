// Contrôleur d'authentification
class AuthController {
    constructor(authService, uiComponents) {
        this.authService = authService;
        this.ui = uiComponents;
        this.onLoginSuccess = null;
    }

    bindLoginEvents(onSuccess) {
        this.onLoginSuccess = onSuccess;

        // CORRECTION: Ne pas essayer d'accéder au formulaire de connexion directement
        // Le formulaire est maintenant géré par le composant LoginForm
        console.log("📋 [AuthController] Événements de connexion liés");

        // Les événements sont déjà gérés par la classe LoginForm
        // Le callback onSuccess sera appelé directement par LoginForm
    }

    async handleLogin() {
        // Cette méthode n'est plus appelée directement, tout est géré par LoginForm
        console.log("📋 [AuthController] handleLogin appelé - redirection vers LoginForm");
    }

    validateLoginForm(email, password) {
        // Cette méthode n'est plus utilisée directement, la validation est gérée par LoginForm
        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    handleLoginError(error) {
        // Cette méthode n'est plus utilisée directement, les erreurs sont gérées par LoginForm
    }

    logout() {
        this.authService.logout();
        this.ui.showLoginPanel();
        this.ui.clearUserInfo();
    }
}
