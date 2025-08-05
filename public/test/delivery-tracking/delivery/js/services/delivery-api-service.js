class DeliveryPersonApiService {
    constructor() {
        this.baseUrl = DELIVERY_CONFIG.API.BASE_URL;
        this.token = localStorage.getItem("delivery_person_token");
        this.cachedOrders = new Map();
        this.trackingApi = new DeliveryTrackingApiService(this);
    }

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

    async login(email, password) {
        const response = await this.request(DELIVERY_CONFIG.API.ENDPOINTS.LOGIN, {
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

    async getOrders(deliveryPersonId, filters = {}, page = 1) {
        const endpoint = this.buildOrdersEndpoint(
            deliveryPersonId,
            filters,
            page,
        );
        const response = await this.request(endpoint);

        if (response.data?.orders) {
            this.cacheOrderIds(response.data.orders);
        }

        return response;
    }

    buildOrdersEndpoint(deliveryPersonId, filters, page) {
        let endpoint = DELIVERY_CONFIG.API.ENDPOINTS.DELIVERY_PERSON_ORDERS.replace(
            "{id}",
            deliveryPersonId,
        );

        const params = new URLSearchParams({
            page: page.toString(),
            per_page: DELIVERY_CONFIG.UI.DEFAULT_PAGINATION.toString(),
            ...filters,
        });

        return `${endpoint}?${params.toString()}`;
    }

    cacheOrderIds(orders) {
        orders.forEach((order) => {
            this.cachedOrders.set(order.order_number, order);
        });

        localStorage.setItem(
            "cached_orders",
            JSON.stringify(Array.from(this.cachedOrders.values())),
        );
    }

    getOrderFromCache(orderNumber) {
        if (this.cachedOrders.has(orderNumber)) {
            return this.cachedOrders.get(orderNumber);
        }

        const cached = JSON.parse(
            localStorage.getItem("cached_orders") || "[]",
        );
        const order = cached.find((o) => o.order_number === orderNumber);

        if (order) {
            this.cachedOrders.set(orderNumber, order);
        }

        return order;
    }

    async getOrder(orderNumberOrId) {
        try {
            const cachedOrder = this.getOrderFromCache(orderNumberOrId);
            
            if (cachedOrder) {
                return {
                    _metadata: {
                        success: true,
                        message: "Commande récupérée du cache"
                    },
                    data: cachedOrder
                };
            }
            
            const deliveryPerson = JSON.parse(localStorage.getItem("delivery_person"));
            if (!deliveryPerson) {
                throw new Error("Livreur non connecté");
            }
            
            const deliveryPersonId = deliveryPerson.delivery_person_id || deliveryPerson.id;
            await this.getOrders(deliveryPersonId, {}, 1);
            
            const refreshedOrder = this.getOrderFromCache(orderNumberOrId);
            
            if (refreshedOrder) {
                return {
                    _metadata: {
                        success: true,
                        message: "Commande récupérée après actualisation"
                    },
                    data: refreshedOrder
                };
            }
            
            throw new Error(`Commande ${orderNumberOrId} non trouvée`);
        } catch (error) {
            console.error(`Failed to get order ${orderNumberOrId}:`, error);
            throw error;
        }
    }
    
    async getOrderTracking(orderNumber) {
        try {
            const orderData = this.getOrderFromCache(orderNumber);
            if (!orderData || !orderData.id) {
                throw new Error(`Impossible de trouver l'ID numérique pour la commande ${orderNumber}`);
            }
            
            const numericOrderId = orderData.id;
            const endpoint = DELIVERY_CONFIG.API.ENDPOINTS.TRACKING_DETAILS.replace("{orderId}", numericOrderId);
            return await this.request(endpoint);
        } catch (error) {
            console.error(`Failed to get tracking for order ${orderNumber}:`, error);
            throw error;
        }
    }
    
    getOrderNumberFromId(orderIdOrNumber) {
        if (typeof orderIdOrNumber === 'string' && orderIdOrNumber.startsWith('CMD-')) {
            return orderIdOrNumber;
        }
        
        for (const [orderNumber, orderData] of this.cachedOrders.entries()) {
            if (orderData.id == orderIdOrNumber) {
                return orderNumber;
            }
        }
        
        return null;
    }

    getOrderIdFromCache(orderNumber) {
        if (typeof orderNumber === 'number' || (typeof orderNumber === 'string' && !orderNumber.startsWith('CMD-'))) {
            return parseInt(orderNumber, 10);
        }
        
        const orderData = this.getOrderFromCache(orderNumber);
        if (orderData && orderData.id) {
            return orderData.id;
        }
        
        console.warn(`Impossible de trouver l'ID pour la commande ${orderNumber}`);
        return null;
    }
}
