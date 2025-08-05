// Contrôleur de gestion des commandes
class OrderController {
    constructor(apiService, uiComponents, errorHandler) {
        this.apiService = apiService;
        this.ui = uiComponents;
        this.errorHandler = errorHandler;
        this.currentPage = 1;
    }

    bindEvents() {
        document.querySelectorAll(".order-card").forEach((card) => {
            const trackBtn = card.querySelector(".track-order-btn");
            if (trackBtn) {
                trackBtn.addEventListener("click", () => {
                    const orderData = this.extractOrderDataFromCard(card);
                    window.customerApp.trackingController.startTracking(
                        orderData,
                    );
                });
            }
        });

        const searchInput = document.getElementById("orderSearch");
        if (searchInput) {
            searchInput.addEventListener(
                "input",
                this.debounce(() => {
                    this.loadCustomerOrders(window.customerApp.currentUser);
                }, 300),
            );
        }

        const statusFilter = document.getElementById("statusFilter");
        if (statusFilter) {
            statusFilter.addEventListener("change", () => {
                this.loadCustomerOrders(window.customerApp.currentUser);
            });
        }
    }

    async loadCustomerOrders(user, page = 1) {
        if (!user) return;

        const filters = this.ui.getFilters();
        this.ui.setLoadingState("refreshOrdersBtn", true);

        try {
            const customerId = user.customer_id || user.id;
            const response = await this.apiService.getCustomerOrders(
                customerId,
                filters,
                page,
            );

            if (response.data) {
                const orders = Array.isArray(response.data)
                    ? response.data
                    : response.data.data || [];
                const pagination = response._metadata?.pagination || {
                    current_page: 1,
                    last_page: 1,
                };

                this.ui.renderOrders(
                    orders,
                    pagination.current_page || 1,
                    pagination.last_page || 1,
                );

                if (orders.length === 0 && page === 1) {
                    this.ui.showInfo("Aucune commande trouvée");
                }

                this.bindEvents();
            }
        } catch (error) {
            const result = await this.errorHandler.handleApiError(
                error,
                "loadCustomerOrders",
            );

            if (result.shouldRetry) {
                setTimeout(() => this.loadCustomerOrders(user, page), 1000);
                return;
            }

            this.ui.showError("Impossible de charger vos commandes");
        } finally {
            this.ui.setLoadingState("refreshOrdersBtn", false);
        }
    }

    extractOrderDataFromCard(orderCard) {
        const orderNumber = orderCard.dataset.orderNumber;
        const orderId = orderCard.dataset.orderId;
        const orderNumberElement = orderCard.querySelector("h6");
        const details = orderCard.querySelectorAll("p");

        return {
            id: orderId,
            order_number: orderNumber,
            delivery_address: details[3]
                ? details[3].textContent.replace("Adresse: ", "")
                : "N/A",
            status: CUSTOMER_CONFIG.ORDER_STATUS.IN_PROGRESS, // 🔧 CORRECTION: IN_PROGRESS au lieu de PROCESSING
            customer: window.customerApp.currentUser,
        };
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}
