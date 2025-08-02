// Service pour les appels API avec authentification
class DeliveryPersonApiService {
    constructor() {
        this.baseUrl = CONFIG.API.BASE_URL;
        this.token = localStorage.getItem("delivery_person_token");
        this.cachedOrders = new Map();
    }

    // Gestion des tokens
    setToken(token) {
        this.token = token;
        localStorage.setItem("delivery_person_token", token);
    }

    clearToken() {
        this.token = null;
        localStorage.removeItem("delivery_person_token");
    }

    getHeaders() {
        const headers = {
            "Content-Type": "application/json",
            Accept: "application/json",
        };

        if (this.token) {
            headers.Authorization = `Bearer ${this.token}`;
        }

        return headers;
    }

    // Méthode de requête centrale optimisée
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: this.getHeaders(),
            ...options,
        };

        try {
            const response = await fetch(url, config);

            if (response.status === 401) {
                this.clearToken();
                throw new Error("Session expirée. Veuillez vous reconnecter.");
            }

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(
                    errorData.message || `Erreur HTTP: ${response.status}`,
                );
            }

            return await response.json();
        } catch (error) {
            console.error(`API Request failed [${endpoint}]:`, error);
            throw error;
        }
    }

    // Authentification
    async login(email, password) {
        const response = await this.request(CONFIG.API.ENDPOINTS.LOGIN, {
            method: "POST",
            body: JSON.stringify({
                login: email,
                password: password,
            }),
        });

        if (response._metadata?.success && response.data?.access_token) {
            this.setToken(response.data.access_token);
            return response.data;
        }

        throw new Error(response._metadata?.message || "Erreur de connexion");
    }

    // Gestion des commandes
    async getOrders(deliveryPersonId, filters = {}, page = 1) {
        const endpoint = this.buildOrdersEndpoint(
            deliveryPersonId,
            filters,
            page,
        );
        const response = await this.request(endpoint);

        // Mettre en cache les commandes
        if (response.data?.orders) {
            this.cacheOrderIds(response.data.orders);
        }

        return response;
    }

    buildOrdersEndpoint(deliveryPersonId, filters, page) {
        let endpoint = CONFIG.API.ENDPOINTS.DELIVERY_PERSON_ORDERS.replace(
            "{id}",
            deliveryPersonId,
        );

        const params = new URLSearchParams({
            page: page.toString(),
            per_page: CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters,
        });

        return `${endpoint}?${params.toString()}`;
    }

    // Cache management optimisé
    cacheOrderIds(orders) {
        orders.forEach((order) => {
            this.cachedOrders.set(order.order_number, order);
        });

        // Persister en localStorage
        localStorage.setItem(
            "cached_orders",
            JSON.stringify(Array.from(this.cachedOrders.values())),
        );
    }

    getOrderFromCache(orderNumber) {
        if (this.cachedOrders.has(orderNumber)) {
            return this.cachedOrders.get(orderNumber);
        }

        // Fallback sur localStorage
        const cached = JSON.parse(
            localStorage.getItem("cached_orders") || "[]",
        );
        const order = cached.find((o) => o.order_number === orderNumber);

        if (order) {
            this.cachedOrders.set(orderNumber, order);
        }

        return order;
    }

    getOrderIdFromCache(orderNumber) {
        const order = this.getOrderFromCache(orderNumber);
        return order?.id || null;
    }

    clearCache() {
        this.cachedOrders.clear();
        localStorage.removeItem("cached_orders");
    }
}
