class CustomerAuthService {
    constructor() {
        this.apiService = new CustomerApiService();
        this.storageKey = "delivery-tracking-client-auth";
        this.tokenDuration = 2 * 60 * 60 * 1000; // 2 heures
    }

    async login(email, password) {
        try {
            const response = await fetch(
                `${CUSTOMER_CONFIG.API.BASE_URL}${CUSTOMER_CONFIG.API.ENDPOINTS.LOGIN_CUSTOMER}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        login: email,
                        password: password,
                    }),
                },
            );

            if (!response.ok) {
                throw new Error(`Erreur de connexion: ${response.status}`);
            }

            const responseData = await response.json();
            const data = responseData.data;

            const authData = {
                token: data.access_token,
                user: data.user,
                loginTime: Date.now(),
                expiresAt: Date.now() + this.tokenDuration,
            };

            localStorage.setItem(this.storageKey, JSON.stringify(authData));

            return authData;
        } catch (error) {
            console.error("Login error:", error);
            throw error;
        }
    }

    async logout() {
        try {
            const authData = this.getAuthData();
            if (authData && authData.token && !this.isTokenExpired()) {
                await fetch(
                    `${CUSTOMER_CONFIG.API.BASE_URL}${CUSTOMER_CONFIG.API.ENDPOINTS.LOGOUT}`,
                    {
                        method: "POST",
                        headers: {
                            Authorization: `Bearer ${authData.token}`,
                            Accept: "application/json",
                        },
                    },
                );
            }
        } catch (error) {
            console.error("Logout error:", error);
        } finally {
            localStorage.removeItem(this.storageKey);
        }
    }

    getAuthData() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            return stored ? JSON.parse(stored) : null;
        } catch (error) {
            console.error("Error parsing auth data:", error);
            return null;
        }
    }

    isTokenExpired() {
        const authData = this.getAuthData();
        if (!authData || !authData.expiresAt) {
            return true;
        }
        return Date.now() > authData.expiresAt;
    }

    isAuthenticated() {
        // Vérifier directement le token api_token pour éviter les problèmes d'expiration
        const apiToken = localStorage.getItem('api_token');
        if (apiToken) {
            return true;
        }
        
        // Fallback vers le système d'auth complexe
        const authData = this.getAuthData();
        return authData && authData.token && !this.isTokenExpired();
    }

    getToken() {
        // Priorité au token api_token
        const apiToken = localStorage.getItem('api_token');
        if (apiToken) {
            return apiToken;
        }
        
        // Fallback vers le système d'auth complexe
        if (this.isAuthenticated()) {
            const authData = this.getAuthData();
            return authData.token;
        }
        return null;
    }

    getUser() {
        // Si on utilise api_token, récupérer les données depuis current_user cache
        const apiToken = localStorage.getItem('api_token');
        if (apiToken) {
            const cachedUser = localStorage.getItem('current_user');
            if (cachedUser) {
                try {
                    return JSON.parse(cachedUser);
                } catch (error) {
                    console.error("Error parsing cached user:", error);
                }
            }
        }
        
        // Fallback vers le système d'auth complexe
        if (this.isAuthenticated()) {
            const authData = this.getAuthData();
            return authData.user;
        }
        return null;
    }

    clearExpiredToken() {
        if (this.isTokenExpired()) {
            localStorage.removeItem(this.storageKey);
        }
    }
}

// Service pour les appels API côté client
class CustomerApiService {
    constructor() {
        this.baseUrl = CUSTOMER_CONFIG.API.BASE_URL;
        this.authService = null;
    }

    setAuthService(authService) {
        this.authService = authService;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const defaultOptions = {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
        };

        if (this.authService && this.authService.isAuthenticated()) {
            const token = this.authService.getToken();
            defaultOptions.headers["Authorization"] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, {
                ...defaultOptions,
                ...options,
            });

            if (!response.ok) {
                if (response.status === 401) {
                    if (this.authService) {
                        await this.authService.logout();
                        window.location.reload();
                    }
                }
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("API Request failed:", error);
            throw error;
        }
    }

    async getCustomers(search = "", page = 1) {
        let endpoint = CUSTOMER_CONFIG.API.ENDPOINTS.CUSTOMERS;

        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CUSTOMER_CONFIG.UI.DEFAULT_PAGINATION.toString(),
        });

        if (search.trim()) {
            params.append("search", search.trim());
        }

        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    async getMyOrders(filters = {}, page = 1) {
        let endpoint = CUSTOMER_CONFIG.API.ENDPOINTS.MY_ORDERS;

        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CUSTOMER_CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters,
        });

        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    async getCustomerOrders(customerId, filters = {}, page = 1) {
        let endpoint = CUSTOMER_CONFIG.API.ENDPOINTS.CUSTOMER_ORDERS.replace(
            "{id}",
            customerId,
        );

        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CUSTOMER_CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters,
        });

        endpoint += `?${params.toString()}`;
        return this.request(endpoint);
    }

    async getTrackingDetails(orderId) {
        const endpoint = CUSTOMER_CONFIG.API.ENDPOINTS.TRACKING_DETAILS.replace(
            "{orderId}",
            orderId,
        );
        return this.request(endpoint);
    }
}
