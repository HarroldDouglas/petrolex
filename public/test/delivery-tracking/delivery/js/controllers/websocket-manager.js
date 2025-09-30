// WebSocket manager for delivery interface
class DeliveryWebSocketManager {
    constructor() {
        this.reverb = null;
        this.trackingChannel = null;
        this.isConnected = false;
        this.deliveryManager = null;
    }

    setDeliveryManager(deliveryManager) {
        this.deliveryManager = deliveryManager;
    }

    async connect() {
        try {
            console.log('[WebSocket] Connecting to Reverb...');
            
            this.reverb = new Reverb({
                broadcaster: 'reverb',
                key: DELIVERY_CONFIG.WEBSOCKET.KEY,
                wsHost: DELIVERY_CONFIG.WEBSOCKET.HOST,
                wsPort: DELIVERY_CONFIG.WEBSOCKET.PORT,
                wssPort: DELIVERY_CONFIG.WEBSOCKET.WSS_PORT,
                forceTLS: DELIVERY_CONFIG.WEBSOCKET.FORCE_TLS,
                enabledTransports: ['ws', 'wss']
            });

            await new Promise((resolve, reject) => {
                const timeout = setTimeout(() => {
                    reject(new Error('WebSocket connection timeout'));
                }, 5000);

                this.reverb.connection.bind('connected', () => {
                    clearTimeout(timeout);
                    this.isConnected = true;
                    console.log('[WebSocket] Connected successfully');
                    resolve();
                });

                this.reverb.connection.bind('error', (error) => {
                    clearTimeout(timeout);
                    console.error('[WebSocket] Connection error:', error);
                    reject(error);
                });
            });

            // No global subscription - will subscribe to specific delivery when needed

        } catch (error) {
            console.error('[WebSocket] Connection failed:', error);
            this.isConnected = false;
        }
    }

    subscribeToSpecificDelivery(orderNumber) {
        if (!orderNumber) {
            console.error("[WebSocket] Cannot subscribe: orderNumber required");
            return;
        }

        try {
            const channelName = `delivery-${orderNumber}`;
            console.log(`[WebSocket] Subscribing to specific channel: ${channelName}`);
            this.trackingChannel = this.reverb.subscribe(channelName);

            this.trackingChannel.bind('pusher:subscription_succeeded', () => {
                console.log(`[WebSocket] Successfully subscribed to ${channelName}`);
            });

            this.trackingChannel.bind('pusher:subscription_error', (status) => {
                console.error(`[WebSocket] Subscription error for ${channelName}:`, status);
            });

            this.trackingChannel.bind("delivery-position-updated", (data) => {
                console.log("[WebSocket] Position update received:", data);
                if (this.deliveryManager) {
                    this.deliveryManager.handleRealTimeUpdate(data);
                }
            });

            this.trackingChannel.bind("delivery-status-updated", (data) => {
                console.log("[WebSocket] Status update received:", data);
                if (this.deliveryManager) {
                    this.deliveryManager.handleRealTimeUpdate(data);
                }
            });

        } catch (error) {
            console.error('[WebSocket] Subscription failed:', error);
        }
    }

    disconnect() {
        if (this.trackingChannel) {
            this.trackingChannel.unbind_all();
            this.trackingChannel = null;
        }
        
        if (this.reverb) {
            this.reverb.disconnect();
        }
        
        this.isConnected = false;
        console.log('[WebSocket] Disconnected');
    }

    getConnectionStatus() {
        return this.isConnected;
    }
}

window.DeliveryWebSocketManager = DeliveryWebSocketManager;