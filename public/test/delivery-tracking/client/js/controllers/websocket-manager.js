// Gestionnaire WebSocket
class WebSocketManager {
    constructor(uiComponents, trackingController) {
        this.ui = uiComponents;
        this.trackingController = trackingController;

        this.pusher = null;
        this.trackingChannel = null;
        this.orderChannel = null;
        this.wsConnected = false;
        this.pendingSubscriptions = [];
    }

    initialize() {
        Pusher.logToConsole = true; // Activation des logs de Pusher
        console.log("[WebSocket] Initialisation...");

        if (!CUSTOMER_CONFIG.WEBSOCKET.ENABLED) {
            console.warn("[WebSocket] WebSocket est désactivé dans la configuration.");
            return;
        }

        const pusherConfig = {
            wsHost: CUSTOMER_CONFIG.WEBSOCKET.HOST,
            wsPort: CUSTOMER_CONFIG.WEBSOCKET.PORT,
            wssPort: CUSTOMER_CONFIG.WEBSOCKET.PORT,
            forceTLS: CUSTOMER_CONFIG.WEBSOCKET.FORCE_TLS,
            enabledTransports: CUSTOMER_CONFIG.WEBSOCKET.ENABLED_TRANSPORTS,
            cluster: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_CLUSTER || "mt1",
            // Configuration pour Laravel Reverb local
            activityTimeout: 30000, 
            pongTimeout: 10000
        };

        console.log("[WebSocket] Configuration Pusher :", {
            appKey: CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_KEY,
            ...pusherConfig
        });

        try {
            this.pusher = new Pusher(CUSTOMER_CONFIG.WEBSOCKET.PUSHER_APP_KEY, pusherConfig);

            // === GESTION DES ÉTATS DE CONNEXION ===
            this.pusher.connection.bind('state_change', (states) => {
                console.log("[WebSocket] Changement d'état:", states);
                // states = { previous: 'connecting', current: 'connected' }
            });

            this.pusher.connection.bind("connecting", () => {
                console.log("[WebSocket] Connexion en cours...");
            });

            this.pusher.connection.bind("connected", () => {
                console.log("✅ [WebSocket] Connexion établie avec succès !");
                console.log("📊 [WebSocket] État de la connexion:", this.pusher.connection.state);
                this.wsConnected = true;
                this.ui.updateWebSocketStatus(true);
                this.ui.showSuccess("Connexion temps réel établie", 2000);
                this.processPendingSubscriptions();
            });

            this.pusher.connection.bind("disconnected", () => {
                console.warn("[WebSocket] Connexion interrompue.");
                this.wsConnected = false;
                this.ui.updateWebSocketStatus(false);
                this.ui.showWarning("Connexion temps réel interrompue");
            });

            this.pusher.connection.bind("error", (error) => {
                console.error("❌ [WebSocket] Erreur de connexion :", error);
                console.error("🔍 [WebSocket] Détails de l'erreur:", error.error);
                this.wsConnected = false;
                this.ui.updateWebSocketStatus(false);
                this.ui.showError(`Erreur WebSocket: ${error.error?.data?.message || 'Vérifiez la console'}`);
            });

            this.pusher.connection.bind("failed", () => {
                console.error("❌ [WebSocket] Connexion échouée définitivement");
                this.wsConnected = false;
                this.ui.updateWebSocketStatus(false);
                this.ui.showError("Connexion WebSocket échouée - Basculement en mode API");
            });

            // No global channel subscription - security fix

        } catch (error) {
            console.error("[WebSocket] Erreur critique lors de l'initialisation de Pusher:", error);
            this.ui.showError("Impossible d'initialiser la connexion temps réel");
        }
    }

    subscribeToOrder(orderNumber) {
        if (this.wsConnected) {
            const channelName = `delivery-${orderNumber}`;
            console.log(`[WebSocket] Subscribing to specific order channel: ${channelName}`);
            this.orderChannel = this.pusher.subscribe(channelName);
            
            this.orderChannel.bind("delivery-position-updated", (data) => {
                console.log("[WebSocket] Position update received:", data);
                this.trackingController.handleLocationUpdate(data);
            });

            this.orderChannel.bind("delivery-status-updated", (data) => {
                console.log("[WebSocket] Status update received:", data);
                this.trackingController.handleStatusUpdate(data);
                this.updateOrderCardStatus(data);
            });
        } else {
            console.warn(`[WebSocket] Connection not established. Queuing subscription for '${orderNumber}'.`);
            this.pendingSubscriptions.push(orderNumber);
        }
    }

    unsubscribeFromOrder(orderNumber) {
        if (this.orderChannel) {
            console.log(`[WebSocket] Désinscription du canal: ${this.orderChannel.name}`);
            this.pusher.unsubscribe(this.orderChannel.name);
            this.orderChannel = null;
        }
    }

    processPendingSubscriptions() {
        if (this.pendingSubscriptions.length > 0) {
            console.log("[WebSocket] Traitement des souscriptions en attente...");
        }
        while (this.pendingSubscriptions.length > 0) {
            const orderNumber = this.pendingSubscriptions.shift();
            this.subscribeToOrder(orderNumber);
        }
    }

    isConnected() {
        return this.wsConnected;
    }

    disconnect() {
        if (this.pusher) {
            console.log("[WebSocket] Déconnexion...");
            this.pusher.disconnect();
        }
        this.wsConnected = false;
        this.pendingSubscriptions = [];
    }

    // === NOUVELLE MÉTHODE : Mise à jour des cartes de commandes ===
    updateOrderCardStatus(data) {
        if (!data.order_number) return;
        
        console.log(`[WebSocket] Mise à jour du statut de la commande ${data.order_number}:`, data);
        
        // Trouver la carte de commande dans la liste
        const orderCard = document.querySelector(`[data-order-number="${data.order_number}"]`);
        if (!orderCard) {
            console.log(`[WebSocket] Carte de commande ${data.order_number} non trouvée dans la liste`);
            return;
        }
        
        const status = data.status?.value || data.status;
        const statusLabel = data.status?.label || CUSTOMER_CONFIG.STATUS.TRANSLATIONS[status] || status;
        const statusColor = CUSTOMER_CONFIG.STATUS.COLORS[status] || "secondary";
        
        // Mettre à jour le badge de statut
        const statusBadge = orderCard.querySelector('.badge');
        if (statusBadge) {
            statusBadge.textContent = statusLabel;
            statusBadge.className = `badge bg-${statusColor} mb-2`;
        }
        
        // Mettre à jour le bouton d'action selon le nouveau statut
        const actionContainer = orderCard.querySelector('.text-end');
        if (actionContainer) {
            const newButton = this.generateTrackingButton(status, data.order_number);
            const oldButton = actionContainer.querySelector('button');
            if (oldButton && newButton) {
                oldButton.outerHTML = newButton;
                console.log(`[WebSocket] ✅ Bouton mis à jour pour ${data.order_number}: ${status} -> ${newButton.includes('Suivre') ? 'SUIVRE' : 'AUTRE'}`);
            }
        }
        
        // Afficher une notification à l'utilisateur
        if (status === CUSTOMER_CONFIG.ORDER_STATUS.IN_PROGRESS) {  // 🔧 CORRECTION: IN_PROGRESS au lieu de PROCESSING
            this.ui.showSuccess(`📦 Votre commande ${data.order_number} est maintenant en cours de livraison ! Vous pouvez la suivre.`, 5000);
        }
    }

    // === NOUVELLE MÉTHODE : Générer le bon bouton selon le statut ===
    generateTrackingButton(status, orderNumber) {
        switch (status) {
            case CUSTOMER_CONFIG.ORDER_STATUS.IN_PROGRESS:  // 🔧 CORRECTION: IN_PROGRESS au lieu de PROCESSING
                return `<button class="btn btn-sm btn-success w-100" onclick="window.customerApp.startTracking('${orderNumber}')">
                    <i class="fas fa-map-marker-alt"></i> Suivre
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.CONFIRMED:
                return `<button class="btn btn-sm btn-warning w-100" disabled>
                    <i class="fas fa-clock"></i> En attente
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.DELIVERED:
                return `<button class="btn btn-sm btn-outline-success w-100" disabled>
                    <i class="fas fa-check"></i> Livrée
                </button>`;
            default:
                const statusLabel = CUSTOMER_CONFIG.STATUS.TRANSLATIONS[status] || status;
                return `<button class="btn btn-sm btn-outline-secondary w-100" disabled>
                    ${statusLabel}
                </button>`;
        }
    }
}