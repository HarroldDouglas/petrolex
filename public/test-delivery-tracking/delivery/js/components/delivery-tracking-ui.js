// Gestion du tracking, des contrôles de livraison et des notifications
class DeliveryTrackingUI {
    constructor(baseUI) {
        this.ui = baseUI;
    }

    // Estimations de route
    updateRouteEstimates(time, distance, isRealTime = false) {
        console.log(
            "updateRouteEstimates: time=",
            time,
            "distance=",
            distance,
            "isRealTime=",
            isRealTime,
        );
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

        if (isPaused) {
            this.ui.elements.startDeliveryBtn.innerHTML =
                '<i class="fas fa-play"></i> Reprendre';
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
        console.log(
            "updateCurrentPosition: position=",
            position,
            "speed=",
            speed,
        );
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
        console.log(title + ":", message);
        this.showNotification("success", title, message, 4000);
    }

    showInfo(message, title = "Information") {
        console.info(title + ":", message);
        this.showNotification("info", title, message, 3000);
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
