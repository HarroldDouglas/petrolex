// Contrôleur de suivi des commandes
class TrackingController {
    constructor(apiService, uiComponents, mapService) {
        this.apiService = apiService;
        this.ui = uiComponents;
        this.mapService = mapService;

        this.currentTrackingOrder = null;
        this.isTracking = false;
        this.updateInterval = null;

        this.initialTotalDistance = null;
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
                
                // Utiliser la vraie distance totale du trajet
                if (trackingData.total_distance && !this.initialTotalDistance) {
                    this.initialTotalDistance = parseFloat(trackingData.total_distance);
                    console.log(`📏 [TrackingController] Distance totale du trajet: ${this.initialTotalDistance}km`);
                } else if (trackingData.distance_remaining && !this.initialTotalDistance) {
                    // Fallback si total_distance n'est pas disponible
                    this.initialTotalDistance = parseFloat(trackingData.distance_remaining);
                    console.log(`📏 [TrackingController] Distance restante utilisée comme référence: ${this.initialTotalDistance}km`);
                }
                
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
                            // 🔧 CORRECTION: Créer les bounds correctement pour le service de carte
                            const bounds = new mapboxgl.LngLatBounds();
                            bounds.extend([driverLng, driverLat]);
                            bounds.extend([destLng, destLat]);
                            
                            this.mapService.map.fitBounds(bounds, {
                                padding: { top: 50, bottom: 50, left: 50, right: 50 },
                                duration: CUSTOMER_CONFIG.UI.ANIMATION_DURATION,
                            });
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
        // 🔍 DEBUG: Afficher TOUTES les propriétés reçues
        console.log("🔍 [TrackingController] TOUTES les données reçues du backend:", data);
        console.log("🔍 [TrackingController] Propriétés spécifiques:", {
            progress_percentage: data.progress_percentage,
            estimated_duration: data.estimated_duration,
            estimated_time_remaining: data.estimated_time_remaining,
            distance_remaining: data.distance_remaining,
            current_speed: data.current_speed,
            total_distance: data.total_distance
        });

        // Corriger les noms de propriétés pour correspondre à l'API
        this.ui.updateTrackingStats({
            eta: data.estimated_duration || data.estimated_time_remaining,
            distance: data.distance_remaining,
            speed: data.current_speed,
        });
        console.log("📊 [TrackingController] Stats envoyées à l'UI:", {
            eta: data.estimated_duration || data.estimated_time_remaining,
            distance: data.distance_remaining,
            speed: data.current_speed,
        });

        // 🔧 CORRECTION FINALE: Utiliser DIRECTEMENT la progression du backend
        if (data.progress_percentage !== undefined && data.progress_percentage !== null) {
            const progressValue = parseFloat(data.progress_percentage);
            if (!isNaN(progressValue)) {
                console.log(`📊 [TrackingController] Progression du backend: ${progressValue}%`);
                this.ui.updateTrackingProgress(progressValue);
            } else {
                console.warn(`⚠️ [TrackingController] Progression invalide du backend:`, data.progress_percentage);
                this.ui.updateTrackingProgress(0);
            }
        } else {
            console.warn(`⚠️ [TrackingController] Aucune progression renvoyée par le backend`);
            console.warn(`⚠️ [TrackingController] Tentative de calcul manuel...`);
            
            // Fallback: essayer de calculer la progression manuellement si possible
            if (data.total_distance && data.distance_remaining) {
                const totalDist = parseFloat(data.total_distance);
                const remainingDist = parseFloat(data.distance_remaining);
                if (!isNaN(totalDist) && !isNaN(remainingDist) && totalDist > 0) {
                    const traveled = totalDist - remainingDist;
                    const progressCalculated = Math.max(0, Math.min(100, (traveled / totalDist) * 100));
                    console.log(`📊 [TrackingController] Progression calculée manuellement: ${progressCalculated}% (${traveled}km / ${totalDist}km)`);
                    this.ui.updateTrackingProgress(progressCalculated);
                } else {
                    console.warn(`⚠️ [TrackingController] Impossible de calculer la progression: total=${totalDist}, remaining=${remainingDist}`);
                    this.ui.updateTrackingProgress(0);
                }
            } else {
                console.warn(`⚠️ [TrackingController] Données manquantes pour calculer la progression`);
                this.ui.updateTrackingProgress(0);
            }
        }

        // 🔧 CORRECTION: Mettre à jour aussi la position du livreur depuis les requêtes périodiques
        if (data.driver_lat && data.driver_lng) {
            const driverLat = parseFloat(data.driver_lat);
            const driverLng = parseFloat(data.driver_lng);
            
            if (!isNaN(driverLat) && !isNaN(driverLng)) {
                console.log(`📍 [TrackingController] Mise à jour position livreur: ${driverLat}, ${driverLng}`);
                
                this.mapService.updateDriverPosition(driverLat, driverLng, {
                    name: data.driver_name || "Livreur"
                });
                
                // Redessiner la route si on a la destination
                if (data.destination_lat && data.destination_lng) {
                    const destLat = parseFloat(data.destination_lat);
                    const destLng = parseFloat(data.destination_lng);
                    
                    if (!isNaN(destLat) && !isNaN(destLng)) {
                        this.mapService.drawRoute(
                            [driverLng, driverLat],
                            [destLng, destLat]
                        );
                    }
                }
            }
        }

        this.updateLastUpdateTime();
    }

    // Nouvelle méthode pour calculer la progression
    calculateProgress(data) {
        // D'abord, vérifier si la progression est directement fournie par le serveur
        if (data.progress_percentage !== undefined && data.progress_percentage !== null) {
            const serverProgress = parseFloat(data.progress_percentage);
            if (!isNaN(serverProgress)) {
                return serverProgress;
            }
        }
        
        // Si pas de progression directe, essayer de calculer basé sur la distance
        if (data.distance_remaining !== undefined && data.distance_remaining !== null) {
            const remainingKm = parseFloat(data.distance_remaining);
            
            // Récupérer la distance totale stockée au début du tracking
            if (this.initialTotalDistance && remainingKm >= 0) {
                const traveledDistance = this.initialTotalDistance - remainingKm;
                const progress = (traveledDistance / this.initialTotalDistance) * 100;
                return Math.max(0, Math.min(100, progress));
            }
        }
        
        return 0; // Par défaut, retourner 0% si aucun calcul possible
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
