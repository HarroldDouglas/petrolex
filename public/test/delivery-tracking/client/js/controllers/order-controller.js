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

    async loadCustomerOrders(user, filters = {}, page = 1) {
        if (!user) return;
        this.ui.setLoadingState("refreshOrdersBtn", true);

        try {
            console.log("🔍 Chargement des commandes pour l'utilisateur:", user);
            console.log("🔍 [OrderController] Filtres:", filters);
            console.log("🔍 [OrderController] Page:", page);
            const response = await this.apiService.getMyOrders(filters, page);

            // Retourner la réponse pour que CustomerOrderManager la traite
            return response;
        } catch (error) {
            console.error("❌ Erreur lors du chargement des commandes:", error);
            
            // Gestion d'erreur simplifiée
            if (window.customerApp && window.customerApp.notificationService) {
                window.customerApp.notificationService.error("Impossible de charger vos commandes");
            }
        } finally {
            // Toujours remettre le bouton à l'état normal
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
