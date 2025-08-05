// Gestion du tracking, des contrôles de livraison et des notifications
class DeliveryTrackingUI {
    constructor(baseUI) {
        this.ui = baseUI;
        // Référence pour accéder aux données de commande
        this.orderManager = null;
    }

    // Méthode pour injecter la référence de orderManager
    setOrderManager(orderManager) {
        this.orderManager = orderManager;
    }

    // Estimations de route
    updateRouteEstimates(time, distance, isRealTime = false) {
        if (time !== null && distance !== null) {
            const timeText = isRealTime ? `${time} min restant` : `${time} min`;
            const distanceText = isRealTime
                ? `${distance} km restant`
                : `${distance} km`;

            this.ui.elements.estimatedTime.textContent = timeText;
            this.ui.elements.estimatedDistance.textContent = distanceText;
        } else if (!isRealTime) {
            this.ui.elements.estimatedTime.textContent = "Calcul...";
            this.ui.elements.estimatedDistance.textContent = "Calcul...";
        }
    }

    // Contrôles de livraison
    updateProgress(percent) {
        const roundedPercent = Math.round(percent);
        this.ui.elements.progressPercent.textContent = `${roundedPercent}%`;
        this.ui.elements.progressBar.style.width = `${percent}%`;
    }

    setDeliveryControlsState(isTracking, isPaused) {
        this.ui.elements.startDeliveryBtn.disabled = isTracking && !isPaused;
        this.ui.elements.pauseDeliveryBtn.disabled = !isTracking || isPaused;
        this.ui.elements.stopDeliveryBtn.disabled = !isTracking;
        this.ui.elements.completeDeliveryBtn.style.display =
            isTracking && !isPaused ? "inline-block" : "none";

        // CORRECTION: Gérer le texte du bouton selon le contexte
        const selectedOrder = this.ui.orderManager?.getSelectedOrder();
        
        if (isPaused) {
            this.ui.elements.startDeliveryBtn.innerHTML =
                '<i class="fas fa-play"></i> Continuer';
        } else if (selectedOrder?.status === 'processing' && selectedOrder?.trackingData) {
            // Pour une commande déjà en cours, afficher "Continuer" même si pas en pause
            this.ui.elements.startDeliveryBtn.innerHTML =
                '<i class="fas fa-play"></i> Continuer';
        } else {
            this.ui.elements.startDeliveryBtn.innerHTML =
                '<i class="fas fa-play"></i> Démarrer Livraison';
        }
    }

    getTravelSpeed() {
        return parseInt(this.ui.elements.simulationSpeed.value);
    }

    updateTravelSpeedDisplay(speed) {
        this.ui.elements.currentTravelSpeedDisplay.textContent = speed;
    }

    // Position actuelle
    updateCurrentPosition(position, speed = null) {
        if (position) {
            this.ui.elements.currentPosition.textContent = `${position.lat.toFixed(4)}, ${position.lng.toFixed(4)}`;
            this.ui.elements.lastUpdate.textContent =
                new Date().toLocaleTimeString();
            this.ui.elements.currentLocationSection.style.display = "block";
            if (speed !== null) {
                this.ui.elements.currentSpeed.textContent = `${speed} km/h`;
            } else {
                this.ui.elements.currentSpeed.textContent = "Non disponible";
            }
        }
    }

    // Notifications
    showError(message, title = "Erreur") {
        console.error(title + ":", message);
        this.showNotification("error", title, message, 5000);
    }

    showSuccess(message, title = "Succès") {
        this.showNotification("success", title, message, 4000);
    }

    showInfo(message, title = "Information") {
        console.info(title + ":", message);
        this.showNotification("info", title, message, 3000);
    }

    showWarning(message, title = "Attention") {
        console.warn(title + ":", message);
        this.showNotification("warning", title, message, 4000);
    }

    // NOUVELLE MÉTHODE: Mettre à jour les boutons pour les commandes en cours
    updateDeliveryButtonsForInProgressOrder() {
        console.log('🔄 [DeliveryTrackingUI] Mise à jour des boutons pour commande en cours');
        
        // Récupérer les données de la commande et du tracking
        const selectedOrder = this.orderManager?.getSelectedOrder();
        if (!selectedOrder || !selectedOrder.trackingData) {
            console.warn('⚠️ Pas de données de commande ou tracking disponibles');
            return;
        }
        
        // CORRECTION: Utiliser directement la progression du serveur au lieu de recalculer
        const trackingData = selectedOrder.trackingData;
        if (trackingData.progress_percentage !== undefined && trackingData.progress_percentage !== null) {
            const serverProgress = parseFloat(trackingData.progress_percentage);
            if (!isNaN(serverProgress)) {
                this.updateProgress(serverProgress);
                console.log(`📊 [TrackingUI] Progression du serveur utilisée: ${serverProgress}%`);
            }
        }
        
        // Modifier le bouton principal
        const startBtn = this.ui.elements.startDeliveryBtn;
        startBtn.innerHTML = '<i class="fas fa-play"></i> Continuer';
        startBtn.className = 'btn btn-warning btn-control';
        startBtn.disabled = false;

        // Afficher le bouton Terminer
        const completeBtn = this.ui.elements.completeDeliveryBtn;
        completeBtn.style.display = 'inline-block';
        completeBtn.disabled = false;

        console.log('✅ [DeliveryTrackingUI] Boutons mis à jour: Continuer activé, Terminer visible');
        
        // Afficher un message informatif
        this.showInfo('Cette commande est en cours de livraison. Vous pouvez continuer, pauser ou arrêter.');
    }

    // NOUVELLE MÉTHODE: Calculer la progression basée sur la distance parcourue
    calculateDistanceBasedProgress(trackingData, orderDetails) {
        if (!trackingData || !orderDetails?.delivery_address) {
            return 0;
        }

        // Coordonnées de départ (centre de distribution ou position initiale)
        const startLat = orderDetails.distribution_center?.latitude || DELIVERY_CONFIG.DEFAULT_CENTER.lat;
        const startLng = orderDetails.distribution_center?.longitude || DELIVERY_CONFIG.DEFAULT_CENTER.lng;
        
        // Coordonnées actuelles du livreur
        const currentLat = parseFloat(trackingData.driver_lat);
        const currentLng = parseFloat(trackingData.driver_lng);
        
        // Coordonnées de destination
        const destLat = parseFloat(orderDetails.delivery_address.latitude);
        const destLng = parseFloat(orderDetails.delivery_address.longitude);
        
        // Vérifier que toutes les coordonnées sont valides
        if (isNaN(currentLat) || isNaN(currentLng) || isNaN(destLat) || isNaN(destLng)) {
            console.warn('⚠️ Coordonnées invalides pour le calcul de progression');
            return 0;
        }
        
        // Calculer les distances
        const totalDistance = this.calculateDistance(startLat, startLng, destLat, destLng);
        const remainingDistance = this.calculateDistance(currentLat, currentLng, destLat, destLng);
        
        if (totalDistance === 0) return 0;
        
        // CORRECTION: Utiliser la distance restante pour calculer la progression
        // Plus le livreur est proche de la destination, plus la progression est élevée
        const traveledDistance = Math.max(0, totalDistance - remainingDistance);
        let progress = (traveledDistance / totalDistance) * 100;
        
        // SÉCURITÉ: Limiter la progression à 95% tant que la livraison n'est pas terminée
        // Évite d'afficher 100% prématurément
        if (trackingData.status !== 'delivered') {
            progress = Math.min(95, progress);
        }
        
        // S'assurer que la progression est dans une plage valide
        progress = Math.max(0, Math.min(100, progress));
        
        console.log('📏 [TrackingUI] Calcul progression:', {
            totalDistance: totalDistance.toFixed(2),
            remainingDistance: remainingDistance.toFixed(2),
            traveledDistance: traveledDistance.toFixed(2),
            progress: progress.toFixed(1) + '%',
            status: trackingData.status
        });
        
        return Math.round(progress);
    }

    // NOUVELLE MÉTHODE: Calculer la distance entre deux points (formule haversine)
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Rayon de la Terre en km
        const dLat = this.toRadians(lat2 - lat1);
        const dLng = this.toRadians(lng2 - lng1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    toRadians(degrees) {
        return degrees * (Math.PI/180);
    }

    showNotification(type, title, message, duration = 4000) {
        const notification = document.createElement("div");
        notification.className = `notification ${type} fade-in`;
        notification.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <strong style="display: block; margin-bottom: 5px;">${title}</strong>
                    <div style="font-size: 0.9em;">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: none; border: none; color: inherit; font-size: 1.2em; cursor: pointer; margin-left: 10px;">&times;</button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification && notification.parentElement) {
                notification.style.opacity = "0";
                notification.style.transform = "translateX(100%)";
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, duration);
    }
}
