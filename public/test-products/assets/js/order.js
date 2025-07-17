$(document).ready(function() {
    // Initialize CartService
    CartService.init();

    // Fetch Customers
    ApiService.fetchCustomers()
        .done(function(response) {
            if (response && response.data) {
                const customerSelect = $("#customer");
                response.data.forEach(function(customer) {
                    customerSelect.append(`<option value="${customer.id}">${customer.full_name}</option>`);
                });
            }
        })
        .fail(function(jqXHR) {
            console.error("Error fetching customers:", jqXHR.responseText);
        });

    // Fetch Distribution Centers
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

    $("#addProductBtn").on("click", function() {
        // Placeholder for adding product to cart
        const newProduct = {
            name: `Produit exemple ${CartService.getCart().length + 1}`,
            option_name: "Recharge",
            price: 6500, // Example price
            quantity: parseInt($("#productQuantity").val())
        };
        CartService.addToCart(newProduct);
    });
});