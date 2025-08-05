// Classe principale qui combine tous les composants UI
class DeliveryPersonUIComponents extends DeliveryUIBase {
    constructor() {
        super();
        this.ordersUI = new DeliveryOrdersUI(this);
        this.trackingUI = new DeliveryTrackingUI(this);
    }

    // Délégation vers les sous-composants pour maintenir la compatibilité
    renderOrders(orders, currentPage, totalPages) {
        return this.ordersUI.renderOrders(orders, currentPage, totalPages);
    }

    updateSelectedOrderDetails(order) {
        return this.ordersUI.updateSelectedOrderDetails(order);
    }

    highlightSelectedOrder(orderNumber) {
        return this.ordersUI.highlightSelectedOrder(orderNumber);
    }

    updateRouteEstimates(time, distance, isRealTime) {
        return this.trackingUI.updateRouteEstimates(time, distance, isRealTime);
    }

    updateProgress(percent) {
        return this.trackingUI.updateProgress(percent);
    }

    setDeliveryControlsState(isTracking, isPaused) {
        return this.trackingUI.setDeliveryControlsState(isTracking, isPaused);
    }

    getTravelSpeed() {
        return this.trackingUI.getTravelSpeed();
    }

    updateTravelSpeedDisplay(speed) {
        return this.trackingUI.updateTravelSpeedDisplay(speed);
    }

    updateCurrentPosition(position, speed) {
        return this.trackingUI.updateCurrentPosition(position, speed);
    }

    showError(message, title) {
        return this.trackingUI.showError(message, title);
    }

    showSuccess(message, title) {
        return this.trackingUI.showSuccess(message, title);
    }

    showInfo(message, title) {
        return this.trackingUI.showInfo(message, title);
    }

    // AJOUT: Méthode showWarning manquante
    showWarning(message, title) {
        return this.trackingUI.showWarning(message, title);
    }
}
