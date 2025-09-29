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

            this.subscribeToDeliveryTracking();

        } catch (error) {
            console.error('[WebSocket] Connection failed:', error);
            this.isConnected = false;
        }
    }

    subscribeToDeliveryTracking() {
        try {
            console.log("[WebSocket] Subscribing to 'delivery-tracking' channel...");
            this.trackingChannel = this.reverb.subscribe("delivery-tracking");

            this.trackingChannel.bind('pusher:subscription_succeeded', () => {
                console.log("[WebSocket] Successfully subscribed to 'delivery-tracking' channel");
            });

            this.trackingChannel.bind('pusher:subscription_error', (status) => {
                console.error("[WebSocket] Subscription error for 'delivery-tracking':", status);
            });

            // Listen for position updates (same as client)
            this.trackingChannel.bind("delivery-position-updated", (data) => {
                console.log("[WebSocket] Event received: 'delivery-position-updated'", data);
                if (this.deliveryManager) {
                    this.deliveryManager.handleRealTimeUpdate(data);
                }
            });

            this.trackingChannel.bind("delivery-status-updated", (data) => {
                console.log("[WebSocket] Event received: 'delivery-status-updated'", data);
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
            this.reverb.unsubscribe("delivery-tracking");
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