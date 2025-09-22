// Facade pour simplifier la gestion des contrôles de livraison
class DeliveryControlsFacade {
    constructor(ui, orderManager, deliveryManager) {
        this.ui = ui;
        this.orderManager = orderManager;
        this.deliveryManager = deliveryManager;
        this.controlsManager = new DeliveryControlsManager(ui.elements);
    }
    
    // 🎯 Interface simple qui cache toute la complexité
    async handleOrderSelection(orderNumber) {
        try {
            const order = await this.orderManager.selectOrder(orderNumber);
            
            if (!order) {
                console.warn('Aucune commande trouvée pour:', orderNumber);
                return;
            }
            
            console.log(`📦 Commande ${order.order_number} sélectionnée (statut: ${order.status})`);
            
            // Logique centralisée et claire
            this.showOrderDetails(order);
            this.configureControlsForOrder(order);
            await this.updateRouteInfo(order);
            
        } catch (error) {
            console.error('Erreur lors de la sélection de la commande:', error);
            this.ui.showError('Erreur lors de la sélection de la commande');
        }
    }
    
    showOrderDetails(order) {
        this.ui.updateSelectedOrderDetails(order);
        this.ui.highlightSelectedOrder(order.id);
        this.ui.showSelectedOrderDetails();
    }
    
    configureControlsForOrder(order) {
        const stateName = this.getStateNameForOrder(order);
        const context = this.getContextForOrder(order);
        
        // 🎯 Utilisation du Strategy Pattern
        this.controlsManager.updateControlsForState(stateName, context);
        
        console.log(`🎮 Contrôles configurés pour l'état: ${stateName}`);
    }
    
    getStateNameForOrder(order) {
        switch (order.status) {
            case DELIVERY_CONFIG.ORDER_STATUS.PAID:
                return 'paid';
            case DELIVERY_CONFIG.ORDER_STATUS.PROCESSING:
                return 'processing';
            case DELIVERY_CONFIG.ORDER_STATUS.IN_PROGRESS:
                return 'processing';
            default:
                console.warn(`Statut non géré: ${order.status}`);
                return 'paid'; // fallback
        }
    }
    
    getContextForOrder(order) {
        const context = {
            order: order,
            progress: 0
        };
        
        // Si la commande a des données de tracking, les inclure
        if (order.trackingData) {
            context.progress = order.trackingData.progress_percentage || 0;
            context.isPaused = order.trackingData.status === 'paused';
        }
        
        return context;
    }
    
    async updateRouteInfo(order) {
        // 🔧 CORRECTION: Ne pas recalculer si déjà fait par orderManager
        // La route est déjà calculée lors de selectOrder() dans order-manager.js
        console.log('📍 Route déjà calculée par orderManager - pas de recalcul nécessaire');
        
        // Seulement pour les commandes en cours qui ont besoin de mise à jour spéciale
        if (order.status === DELIVERY_CONFIG.ORDER_STATUS.IN_PROGRESS && order.trackingData) {
            console.log('🔄 Mise à jour spéciale pour commande en cours');
            if (this.deliveryManager) {
                await this.deliveryManager.calculateRouteForSelectedOrder();
            }
        }
    }
    
    // Méthodes pour gérer les transitions d'état
    handleTrackingStarted(progress = 0) {
        this.controlsManager.updateControlsForState('tracking_active', { 
            progress,
            isPaused: false 
        });
        console.log('🚀 Tracking démarré - contrôles mis à jour');
    }
    
    handleTrackingPaused(progress = 0) {
        this.controlsManager.updateControlsForState('tracking_paused', { 
            progress 
        });
        console.log('⏸️ Tracking en pause - contrôles mis à jour');
    }
    
    handleTrackingResumed(progress = 0) {
        this.controlsManager.updateControlsForState('tracking_active', { 
            progress,
            isPaused: false 
        });
        console.log('▶️ Tracking repris - contrôles mis à jour');
    }
    
    handleTrackingCompleted() {
        this.controlsManager.updateControlsForState('tracking_active', { 
            progress: 100 
        });
        console.log('✅ Tracking complété - seul le bouton Terminer est visible');
    }
}