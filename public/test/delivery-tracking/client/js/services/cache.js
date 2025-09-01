class CustomerCacheService {
    constructor() {
        this.cache = new Map();
        this.cacheExpiry = new Map();
        this.defaultTTL = 30000; // 30 secondes par défaut
    }

    set(key, data, ttl = this.defaultTTL) {
        this.cache.set(key, data);
        this.cacheExpiry.set(key, Date.now() + ttl);

        setTimeout(() => this.delete(key), ttl);
    }

    get(key) {
        const expiry = this.cacheExpiry.get(key);

        if (!expiry || Date.now() > expiry) {
            this.delete(key);
            return null;
        }

        return this.cache.get(key);
    }

    delete(key) {
        this.cache.delete(key);
        this.cacheExpiry.delete(key);
    }

    clear() {
        this.cache.clear();
        this.cacheExpiry.clear();
    }

    cacheDriverPosition(orderNumber, position) {
        this.set(`driver_position_${orderNumber}`, position, 10000);
    }

    getDriverPosition(orderNumber) {
        return this.get(`driver_position_${orderNumber}`);
    }

    cacheTrackingDetails(orderId, details) {
        this.set(`tracking_${orderId}`, details, 15000);
    }

    getTrackingDetails(orderId) {
        return this.get(`tracking_${orderId}`);
    }
}
