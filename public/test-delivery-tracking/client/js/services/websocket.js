// Service WebSocket amélioré pour clients
class CustomerWebSocketService {
    constructor() {
        this.pusher = null;
        this.channels = new Map();
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 2000;
        this.heartbeatInterval = null;
        this.cache = new CustomerCacheService();
    }

    initialize() {
        if (!CUSTOMER_CONFIG.WEBSOCKET.ENABLED) {
            console.warn("WebSocket désactivé dans la configuration");
            return false;
        }

        const config = {
            key: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_KEY,
            options: {
                wsHost: CUSTOMER_CONFIG.WEBSOCKET.HOST,
                wsPort: CUSTOMER_CONFIG.WEBSOCKET.PORT,
                wssPort: CUSTOMER_CONFIG.WEBSOCKET.PORT,
                forceTLS: CUSTOMER_CONFIG.WEBSOCKET.FORCE_TLS,
                enabledTransports: CUSTOMER_CONFIG.WEBSOCKET.ENABLED_TRANSPORTS,
                cluster: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_CLUSTER,
                disableStats: true,
            },
        };

        if (!config.key) {
            console.warn("Configuration WebSocket manquante - clé Pusher introuvable");
            return false;
        }

        try {
            this.pusher = new Pusher(config.key, {
                ...config.options,
                enableLogging: true,
                forceTLS: true,
            });

            this.setupConnectionHandlers();
            this.setupHeartbeat();

            console.log("✅ WebSocket initialisé avec succès");
            return true;
        } catch (error) {
            console.error("❌ Erreur initialisation WebSocket:", error);
            return false;
        }
    }

    setupConnectionHandlers() {
        this.pusher.connection.bind("connected", () => {
            this.connected = true;
            this.reconnectAttempts = 0;
            this.emit("connected");
        });

        this.pusher.connection.bind("disconnected", () => {
            this.connected = false;
            this.emit("disconnected");
            this.attemptReconnect();
        });

        this.pusher.connection.bind("error", (error) => {
            this.emit("error", error);
        });
    }

    setupHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            if (this.connected && this.pusher) {
                this.pusher.connection.send_event("pusher:ping", {});
            }
        }, 30000);
    }

    attemptReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.emit("reconnect_failed");
            return;
        }

        this.reconnectAttempts++;

        setTimeout(() => {
            if (this.pusher) {
                this.pusher.connect();
            }
        }, this.reconnectDelay * this.reconnectAttempts);
    }

    subscribeToDeliveryTracking(orderNumber) {
        if (!this.pusher || !this.connected) {
            return null;
        }

        const channelName = `delivery-${orderNumber}`;

        if (this.channels.has(channelName)) {
            return this.channels.get(channelName);
        }

        const channel = this.pusher.subscribe(channelName);
        this.channels.set(channelName, channel);

        channel.bind("delivery-position-updated", (data) => {
            this.cache.cacheDriverPosition(orderNumber, data);
            this.emit("position_updated", data);
        });

        channel.bind("delivery-status-updated", (data) => {
            this.emit("status_updated", data);
        });

        return channel;
    }

    unsubscribeFromDeliveryTracking(orderNumber) {
        const channelName = `delivery-${orderNumber}`;
        const channel = this.channels.get(channelName);

        if (channel && this.pusher) {
            this.pusher.unsubscribe(channelName);
            this.channels.delete(channelName);
        }
    }

    disconnect() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }

        this.channels.forEach((channel, channelName) => {
            if (this.pusher) {
                this.pusher.unsubscribe(channelName);
            }
        });

        this.channels.clear();

        if (this.pusher) {
            this.pusher.disconnect();
            this.pusher = null;
        }

        this.connected = false;
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

            this.channel = this.pusher.subscribe("delivery-tracking");

            this.channel.bind("delivery-position-updated", (data) => {
                this.triggerCallback("positionUpdate", data);
            });

            this.channel.bind("delivery-status-updated", (data) => {
                this.triggerCallback("statusUpdate", data);
            });
        } catch (error) {
            this.triggerCallback("error", error);
        }
    }

    subscribeToDelivery(orderNumber) {
        if (!this.pusher) {
            return;
        }

        if (this.channel) {
            this.pusher.unsubscribe(this.channel.name);
        }

        this.channel = this.pusher.subscribe(`delivery.${orderNumber}`);

        this.channel.bind("DeliveryLocationUpdated", (data) => {
            this.triggerCallback("locationUpdated", data);
        });

        this.channel.bind("DeliveryStatusUpdated", (data) => {
            this.triggerCallback("statusUpdated", data);
        });
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
