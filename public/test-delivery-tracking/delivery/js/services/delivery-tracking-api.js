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
        
        try {
            const existingTracking = await this.apiService.request(`/tracking/delivery/${orderId}`);
            if (existingTracking && existingTracking.data) {
                if (existingTracking.data.progress_percentage) {
                    const resumeData = {
                        driver_lat: position.lat,
                        driver_lng: position.lng,
                        timestamp: new Date().toISOString(),
                        progress_percentage: existingTracking.data.progress_percentage
                    };
                    
                    return await this.apiService.request(`/tracking/delivery/${orderId}/start`, {
                        method: 'POST',
                        body: JSON.stringify(resumeData)
                    });
                }
            }
        } catch (error) {
            // If tracking doesn't exist or other error, continue normally
        }
        
        return this.createOrStartTracking(orderId, position);
    }
    
    async createOrStartTracking(orderId, position) {
        const trackingData = {
            driver_lat: position.lat,
            driver_lng: position.lng,
            timestamp: new Date().toISOString()
        };
        
        try {
            const createResponse = await this.apiService.request('/tracking/delivery', {
                method: 'POST',
                body: JSON.stringify({
                    order_id: orderId,
                    ...trackingData
                })
            });
            
            if (createResponse._metadata?.success) {
                const startResponse = await this.apiService.request(`/tracking/delivery/${orderId}/start`, {
                    method: 'POST',
                    body: JSON.stringify(trackingData)
                });
                
                return startResponse;
            }
            
            return createResponse;
        } catch (error) {
            if (error.response?.status === 409) {
                try {
                    const trackingDetails = await this.apiService.request(`/tracking/delivery/${orderId}`);
                    
                    if (trackingDetails && trackingDetails.data) {
                        const startResponse = await this.apiService.request(`/tracking/delivery/${orderId}/start`, {
                            method: 'POST',
                            body: JSON.stringify({
                                ...trackingData,
                                progress_percentage: trackingDetails.data.progress_percentage
                            })
                        });
                        
                        return startResponse;
                    }
                    
                    const startResponse = await this.apiService.request(`/tracking/delivery/${orderId}/start`, {
                        method: 'POST',
                        body: JSON.stringify(trackingData)
                    });
                    
                    return startResponse;
                } catch (startError) {
                    console.error('Error during resume:', startError);
                    throw startError;
                }
            }
            
            console.error('Error during creation/start:', error);
            throw error;
        }
    }

    async updatePosition(orderId, position, speed, additionalData = null) {
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
                ...(additionalData || {}),
            };
            
            const response = await this.apiService.request(`/tracking/delivery/${orderId}/position`, {
                method: 'PATCH',
                body: JSON.stringify(updateData)
            });
            
            if (response && response.data) {
                if (response.data.progress_percentage !== undefined) {
                    const serverProgress = parseFloat(response.data.progress_percentage);
                    if (Math.abs(serverProgress - (additionalData?.progress_percentage || 0)) > 1) {
                        console.log(`Progress difference: local=${additionalData?.progress_percentage}%, server=${serverProgress}%`);
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
