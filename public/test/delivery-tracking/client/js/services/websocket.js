// Service WebSocket pour clients avec ReverbClient
class CustomerWebSocketService {
    constructor() {
        this.reverb = null;
        this.channels = new Map();
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 2000;
        this.heartbeatInterval = null;
        this.cache = new CustomerCacheService();
    }

    initialize() {
        console.log("🚀 Initialisation CustomerWebSocketService avec ReverbClient...");
        
        if (!CUSTOMER_CONFIG.WEBSOCKET.ENABLED) {
            console.warn("WebSocket désactivé dans la configuration");
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

        // Souscrire au canal principal de tracking
        this.subscribeToMainTrackingChannel();
    }

    subscribeToMainTrackingChannel() {
        const channel = this.reverb.subscribe("delivery-tracking");
        this.channels.set("delivery-tracking", channel);

        channel.bind("delivery-position-updated", (data) => {
            console.log("📍 Position mise à jour reçue:", data);
            if (data.order_number) {
                this.cache.cacheDriverPosition(data.order_number, data);
            }
            this.emit("position_updated", data);
        });

        channel.bind("delivery-status-updated", (data) => {
            console.log("📊 Statut mise à jour reçu:", data);
            this.emit("status_updated", data);
        });
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

    attemptReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.emit("reconnect_failed");
            return;
        }

        this.reconnectAttempts++;

        setTimeout(() => {
            if (this.reverb) {
                this.reverb.connect();
            }
        }, this.reconnectDelay * this.reconnectAttempts);
    }

    subscribeToDeliveryTracking(orderNumber) {
        if (!this.reverb || !this.connected) {
            console.warn("❌ Impossible de souscrire: WebSocket non connecté");
            return null;
        }

        const channelName = `delivery-${orderNumber}`;

        if (this.channels.has(channelName)) {
            return this.channels.get(channelName);
        }

        const channel = this.reverb.subscribe(channelName);
        this.channels.set(channelName, channel);

        channel.bind("delivery-position-updated", (data) => {
            this.cache.cacheDriverPosition(orderNumber, data);
            this.emit("position_updated", data);
        });

        channel.bind("delivery-status-updated", (data) => {
            this.emit("status_updated", data);
        });

        console.log(`📡 Souscrit au canal: ${channelName}`);
        return channel;
    }

    unsubscribeFromDeliveryTracking(orderNumber) {
        const channelName = `delivery-${orderNumber}`;
        const channel = this.channels.get(channelName);

        if (channel) {
            this.channels.delete(channelName);
            console.log(`📡 Désinscrit du canal: ${channelName}`);
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
