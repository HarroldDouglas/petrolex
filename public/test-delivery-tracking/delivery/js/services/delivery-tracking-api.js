// Service spécialisé pour les API de tracking
class DeliveryTrackingApiService {
    constructor(baseApiService) {
        this.api = baseApiService;
    }

    // Démarrer le tracking d'une commande
    async startTracking(orderNumber, position) {
        const orderId = this.api.getOrderIdFromCache(orderNumber);

        if (!orderId) {
            throw new Error(
                "Impossible de récupérer l'ID de la commande. Rechargez les commandes.",
            );
        }

        return await this.createOrStartTracking(orderId, position);
    }

    async createOrStartTracking(orderId, position) {
        const trackingData = this.buildTrackingData(position);

        try {
            // Tenter de créer un nouveau tracking
            return await this.api.request("/tracking/delivery", {
                method: "POST",
                body: JSON.stringify({
                    order_id: orderId,
                    ...trackingData,
                }),
            });
        } catch (error) {
            // Si le tracking existe déjà, le démarrer
            console.log("Tracking exists, attempting to start:", error.message);
            return await this.api.request(
                `/tracking/delivery/${orderId}/start`,
                {
                    method: "POST",
                    body: JSON.stringify(trackingData),
                },
            );
        }
    }

    // Mettre à jour la position
    async updatePosition(orderNumber, position, speed = 0) {
        const orderId = this.api.getOrderIdFromCache(orderNumber);

        if (!orderId) {
            console.warn(
                "Order ID not found for position update:",
                orderNumber,
            );
            return null;
        }

        const updateData = {
            ...this.buildTrackingData(position),
            current_speed: speed,
        };

        try {
            return await this.api.request(
                `/tracking/delivery/${orderId}/position`,
                {
                    method: "PATCH",
                    body: JSON.stringify(updateData),
                },
            );
        } catch (error) {
            console.error("Position update failed:", error);
            return null;
        }
    }

    // Récupérer les détails du tracking
    async getTrackingDetails(orderNumber) {
        const orderId = this.api.getOrderIdFromCache(orderNumber);

        if (!orderId) {
            throw new Error("Order ID not found for tracking details");
        }

        return await this.api.request(`/tracking/delivery/${orderId}`);
    }

    // Marquer le tracking comme terminé
    async completeTracking(orderNumber) {
        const orderId = this.api.getOrderIdFromCache(orderNumber);

        if (!orderId) {
            throw new Error("Order ID not found for completion");
        }

        return await this.api.request(
            `/tracking/delivery/${orderId}/complete`,
            {
                method: "PATCH",
            },
        );
    }

    // Utilitaires
    buildTrackingData(position) {
        return {
            driver_lat: position.lat,
            driver_lng: position.lng,
            timestamp: new Date().toISOString(),
        };
    }

    // Vérifier le statut du tracking
    async getTrackingStatus(orderNumber) {
        try {
            const details = await this.getTrackingDetails(orderNumber);
            return {
                isActive: details.data?.status === "active",
                lastUpdate: details.data?.last_update,
                totalDistance: details.data?.total_distance,
            };
        } catch (error) {
            return {
                isActive: false,
                error: error.message,
            };
        }
    }

    // Batch update pour plusieurs positions
    async batchUpdatePositions(updates) {
        const promises = updates.map((update) =>
            this.updatePosition(
                update.orderNumber,
                update.position,
                update.speed,
            ),
        );

        const results = await Promise.allSettled(promises);

        return results.map((result, index) => ({
            orderNumber: updates[index].orderNumber,
            success: result.status === "fulfilled",
            error: result.status === "rejected" ? result.reason.message : null,
        }));
    }
}
