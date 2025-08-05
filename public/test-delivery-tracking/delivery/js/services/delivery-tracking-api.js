class DeliveryTrackingApiService {
    constructor(apiService) {
        this.apiService = apiService;
        this.baseUrl = DELIVERY_CONFIG.API.BASE_URL;
    }

    async startTracking(orderIdentifier, position) {
        let orderId;
        if (typeof orderIdentifier === 'number') {
            orderId = orderIdentifier;
        } else {
            orderId = await this.getOrderId(orderIdentifier);
        }
        
        // 🔧 CORRECTION: Démarrer directement sans vérification inutile
        // Le serveur gère déjà les cas de tracking existant vs nouveau
        const trackingData = {
            driver_lat: position.lat,
            driver_lng: position.lng,
            timestamp: new Date().toISOString()
        };
        
        try {
            // Appeler directement l'endpoint start - le serveur gère tout
            const response = await this.apiService.request(`/tracking/delivery/${orderId}/start`, {
                method: 'POST',
                body: JSON.stringify(trackingData)
            });
            
            return response;
        } catch (error) {
            console.error('Erreur lors du démarrage du tracking:', error);
            throw error;
        }
    }

    async updatePosition(orderId, position, speed, progressPercentage = null, distanceRemaining = null, estimatedDuration = null) {
        if (!orderId) {
            console.error('Missing order ID for updatePosition');
            return;
        }

        try {
            const updateData = {
                driver_lat: position.lat,
                driver_lng: position.lng,
                timestamp: new Date().toISOString(),
                current_speed: speed,
            };
            
            // 🔧 AJOUTER les données calculées côté client si disponibles
            if (progressPercentage !== null) {
                updateData.progress_percentage = progressPercentage;
            }
            if (distanceRemaining !== null) {
                updateData.distance_remaining = distanceRemaining;
            }
            if (estimatedDuration !== null) {
                updateData.estimated_duration = estimatedDuration;
            }
            
            const response = await this.apiService.request(`/tracking/delivery/${orderId}/position`, {
                method: 'PATCH',
                body: JSON.stringify(updateData)
            });
            
            if (response && response.data) {
                if (response.data.progress_percentage !== undefined) {
                    const serverProgress = parseFloat(response.data.progress_percentage);
                    if (Math.abs(serverProgress - (progressPercentage || 0)) > 1) {
                        console.log(`📊 Progress difference: local=${progressPercentage}%, server=${serverProgress}%`);
                    }
                }
            }
            
            return response;
        } catch (error) {
            console.error('Error updating position:', error);
            throw error;
        }
    }

    async completeTracking(orderId) {
        try {
            return await this.apiService.request(`/tracking/delivery/${orderId}/complete`, {
                method: 'PATCH',
            });
        } catch (error) {
            console.error('Error completing tracking:', error);
            throw error;
        }
    }

    async getOrderId(orderNumber) {
        if (typeof orderNumber === 'number') return orderNumber;
        
        try {
            if (this.apiService.getOrderIdFromCache) {
                return this.apiService.getOrderIdFromCache(orderNumber);
            }
            
            throw new Error(`Conversion method number -> ID not found in apiService`);
        } catch (error) {
            console.error('Error retrieving ID:', error);
            throw error;
        }
    }
}
