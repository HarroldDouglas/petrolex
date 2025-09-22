// Gestionnaire spécialisé pour les commandes
// Affichage, filtrage, pagination des commandes

class CustomerOrderManager {
    constructor(orderController, uiManager) {
        this.orderController = orderController;
        this.uiManager = uiManager;
        this.initOrderElements();
    }

    initOrderElements() {
        this.elements = {
            refreshOrdersBtn: document.getElementById("refreshOrdersBtn"),
            statusFilter: document.getElementById("statusFilter"),
            orderNumberFilter: document.getElementById("orderNumberFilter"),
            ordersList: document.getElementById("ordersList"),
            ordersPagination: document.getElementById("ordersPagination"),
        };
    }

    // === RENDU DES COMMANDES ===

    renderOrders(orders, currentPage = 1, totalPages = 1) {
        const list = this.elements.ordersList;
        list.innerHTML = "";

        if (!orders || orders.length === 0) {
            list.innerHTML =
                '<div class="text-center text-muted py-3">Aucune commande trouvée</div>';
            return;
        }

        orders.forEach((order) => {
            const orderCard = this.createOrderCard(order);
            list.appendChild(orderCard);
        });

        this.updatePagination(currentPage, totalPages);
    }

    createOrderCard(order) {
        const div = document.createElement("div");
        div.className = "order-card mb-2 p-3 border rounded";
        div.dataset.orderNumber = order.order_number;
        div.dataset.orderId = order.id;

        const statusColor = CUSTOMER_CONFIG.STATUS.COLORS[order.status] || "secondary";
        const statusLabel =
            CUSTOMER_CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
        const deliveryAddress = this.extractDeliveryAddress(order);

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-primary">${order.order_number}</h6>
                    <p class="mb-1 text-sm"><strong>Date:</strong> ${this.uiManager.formatDate(order.order_date)}</p>
                    <p class="mb-1 text-sm"><strong>Montant:</strong> ${order.total_amount || 0}€</p>
                    <p class="mb-0 text-sm"><strong>Adresse:</strong> ${deliveryAddress}</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-${statusColor} mb-2">${statusLabel}</span>
                    ${this.getTrackingActionButton(order)}
                </div>
            </div>
        `;

        return div;
    }

    extractDeliveryAddress(order) {
        return (
            order.delivery_address?.name ||
            order.delivery_address?.address ||
            order.delivery_address ||
            "Adresse non définie"
        );
    }

    getTrackingActionButton(order) {
        switch (order.status) {
            case CUSTOMER_CONFIG.ORDER_STATUS.PROCESSING:  // En cours de livraison
                return `<button class="btn btn-sm btn-success w-100" onclick="window.customerApp.startTracking('${order.order_number}')">
                    <i class="fas fa-map-marker-alt"></i> Suivre
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.PAID:  // Payée, en attente de traitement
                return `<button class="btn btn-sm btn-info w-100" disabled>
                    <i class="fas fa-credit-card"></i> Payée
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.PENDING:  // En attente de paiement
                return `<button class="btn btn-sm btn-warning w-100" disabled>
                    <i class="fas fa-clock"></i> En attente
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.DELIVERED:
                return `<button class="btn btn-sm btn-outline-success w-100" disabled>
                    <i class="fas fa-check"></i> Livrée
                </button>`;
            case CUSTOMER_CONFIG.ORDER_STATUS.CANCELLED:
                return `<button class="btn btn-sm btn-outline-danger w-100" disabled>
                    <i class="fas fa-times"></i> Annulée
                </button>`;
            default:
                const statusLabel =
                    CUSTOMER_CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
                return `<button class="btn btn-sm btn-outline-secondary w-100" disabled>
                    ${statusLabel}
                </button>`;
        }
    }

    // === PAGINATION ===

    updatePagination(currentPage, totalPages) {
        const pagination = this.elements.ordersPagination;
        pagination.innerHTML = "";

        if (totalPages <= 1) return;

        const nav = document.createElement("nav");
        nav.innerHTML = `
            <ul class="pagination pagination-sm">
                <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadOrders(${currentPage - 1})">Précédent</a>
                </li>
                ${this.generatePageNumbers(currentPage, totalPages)}
                <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadOrders(${currentPage + 1})">Suivant</a>
                </li>
            </ul>
        `;
        pagination.appendChild(nav);
    }

    generatePageNumbers(currentPage, totalPages) {
        const pages = [];
        const maxPagesToShow = 5;
        let startPage = Math.max(
            1,
            currentPage - Math.floor(maxPagesToShow / 2),
        );
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(`
                <li class="page-item ${i === currentPage ? "active" : ""}">
                    <a class="page-link" href="#" onclick="window.customerApp.loadMyOrders(${i})">${i}</a>
                </li>
            `);
        }

        return pages.join("");
    }

    // === FILTRAGE ===

    getFilters() {
        const filters = {};

        if (this.elements.statusFilter?.value) {
            filters.status = this.elements.statusFilter.value;
        }

        if (this.elements.orderNumberFilter?.value.trim()) {
            filters.order_number = this.elements.orderNumberFilter.value.trim();
        }

        return filters;
    }

    clearFilters() {
        if (this.elements.statusFilter) {
            this.elements.statusFilter.value = "";
        }
        if (this.elements.orderNumberFilter) {
            this.elements.orderNumberFilter.value = "";
        }
    }

    // === SÉLECTION ===

    highlightSelected(orderNumber) {
        // Supprimer la surbrillance de tous les éléments
        document.querySelectorAll(".order-card").forEach((card) => {
            card.classList.remove("selected");
        });

        // Ajouter la surbrillance à l'élément sélectionné
        const selectedCard = document.querySelector(
            `[data-order-number="${orderNumber}"]`,
        );
        if (selectedCard) {
            selectedCard.classList.add("selected");
            selectedCard.scrollIntoView({
                behavior: "smooth",
                block: "nearest",
            });
        }
    }

    clearOrders() {
        if (this.elements.ordersList) {
            this.elements.ordersList.innerHTML = "";
        }
        if (this.elements.ordersPagination) {
            this.elements.ordersPagination.innerHTML = "";
        }
        this.clearFilters();
    }

    // === CHARGEMENT DES COMMANDES ===

    async loadOrders(customerId, page = 1) {
        try {
            console.log(`📦 Chargement des commandes pour client ${customerId}, page ${page}`);
            
            // Afficher un loader
            this.elements.ordersList.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
            
            // Obtenir les filtres
            const filters = this.getFilters();
            
            // Appeler l'API via le controller
            const response = await this.orderController.loadCustomerOrders(customerId, filters, page);
            
            if (response && response.data) {
                // Rendre les commandes
                this.renderOrders(response.data.data || response.data, response.data.current_page || page, response.data.last_page || 1);
                
                // Mettre à jour la pagination
                this.updatePagination(response.data.current_page || page, response.data.last_page || 1);
                
                console.log(`✅ ${response.data.data?.length || response.data.length || 0} commandes chargées`);
            } else {
                this.elements.ordersList.innerHTML = '<div class="text-center text-muted py-3">Aucune commande trouvée</div>';
            }
            
        } catch (error) {
            console.error('❌ Erreur lors du chargement des commandes:', error);
            this.elements.ordersList.innerHTML = '<div class="text-center text-danger py-3"><i class="fas fa-exclamation-triangle"></i> Erreur de chargement</div>';
            
            // Afficher une notification d'erreur
            if (window.customerApp && window.customerApp.notificationService) {
                window.customerApp.notificationService.error('Erreur lors du chargement des commandes');
            }
        }
    }

    // Méthode de rechargement rapide
    async refreshOrders() {
        if (window.customerApp && window.customerApp.currentUser) {
            await this.loadOrders(window.customerApp.currentUser.id);
        }
    }
}
