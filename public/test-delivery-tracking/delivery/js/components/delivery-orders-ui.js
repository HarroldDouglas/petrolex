// Gestion spécialisée des commandes et de leur affichage
class DeliveryOrdersUI {
    constructor(baseUI) {
        this.ui = baseUI;
    }

    // Gestion des commandes
    renderOrders(orders, currentPage = 1, totalPages = 1) {
        const list = this.ui.elements.ordersList;
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

        const statusColor = CONFIG.STATUS.COLORS[order.status] || "secondary";
        const statusLabel =
            CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;

        const customerName =
            order.customer?.full_name ||
            order.customer?.first_name + " " + order.customer?.last_name ||
            "N/A";
        const customerPhone = order.customer?.phone_number || "N/A";
        const deliveryAddress =
            order.delivery_address?.name ||
            order.delivery_address?.address ||
            "N/A";

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-primary">${order.order_number}</h6>
                    <p class="mb-1 text-sm"><strong>Client:</strong> ${customerName}</p>
                    <p class="mb-1 text-sm"><strong>Téléphone:</strong> ${customerPhone}</p>
                    <p class="mb-1 text-sm"><strong>Adresse:</strong> ${deliveryAddress}</p>
                    <p class="mb-0 text-sm"><strong>Montant:</strong> ${order.total_amount || 0}€</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-${statusColor} mb-2">${statusLabel}</span>
                    ${this.getOrderActions(order)}
                </div>
            </div>
        `;

        return div;
    }

    getOrderActions(order) {
        switch (order.status) {
            case CONFIG.ORDER_STATUS.CONFIRMED:
                return `<button class="btn btn-sm btn-success w-100" onclick="window.deliveryPersonApp.selectOrder('${order.order_number}')">
                    <i class="fas fa-play"></i> Sélectionner
                </button>`;
            case CONFIG.ORDER_STATUS.PROCESSING:
                return `<button class="btn btn-sm btn-warning w-100" onclick="window.deliveryPersonApp.selectOrder('${order.order_number}')">
                    <i class="fas fa-eye"></i> Voir détails
                </button>`;
            default:
                return `<button class="btn btn-sm btn-outline-secondary w-100" disabled>
                    <i class="fas fa-check"></i> Terminée
                </button>`;
        }
    }

    updatePagination(currentPage, totalPages) {
        const pagination = this.ui.elements.pagination;
        pagination.innerHTML = "";

        if (totalPages <= 1) return;

        const nav = document.createElement("nav");
        nav.innerHTML = `
            <ul class="pagination pagination-sm">
                <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${currentPage - 1})">Précédent</a>
                </li>
                ${this.generatePageNumbers(currentPage, totalPages)}
                <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${currentPage + 1})">Suivant</a>
                </li>
            </ul>
        `;
        pagination.appendChild(nav);
    }

    generatePageNumbers(currentPage, totalPages) {
        let pages = "";
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);

        for (let i = start; i <= end; i++) {
            pages += `
                <li class="page-item ${i === currentPage ? "active" : ""}">
                    <a class="page-link" href="#" onclick="window.deliveryPersonApp.loadOrders(${i})">${i}</a>
                </li>
            `;
        }
        return pages;
    }

    // Détails de la commande sélectionnée
    updateSelectedOrderDetails(order) {
        this.ui.elements.selectedOrderNumber.textContent = order.order_number;

        const customerName =
            order.customer?.full_name ||
            order.customer?.first_name + " " + order.customer?.last_name ||
            "N/A";
        const customerPhone = order.customer?.phone_number || "N/A";
        const deliveryAddress =
            order.delivery_address?.name ||
            order.delivery_address?.address ||
            "N/A";

        this.ui.elements.selectedCustomerName.textContent = customerName;
        this.ui.elements.selectedCustomerPhone.textContent = customerPhone;
        this.ui.elements.selectedStartAddress.textContent =
            order.distribution_center?.name || "N/A";
        this.ui.elements.selectedDeliveryAddress.textContent = deliveryAddress;

        const statusColor = CONFIG.STATUS.COLORS[order.status] || "secondary";
        const statusLabel =
            CONFIG.STATUS.TRANSLATIONS[order.status] || order.status;
        this.ui.elements.selectedOrderStatus.textContent = statusLabel;
        this.ui.elements.selectedOrderStatus.className = `badge bg-${statusColor}`;

        this.ui.showSelectedOrderDetails();
    }

    highlightSelectedOrder(orderNumber) {
        document.querySelectorAll(".order-card").forEach((card) => {
            card.classList.remove("selected");
        });

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
}
