$(document).ready(function() {
    const deliveryPersonId = localStorage.getItem('delivery_person_id');
    const deliveryPersonName = localStorage.getItem('user_full_name');
    const ordersTableBody = $('#ordersTableBody');
    const paginationControls = $('#paginationControls');
    const noOrdersMessage = $('#noOrdersMessage');
    const orderStatusFilter = $('#orderStatusFilter');
    const orderNumberSearch = $('#orderNumberSearch');

    //TODO create an endpoint to get those order status values
    const ORDER_STATUSES = {
        'CONFIRMED': 'Confirmée',
        'IN_PROGRESS': 'En cours de livraison',
        'DELIVERED': 'Livrée',
        'CANCELLED': 'Annulée',
        'PENDING': 'En attente',
    };

    let currentPage = 1;
    let currentFilters = {};

    // Display delivery person's name
    if (deliveryPersonName) {
        $('#deliveryPersonName').text(deliveryPersonName);
    }

    if (!deliveryPersonId) {
        console.error("Delivery Person ID non trouvé. Redirection vers la page de connexion.");
        window.location.href = '/test-products/index.html';
        return;
    }

    // Populate status filter dropdown
    for (const [value, label] of Object.entries(ORDER_STATUSES)) {
        orderStatusFilter.append(`<option value="${value.toLowerCase()}">${label}</option>`);
    }

    function fetchOrders(page = 1, filters = {}) {
        currentPage = page;
        currentFilters = filters;
        ordersTableBody.empty();
        noOrdersMessage.hide();
        paginationControls.empty();

        ApiService.fetchDeliveryPersonOrders(deliveryPersonId, { page: page, ...filters })
            .done(function(response) {
                if (response && response.data && response.data.length > 0) {
                    response.data.forEach(order => {
                        const row = `
                            <tr>
                                <td>${order.order_number}</td>
                                <td>${order.distribution_center ? order.distribution_center.name : 'N/A'}</td>
                                <td>${order.customer ? order.customer.first_name + ' ' + order.customer.last_name : 'N/A'}</td>
                                <td>${order.delivery_address ? order.delivery_address.name : 'N/A'}</td>
                                <td>${new Date(order.order_date).toLocaleDateString()}</td>
                                <td>${ORDER_STATUSES[order.status.toUpperCase()] || order.status}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Changer Statut</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `;
                        ordersTableBody.append(row);
                    });
                    renderPagination(response._metadata.pagination);
                } else {
                    noOrdersMessage.show();
                }
            })
            .fail(function(jqXHR) {
                console.error("Error fetching delivery person orders:", jqXHR.responseText);
                noOrdersMessage.text("Erreur lors du chargement des commandes.").show();
            });
    }

    function renderPagination(pagination) {
        paginationControls.empty();
        if (pagination.total_pages > 1) {
            // Previous Button
            paginationControls.append(`
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Précédent</a>
                </li>
            `);

            // Page Numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                paginationControls.append(`
                    <li class="page-item ${pagination.current_page === i ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            // Next Button
            paginationControls.append(`
                <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Suivant</a>
                </li>
            `);

            // Add click handlers for pagination buttons
            paginationControls.find('.page-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page > 0 && page <= pagination.total_pages) {
                    fetchOrders(page, currentFilters);
                }
            });
        }
    }

    // Search functionality
    orderNumberSearch.on('keyup', function() {
        const searchTerm = $(this).val();
        // Debounce to prevent too many requests
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = setTimeout(() => {
            fetchOrders(1, { ...currentFilters, order_number: searchTerm });
        }, 300);
    });

    orderStatusFilter.on('change', function() {
        const selectedStatus = $(this).val();
        fetchOrders(1, { ...currentFilters, status: selectedStatus });
    });

    // Initial fetch of orders
    fetchOrders();
});
