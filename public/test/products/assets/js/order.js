$(document).ready(function() {
    // Initialize core services
    CartService.init();
    CustomerHandler.init();
    ProductHandler.init();
    AddressModalHandler.init(CustomerHandler); // Pass CustomerHandler to access selected customer data and refresh function

    // Fetch initial data for dropdowns
    ApiService.fetchDistributionCenters()
        .done(function(response) {
            if (response && response.data) {
                const dcSelect = $("#distribution_center");
                response.data.forEach(function(dc) {
                    dcSelect.append(`<option value="${dc.id}">${dc.name}</option>`);
                });
            }
        })
        .fail(function(jqXHR) {
            console.error("Error fetching distribution centers:", jqXHR.responseText);
        });

    ApiService.fetchPaymentMethods()
        .done(function(response) {
            if (response && response.data) {
                const paymentMethodSelect = $("#payment_method");
                response.data.forEach(function(method) {
                    paymentMethodSelect.append(`<option value="${method.value}">${method.label}</option>`);
                });
            }
        })
        .fail(function(jqXHR) {
            console.error("Error fetching payment methods:", jqXHR.responseText);
        });

    ApiService.fetchDeliveryTypes()
        .done(function(response) {
            if (response && response.data) {
                const deliveryTypeSelect = $("#delivery_type");
                response.data.forEach(function(type) {
                    deliveryTypeSelect.append(`<option value="${type.value}">${type.label}</option>`);
                });
            }
        })
        .fail(function(jqXHR) {
            console.error("Error fetching delivery types:", jqXHR.responseText);
        });
    
    $('#order-form').on('submit', function(event) {
        event.preventDefault();
        console.log('order form submitted');
        const customerId = $('#customer').val();
        const distributionCenterId = $('#distribution_center').val();
        const customerDeliveryAddressId = $('#customer_address').val();
        const paymentMethod = $('#payment_method').val();
        const deliveryType = $('#delivery_type').val();
        const cartItems = CartService.getCart();

        if (!customerId || !distributionCenterId || !customerDeliveryAddressId || !paymentMethod || !deliveryType) {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        // Transform cart items to match API expected format
        const orderItems = cartItems.map(item => ({
            product_category_id: item.id,
            quantity: item.quantity,
            option: item.option_value, // This will be null for accessories
            unit_price: item.price
        }));

        const orderData = {
            customer_id: customerId,
            distribution_center_id: distributionCenterId,
            delivery_address_id: customerDeliveryAddressId,
            payment_method: paymentMethod,
            delivery_type: deliveryType,
            items: orderItems
        };

        ApiService.storeOrder(orderData)
            .done(function(response) {
                if (response && response.data) {
                    alert(`Commande créée avec succès! ID de la commande: ${response.data.id}`);
                    window.location.href = '/test-products/order.html';
                } else {
                    alert('Erreur lors de l\'enregistrement de la commande.');
                    console.log('response', response);
                }
            })
            .fail(function(jqXHR) {
                console.error("Error storing order:", jqXHR.responseText);
                alert('Erreur lors de l\'enregistrement de la commande. Voir la console pour plus de détails.');
            });
    });

    // Simulate selection of Payment Method and Delivery Type
    // This is a placeholder and would typically be driven by user interaction
    // or pre-selected values.
    $("#payment_method").val("cash"); // Example: set Cash as default
    $("#delivery_type").val("home_delivery"); // Example: set Home Delivery as default
});