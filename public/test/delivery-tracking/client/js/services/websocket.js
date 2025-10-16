/**
 * Customer WebSocket Service - Handles real-time communication with Reverb server
 * Manages connection state, channel subscriptions, and event dispatching
 */
class CustomerWebSocketService {
    // Connection configuration constants
    static MAX_RECONNECT_ATTEMPTS = 5;
    static RECONNECT_BASE_DELAY_MS = 2000;
    static HEARTBEAT_INTERVAL_MS = 30000;

    // Channel names
    static CHANNELS = {
        MAIN_TRACKING: "delivery-tracking",
        ORDER_PREFIX: "delivery-"
    };

    // Events
    static EVENTS = {
        POSITION_UPDATED: "delivery-position-updated",
        STATUS_UPDATED: "delivery-status-updated"
    };

    constructor() {
        this.reverb = null;
        this.channels = new Map();
        this.connected = false;
        this.reconnectAttempts = 0;
        this.heartbeatInterval = null;
        this.cache = new CustomerCacheService();
    }

    /**
     * Initialize WebSocket connection and setup event handlers
     * @returns {boolean} True if initialization successful
     */
    initialize() {
        console.log("🚀 [WebSocket] Initializing CustomerWebSocketService with ReverbClient");
        
        if (!CUSTOMER_CONFIG.WEBSOCKET.ENABLED) {
            console.warn("❌ [WebSocket] WebSocket disabled in configuration");
            this.updateStatus(false);
            return false;
        }

        try {
            // Utiliser ReverbClient au lieu de Pusher
            this.reverb = new ReverbClient(CUSTOMER_CONFIG.WEBSOCKET.APP_KEY, {
                wsHost: CUSTOMER_CONFIG.WEBSOCKET.HOST,
                wsPort: CUSTOMER_CONFIG.WEBSOCKET.PORT
            });

            this.setupConnectionHandlers();
            console.log("✅ WebSocket initialisé avec succès");
            return true;
        } catch (error) {
            console.error("❌ Erreur initialisation WebSocket:", error);
            this.updateStatus(false);
            return false;
        }
    }

    setupConnectionHandlers() {
        // Écouter les événements de connexion ReverbClient
        this.reverb.bind('connected', () => {
            console.log("🔌 CustomerWebSocketService: Connexion établie");
            this.connected = true;
            this.reconnectAttempts = 0;
            this.updateStatus(true);
            this.emit("connected");
        });

        this.reverb.bind('state_change', (states) => {
            console.log(`🔄 CustomerWebSocketService: ${states.previous} → ${states.current}`);
            const isConnected = states.current === 'connected';
            this.connected = isConnected;
            this.updateStatus(isConnected);
            
            if (states.current === 'disconnected') {
                this.emit("disconnected");
                this.attemptReconnect();
            } else if (states.current === 'failed') {
                this.emit("error", { message: "Connexion échouée" });
            }
        });

        // No global channel subscription - security fix
    }

    // Global channel subscription removed for security

    /**
     * Bind standard events to a channel
     * @private
     * @param {Object} channel - ReverbClient channel instance
     */
    _bindChannelEvents(channel) {
        channel.bind(CustomerWebSocketService.EVENTS.POSITION_UPDATED, (data) => {
            console.log("📍 [WebSocket] Position update received:", data?.order_number);
            this._handlePositionUpdate(data);
        });

        channel.bind(CustomerWebSocketService.EVENTS.STATUS_UPDATED, (data) => {
            console.log("📊 [WebSocket] Status update received:", data?.order_number);
            this._handleStatusUpdate(data);
        });
    }

    /**
     * Handle position update data
     * @private
     * @param {Object} data - Position update data
     */
    _handlePositionUpdate(data) {
        if (data?.order_number) {
            this.cache.cacheDriverPosition(data.order_number, data);
        }
        this.emit("position_updated", data);
    }

    /**
     * Handle status update data
     * @private
     * @param {Object} data - Status update data
     */
    _handleStatusUpdate(data) {
        this.emit("status_updated", data);
    }

    updateStatus(connected) {
        // Mettre à jour le badge WebSocket dans l'interface
        const websocketStatus = document.getElementById('websocketStatus');
        if (websocketStatus) {
            if (connected) {
                websocketStatus.textContent = "Connecté";
                websocketStatus.className = "badge bg-success";
            } else {
                websocketStatus.textContent = "Déconnecté";
                websocketStatus.className = "badge bg-secondary";
            }
        }
        console.log(`🎯 Statut WebSocket mis à jour: ${connected ? 'Connecté' : 'Déconnecté'}`);
    }

    /**
     * Attempt to reconnect with exponential backoff
     * @private
     */
    attemptReconnect() {
        if (this.reconnectAttempts >= CustomerWebSocketService.MAX_RECONNECT_ATTEMPTS) {
            console.error("❌ [WebSocket] Max reconnection attempts reached");
            this.emit("reconnect_failed");
            return;
        }

        this.reconnectAttempts++;
        const delay = CustomerWebSocketService.RECONNECT_BASE_DELAY_MS * this.reconnectAttempts;
        
        console.log(`🔄 [WebSocket] Reconnection attempt ${this.reconnectAttempts}/${CustomerWebSocketService.MAX_RECONNECT_ATTEMPTS} in ${delay}ms`);

        setTimeout(() => {
            if (this.reverb) {
                this.reverb.connect();
            }
        }, delay);
    }

    /**
     * Subscribe to order-specific delivery tracking channel
     * @param {string} orderNumber - Order number to track
     * @returns {Object|null} Channel instance or null if failed
     */
    subscribeToDeliveryTracking(orderNumber) {
        if (!this._canSubscribe()) {
            console.warn("❌ [WebSocket] Cannot subscribe: not connected");
            return null;
        }

        const channelName = this._getOrderChannelName(orderNumber);

        // Return existing channel if already subscribed
        if (this.channels.has(channelName)) {
            console.log(`📡 [WebSocket] Already subscribed to: ${channelName}`);
            return this.channels.get(channelName);
        }

        return this._createOrderSubscription(channelName, orderNumber);
    }

    /**
     * Check if service can subscribe to channels
     * @private
     * @returns {boolean} True if can subscribe
     */
    _canSubscribe() {
        return this.reverb && this.connected;
    }

    /**
     * Generate order-specific channel name
     * @private
     * @param {string} orderNumber - Order number
     * @returns {string} Channel name
     */
    _getOrderChannelName(orderNumber) {
        return `${CustomerWebSocketService.CHANNELS.ORDER_PREFIX}${orderNumber}`;
    }

    /**
     * Create new order subscription
     * @private
     * @param {string} channelName - Channel name
     * @param {string} orderNumber - Order number
     * @returns {Object} Channel instance
     */
    _createOrderSubscription(channelName, orderNumber) {
        const channel = this.reverb.subscribe(channelName);
        this.channels.set(channelName, channel);

        this._bindChannelEvents(channel);

        console.log(`📡 [WebSocket] Subscribed to order channel: ${channelName}`);
        return channel;
    }

    /**
     * Unsubscribe from order-specific delivery tracking channel
     * @param {string} orderNumber - Order number to unsubscribe from
     */
    unsubscribeFromDeliveryTracking(orderNumber) {
        const channelName = this._getOrderChannelName(orderNumber);
        
        if (this.channels.has(channelName)) {
            this.channels.delete(channelName);
            console.log(`📡 [WebSocket] Unsubscribed from: ${channelName}`);
        } else {
            console.log(`📡 [WebSocket] Channel not found for unsubscribe: ${channelName}`);
        }
    }

    disconnect() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }

        this.channels.clear();

        if (this.reverb && this.reverb.ws) {
            this.reverb.ws.close();
            this.reverb = null;
        }

        this.connected = false;
        this.updateStatus(false);
        console.log("🔌 WebSocket déconnecté");
    }

    // Méthodes compatibles avec l'ancien système pour TrackingController
    subscribeToOrder(orderNumber) {
        console.log(`📡 [CustomerWebSocketService] Souscription à la commande: ${orderNumber}`);
        return this.subscribeToDeliveryTracking(orderNumber);
    }

    unsubscribeFromOrder(orderNumber) {
        console.log(`📡 [CustomerWebSocketService] Désinscription de la commande: ${orderNumber}`);
        return this.unsubscribeFromDeliveryTracking(orderNumber);
    }

    emit(event, data = null) {
        const customEvent = new CustomEvent(`websocket_${event}`, {
            detail: data,
        });
        document.dispatchEvent(customEvent);
    }

    on(event, callback) {
        document.addEventListener(`websocket_${event}`, (e) =>
            callback(e.detail),
        );
    }

    isConnected() {
        return this.connected;
    }

    getConnectionInfo() {
        return {
            connected: this.connected,
            reconnectAttempts: this.reconnectAttempts,
            channelsCount: this.channels.size,
        };
    }
}

// Service WebSocket simple (version de base)
class CustomerWebSocketBaseService {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.connected = false;
        this.callbacks = {};
    }

    connect() {
        try {
            this.pusher = new Pusher(CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_KEY, {
                wsHost: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_HOST,
                wsPort: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_PORT,
                wssPort: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_PORT,
                forceTLS: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_FORCE_TLS,
                enabledTransports: CUSTOMER_CONFIG.WEBSOCKET.ENABLED_TRANSPORTS,
                disableStats: true,
                cluster: CUSTOMER_CONFIG.WEBSOCKET.CLUSTER,
            });

            this.pusher.connection.bind("connected", () => {
                this.connected = true;
                this.triggerCallback("connected");
            });

            this.pusher.connection.bind("disconnected", () => {
                this.connected = false;
                this.triggerCallback("disconnected");
            });

            this.pusher.connection.bind("error", (error) => {
                this.triggerCallback("error", error);
            });

            this.setupEventHandlers();
        } catch (error) {
            this.triggerCallback("error", error);
        }
    }

    subscribeToDelivery(orderNumber) {
        if (!this.pusher || !orderNumber) {
            return;
        }

        if (this.channel) {
            this.pusher.unsubscribe(this.channel.name);
        }

        this.channel = this.pusher.subscribe(`delivery-${orderNumber}`);

        this.channel.bind("delivery-position-updated", (data) => {
            this.triggerCallback("positionUpdate", data);
        });

        this.channel.bind("delivery-status-updated", (data) => {
            this.triggerCallback("statusUpdate", data);
        });
    }

    setupEventHandlers() {
        // No global channel subscription - only specific channels
    }

    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    triggerCallback(event, data = null) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach((callback) => callback(data));
        }
    }

    disconnect() {
        if (this.channel) {
            this.pusher.unsubscribe(this.channel.name);
            this.channel = null;
        }
        if (this.pusher) {
            this.pusher.disconnect();
            this.pusher = null;
        }
        this.connected = false;
    }
}
