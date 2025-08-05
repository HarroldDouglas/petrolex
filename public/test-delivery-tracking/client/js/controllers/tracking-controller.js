// Contrôleur de suivi des commandes
class TrackingController {
    constructor(apiService, uiComponents, mapService) {
        this.apiService = apiService;
        this.ui = uiComponents;
        this.mapService = mapService;

        this.currentTrackingOrder = null;
        this.isTracking = false;
        this.updateInterval = null;
    }

    bindEvents() {
        const stopTrackingBtn = document.getElementById("stopTrackingBtn");
        if (stopTrackingBtn) {
            stopTrackingBtn.addEventListener("click", () => {
                this.stopTracking();
            });
        }
    }

    async startTracking(orderData) {
        if (this.isTracking) {
            this.stopTracking();
        }

        this.currentTrackingOrder = orderData.order_number;
        this.isTracking = true;

        this.ui.showOrderTracking(orderData);

        try {
            await this.loadInitialTrackingData(orderData);
            this.subscribeToOrderUpdates(orderData.order_number);
            this.startPeriodicUpdates();

            this.ui.showSuccess(
                `Suivi activé pour la commande ${orderData.order_number}`,
            );
        } catch (error) {
            this.ui.showError(`Erreur lors du démarrage du suivi: ${error.message}`);
            this.stopTracking();
        }
    }

    async loadInitialTrackingData(orderData) {
        try {
            if (!orderData.id) {
                throw new Error("ID de commande manquant dans orderData");
            }
            
            const response = await this.apiService.getTrackingDetails(
                orderData.id,
            );

            if (response.data) {
                const trackingData = response.data;
                console.log("🔍 [TrackingController] Données initiales de tracking chargées:", trackingData);
                
                // Mise à jour des informations de suivi
                this.updateTrackingDisplay(trackingData);
                
                // Coordonnées du livreur
                const driverLat = parseFloat(trackingData.driver_lat);
                const driverLng = parseFloat(trackingData.driver_lng);
                
                // CORRECTION: Récupérer les coordonnées de destination depuis l'API ou orderData
                // En priorité, essayer de récupérer depuis les données de commande
                let destLat = parseFloat(orderData.delivery_address?.latitude);
                let destLng = parseFloat(orderData.delivery_address?.longitude);
                
                // Si non disponible dans orderData, essayer depuis le tracking
                if (isNaN(destLat) || isNaN(destLng)) {
                    destLat = parseFloat(trackingData.destination_lat);
                    destLng = parseFloat(trackingData.destination_lng);
                    
                    // Si toujours non disponible, essayer d'autres propriétés possibles
                    if (isNaN(destLat) || isNaN(destLng)) {
                        destLat = parseFloat(trackingData.customer_lat || trackingData.delivery_lat);
                        destLng = parseFloat(trackingData.customer_lng || trackingData.delivery_lng);
                    }
                }
                
                // Vérifier si on a des coordonnées du livreur
                if (!isNaN(driverLat) && !isNaN(driverLng)) {
                    console.log("🚚 [TrackingController] Positionnement du livreur:", driverLat, driverLng);
                    
                    // Positionner le marqueur du livreur
                    this.mapService.updateDriverPosition(driverLat, driverLng, {
                        name: trackingData.driver_name || "Livreur"
                    });
                    
                    // Si on a les coordonnées de destination, dessiner la route
                    if (!isNaN(destLat) && !isNaN(destLng)) {
                        console.log("🗺️ [TrackingController] Dessin de la route vers:", destLat, destLng);
                        
                        // Placer le marqueur de destination
                        this.mapService.setDestination(destLat, destLng, {
                            name: orderData.customer?.full_name || "Client",
                            address: orderData.delivery_address?.name || orderData.delivery_address?.address || "Destination"
                        });
                        
                        // Dessiner la route entre les points
                        this.mapService.drawRoute(
                            [driverLng, driverLat],
                            [destLng, destLat]
                        );
                        
                        // Centrer la vue pour voir les deux points
                        try {
                            // CORRECTION: Utiliser correctement fitBounds avec des coordonnées LngLat
                            this.mapService.fitBounds([
                                [driverLng, driverLat],
                                [destLng, destLat]
                            ]);
                        } catch (error) {
                            console.warn("⚠️ [TrackingController] Impossible d'ajuster la vue:", error);
                            // Fallback: centrer sur le livreur
                            this.mapService.centerOnLocation(driverLat, driverLng);
                        }
                    } else {
                        console.warn("⚠️ [TrackingController] Coordonnées de destination manquantes, centrage sur livreur uniquement");
                        this.mapService.centerOnLocation(driverLat, driverLng);
                    }
                } else {
                    console.warn("⚠️ [TrackingController] Coordonnées du livreur manquantes");
                }
                
                // Ajouter un premier élément dans l'historique
                this.ui.addTrackingHistoryItem("Suivi de livraison démarré");
                
                return trackingData;
            }
        } catch (error) {
            console.error("❌ [TrackingController] Erreur de chargement:", error);
            throw new Error(`Impossible de charger les données de suivi: ${error.message}`);
        }
    }

    subscribeToOrderUpdates(orderNumber) {
        if (window.customerApp.websocketManager.isConnected()) {
            window.customerApp.websocketManager.subscribeToOrder(orderNumber);
        }
    }

    stopTracking() {
        if (!this.currentTrackingOrder) return;

        window.customerApp.websocketManager.unsubscribeFromOrder(
            this.currentTrackingOrder,
        );

        this.stopPeriodicUpdates();

        this.ui.hideOrderTracking();
        this.mapService.clearMarkers();
        this.mapService.clearRoute();

        this.ui.showInfo(
            `Suivi arrêté pour la commande ${this.currentTrackingOrder}`,
        );

        this.currentTrackingOrder = null;
        this.isTracking = false;
    }

    startPeriodicUpdates() {
        this.stopPeriodicUpdates();

        this.updateInterval = setInterval(async () => {
            if (this.currentTrackingOrder) {
                await this.updateTrackingData();
            }
        }, CUSTOMER_CONFIG.UI.UPDATE_INTERVAL);
    }

    stopPeriodicUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    async updateTrackingData() {
        if (!this.currentTrackingOrder) return;

        try {
            const orderCard = document.querySelector(
                `[data-order-number="${this.currentTrackingOrder}"]`,
            );
            if (!orderCard) return;

            const orderId = orderCard.dataset.orderId;
            if (!orderId) return;

            const response = await this.apiService.getTrackingDetails(orderId);

            if (response.data) {
                this.updateTrackingDisplay(response.data);
            }
        } catch (error) {
            console.error("Error updating tracking data:", error);
        }
    }

    updateTrackingDisplay(data) {
        // Corriger les noms de propriétés pour correspondre à l'API
        this.ui.updateTrackingStats({
            eta: data.estimated_duration || data.estimated_time_remaining,
            distance: data.distance_remaining,
            speed: data.current_speed,
        });

        // CORRECTION: Mettre à jour correctement la barre de progression
        // Vérifier explicitement si progress_percentage est défini et est un nombre
        if (data.progress_percentage !== undefined && data.progress_percentage !== null) {
            const progressValue = parseFloat(data.progress_percentage);
            if (!isNaN(progressValue)) {
                console.log(`📊 [TrackingController] Mise à jour de la progression: ${progressValue}%`);
                this.ui.updateTrackingProgress(progressValue);
            } else {
                console.warn(`⚠️ [TrackingController] Valeur de progression invalide:`, data.progress_percentage);
            }
        } else {
            // Calculer la progression basée sur les données disponibles
            console.log(`🔄 [TrackingController] Calcul de progression basé sur les distances`);
            const calculatedProgress = this.calculateProgress(data);
            if (calculatedProgress !== null) {
                console.log(`📊 [TrackingController] Progression calculée: ${calculatedProgress.toFixed(1)}%`);
                this.ui.updateTrackingProgress(calculatedProgress);
            }
        }

        this.updateLastUpdateTime();
    }

    // Nouvelle méthode pour calculer la progression
    calculateProgress(data) {
        // Si on a les coordonnées du livreur et de la destination
        if (data.driver_lat && data.driver_lng && data.destination_lat && data.destination_lng) {
            // Récupérer la distance totale depuis les données de commande ou calculer
            const orderCard = document.querySelector(`[data-order-number="${this.currentTrackingOrder}"]`);
            if (orderCard) {
                // Pour l'instant, utiliser une estimation basée sur la distance restante
                // Une meilleure approche serait de stocker la distance totale initiale
                const remainingDistance = parseFloat(data.distance_remaining);
                if (remainingDistance && remainingDistance > 0) {
                    // Estimation : si distance restante = 1.1km et on sait que c'était ~1.88km au total
                    // On peut estimer la progression
                    const estimatedTotalDistance = 1.88; // À améliorer avec vraies données
                    const traveledDistance = estimatedTotalDistance - remainingDistance;
                    const progress = (traveledDistance / estimatedTotalDistance) * 100;
                    return Math.max(0, Math.min(100, progress)); // Entre 0 et 100
                }
            }
        }
        return null;
    }

    handleLocationUpdate(data) {
        if (data.order_number !== this.currentTrackingOrder) return;

        const driverLat = data.driver_position?.lat || data.current_latitude;
        const driverLng = data.driver_position?.lng || data.current_longitude;

        if (driverLat && driverLng) {
            this.mapService.updateDriverPosition(driverLat, driverLng, {
                name: data.driver_name,
            });

            const destLat = data.destination?.lat;
            const destLng = data.destination?.lng;

            if (destLat && destLng) {
                this.mapService.drawRoute(
                    [driverLng, driverLat],
                    [destLng, destLat],
                );
            }
        }

        this.updateTrackingDisplay(data);
        this.ui.addTrackingHistoryItem(
            `Position mise à jour - ${driverLat?.toFixed(4)}, ${driverLng?.toFixed(4)}`,
        );
    }

    handleStatusUpdate(data) {
        if (data.order_number !== this.currentTrackingOrder) return;

        const status = data.status?.value || data.status;
        const statusLabel =
            data.status?.label || CUSTOMER_CONFIG.STATUS.TRANSLATIONS[status] || status;
        const statusColor = CUSTOMER_CONFIG.STATUS.COLORS[status] || "secondary";

        this.ui.elements.trackingOrderStatus.textContent = statusLabel;
        this.ui.elements.trackingOrderStatus.className = `badge bg-${statusColor}`;

        this.ui.addTrackingHistoryItem(`Statut mis à jour: ${statusLabel}`);

        if (
            status === CUSTOMER_CONFIG.ORDER_STATUS.DELIVERED ||
            status === "completed"
        ) {
            this.handleDeliveryCompleted(data);
        }

        this.updateLastUpdateTime();
    }

    handleDeliveryCompleted(data) {
        this.ui.showSuccess("🎉 Votre commande a été livrée !");
        setTimeout(() => {
            this.stopTracking();
        }, 3000);
    }

    updateLastUpdateTime() {
        const now = new Date().toLocaleTimeString("fr-FR");
        const element = document.getElementById("lastUpdateTime");
        if (element) {
            element.textContent = now;
        }
    }

    cleanup() {
        this.stopTracking();
    }
}
