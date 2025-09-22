// Composant de formulaire de connexion client
// Interface de connexion avec validation et animations

class LoginForm {
    constructor(containerId, onLoginSuccess) {
        this.container = document.getElementById(containerId);
        this.onLoginSuccess = onLoginSuccess;
        
        // CORRECTION: S'assurer que AuthService est accessible
        if (window.customerApp && window.customerApp.authService) {
            this.authService = window.customerApp.authService;
        } else {
            // Fallback: créer une nouvelle instance
            this.authService = new CustomerAuthService();
            console.warn("🔐 LoginForm: Using fallback AuthService instance");
        }

        if (this.container) {
            this.render();
            this.bindEvents();
        } else {
            console.error("LoginForm: Container not found:", containerId);
        }
    }

    // === RENDU DE L'INTERFACE ===

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
                        <div class="card-footer text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Connexion sécurisée
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            ${this.getStyles()}
        `;
    }

    getStyles() {
        return `
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
                    animation: slideIn 0.6s ease-out;
                }
                
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .login-container .card-header {
                    background: transparent;
                    border-bottom: 1px solid #eee;
                    padding: 25px 25px 20px;
                }
                
                .login-container .card-body {
                    padding: 25px;
                }
                
                .login-container .card-footer {
                    background: transparent;
                    border-top: 1px solid #eee;
                    padding: 15px 25px;
                }
                
                .login-container .form-control {
                    border-radius: 10px;
                    padding: 12px 15px;
                    border: 1px solid #ddd;
                    transition: all 0.3s ease;
                }
                
                .login-container .form-control:focus {
                    border-color: #667eea;
                    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                    transform: translateY(-2px);
                }
                
                .login-container .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    border-radius: 10px;
                    padding: 12px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                
                .login-container .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
                
                .login-container .btn-primary:disabled {
                    opacity: 0.7;
                    transform: none;
                }
                
                .login-container .alert {
                    border-radius: 10px;
                    border: none;
                }
                
                @media (max-width: 480px) {
                    .login-container {
                        padding: 10px;
                    }
                    
                    .login-container .card-body,
                    .login-container .card-header,
                    .login-container .card-footer {
                        padding: 20px;
                    }
                }
            </style>
        `;
    }

    // === GESTION DES ÉVÉNEMENTS ===

    bindEvents() {
        const form = this.container.querySelector("#loginForm");
        const emailInput = this.container.querySelector("#email");
        const passwordInput = this.container.querySelector("#password");

        if (!form || !emailInput || !passwordInput) {
            console.error("LoginForm: Required elements not found");
            return;
        }

        // Soumission du formulaire
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.handleLogin();
        });

        // Gestion de l'Enter
        [emailInput, passwordInput].forEach((input) => {
            input.addEventListener("keypress", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    this.handleLogin();
                }
            });
        });

        // Validation en temps réel
        emailInput.addEventListener("input", () => this.validateEmail());
        passwordInput.addEventListener("input", () => this.validatePassword());

        // Focus automatique
        setTimeout(() => {
            emailInput.focus();
        }, 500);
    }

    // === GESTION DE LA CONNEXION ===

    async handleLogin() {
        const emailInput = this.container.querySelector("#email");
        const passwordInput = this.container.querySelector("#password");

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Validation côté client
        if (!this.validateForm(email, password)) {
            return;
        }

        this.showLoading(true);
        this.hideError();

        try {
            console.log("🔐 [LoginForm] Tentative de connexion avec AuthService");
            // CORRECTION: Toujours utiliser le vrai service d'authentification
            const authData = await this.authService.login(email, password);

            console.log("🔐 [LoginForm] Connexion réussie:", authData.user);

            // Animation de succès
            this.showSuccess();

            // Callback de succès après un délai pour l'animation
            setTimeout(() => {
                if (this.onLoginSuccess) {
                    this.onLoginSuccess(authData);
                }
            }, 1000);
        } catch (error) {
            console.error("🔐 [LoginForm] Échec de la connexion:", error);
            this.showError(this.getErrorMessage(error));
        } finally {
            this.showLoading(false);
        }
    }

    // === VALIDATION ===

    validateForm(email, password) {
        if (!email) {
            this.showError("Veuillez saisir votre email");
            this.container.querySelector("#email").focus();
            return false;
        }

        if (!this.isValidEmail(email)) {
            this.showError("Format d'email invalide");
            this.container.querySelector("#email").focus();
            return false;
        }

        if (!password) {
            this.showError("Veuillez saisir votre mot de passe");
            this.container.querySelector("#password").focus();
            return false;
        }

        if (password.length < 3) {
            this.showError(
                "Le mot de passe doit contenir au moins 3 caractères",
            );
            this.container.querySelector("#password").focus();
            return false;
        }

        return true;
    }

    validateEmail() {
        const emailInput = this.container.querySelector("#email");
        const email = emailInput.value.trim();

        if (email && !this.isValidEmail(email)) {
            emailInput.classList.add("is-invalid");
        } else {
            emailInput.classList.remove("is-invalid");
        }
    }

    validatePassword() {
        const passwordInput = this.container.querySelector("#password");
        const password = passwordInput.value;

        if (password && password.length < 3) {
            passwordInput.classList.add("is-invalid");
        } else {
            passwordInput.classList.remove("is-invalid");
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // === GESTION DES MESSAGES ===

    showError(message) {
        const errorDiv = this.container.querySelector("#loginError");
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = "block";
            errorDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
    }

    hideError() {
        const errorDiv = this.container.querySelector("#loginError");
        if (errorDiv) {
            errorDiv.style.display = "none";
        }
    }

    showSuccess() {
        const loginBtn = this.container.querySelector("#loginBtn");
        if (loginBtn) {
            loginBtn.innerHTML = '<i class="fas fa-check me-2"></i>Connecté !';
            loginBtn.className = "btn btn-success btn-lg";
        }
    }

    showLoading(show) {
        const loadingDiv = this.container.querySelector("#loginLoading");
        const loginBtn = this.container.querySelector("#loginBtn");
        const form = this.container.querySelector("#loginForm");

        if (show) {
            if (loadingDiv) loadingDiv.style.display = "block";
            if (loginBtn) loginBtn.disabled = true;
            if (form) form.style.opacity = "0.7";
        } else {
            if (loadingDiv) loadingDiv.style.display = "none";
            if (loginBtn) loginBtn.disabled = false;
            if (form) form.style.opacity = "1";
        }
    }

    // === UTILITAIRES ===

    async simulateLogin(email, password) {
        // Simulation d'un appel API
        await new Promise((resolve) => setTimeout(resolve, 1500));

        // Simulation de données utilisateur
        return {
            success: true,
            user: {
                id: 1,
                email: email,
                first_name: "Client",
                last_name: "Test",
                full_name: "Client Test",
            },
            token: "fake_jwt_token_" + Date.now(),
        };
    }

    getErrorMessage(error) {
        if (error.message) {
            return error.message;
        }

        if (error.status === 401 || error.status === 403) {
            return "Email ou mot de passe incorrect";
        }

        if (error.status >= 500) {
            return "Erreur serveur. Veuillez réessayer plus tard.";
        }

        return "Erreur de connexion. Vérifiez votre connexion internet.";
    }

    // === MÉTHODES PUBLIQUES ===

    show() {
        this.container.style.display = "block";

        // Focus automatique après un délai
        setTimeout(() => {
            const emailInput = this.container.querySelector("#email");
            if (emailInput) {
                emailInput.focus();
            }
        }, 100);
    }

    hide() {
        this.container.style.display = "none";
        this.hideError();
        this.showLoading(false);

        // Reset du formulaire
        const form = this.container.querySelector("#loginForm");
        if (form) {
            form.reset();
        }

        // Reset du bouton
        const loginBtn = this.container.querySelector("#loginBtn");
        if (loginBtn) {
            loginBtn.innerHTML =
                '<i class="fas fa-sign-in-alt me-2"></i>Se connecter';
            loginBtn.className = "btn btn-primary btn-lg";
        }
    }

    destroy() {
        if (this.container) {
            this.container.innerHTML = "";
        }
    }
}
