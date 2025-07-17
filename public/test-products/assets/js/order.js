$(document).ready(function() {
    // Initialize CartService
    CartService.init();

    let allCustomers = []; // To store all customers with their addresses
    let allProducts = []; // To store all products for the selected distribution center

    // Function to populate customer addresses
    function populateCustomerAddresses(customerId) {
        const customerAddressSelect = $("#customer_address");
        customerAddressSelect.empty().append('<option value="">Sélectionnez une adresse</option>');

        if (customerId) {
            const selectedCustomer = allCustomers.find(cust => cust.id == customerId);
            if (selectedCustomer && selectedCustomer.deliveryAddresses) {
                selectedCustomer.deliveryAddresses.forEach(function(address) {
                    customerAddressSelect.append(`<option value="${address.id}">${address.address}</option>`);
                });
            }
        }
    }

    // Function to re-fetch customers and update UI
    function refreshCustomersAndAddresses(selectedCustomerId = null) {
        ApiService.fetchCustomers()
            .done(function(response) {
                if (response && response.data) {
                    allCustomers = response.data; // Update stored customers
                    const customerSelect = $("#customer");
                    customerSelect.empty().append('<option value="">Sélectionnez un client</option>');
                    response.data.forEach(function(customer) {
                        customerSelect.append(`<option value="${customer.id}">${customer.full_name}</option>`);
                    });
                    // Re-select the customer if one was previously selected
                    if (selectedCustomerId) {
                        customerSelect.val(selectedCustomerId).trigger('change');
                    }
                }
            })
            .fail(function(jqXHR) {
                console.error("Error fetching customers:", jqXHR.responseText);
            });
    }

    // Initial fetch of customers
    refreshCustomersAndAddresses();

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

    // Event listener for Customer selection change
    $("#customer").on("change", function() {
        const selectedCustomerId = $(this).val();
        populateCustomerAddresses(selectedCustomerId);
    });

    // Event listener for Distribution Center selection change
    $("#distribution_center").on("change", function() {
        const selectedDcId = $(this).val();
        const productSelect = $("#selectedProduct");
        productSelect.empty().append('<option value="">Sélectionner un produit</option>');
        productSelect.prop("disabled", true); // Disable until products are loaded
        allProducts = []; // Clear previous products

        if (selectedDcId) {
            ApiService.fetchProductsByDistributionCenter(selectedDcId)
                .done(function(response) {
                    if (response && response.data) {
                        allProducts = response.data; // Store all products
                        productSelect.prop("disabled", false); // Enable product select
                        response.data.forEach(function(product) {
                            productSelect.append(`<option value="${product.id}">${product.name}</option>`);
                        });
                    }
                })
                .fail(function(jqXHR) {
                    console.error("Error fetching products:", jqXHR.responseText);
                    productSelect.prop("disabled", true); // Keep disabled on error
                });
        }
    });

    // Add Product to Cart button handler (placeholder)
    $("#addProductBtn").on("click", function() {
        const newProduct = {
            name: `Produit exemple ${CartService.getCart().length + 1}`,
            option_name: "Recharge",
            price: 6500, // Example price
            quantity: parseInt($("#productQuantity").val())
        };
        CartService.addToCart(newProduct);
    });

    // --- New Address Modal Logic ---

    // Before showing the modal, check if a customer is selected
    $('#addAddressModal').on('show.bs.modal', function (event) {
        const selectedCustomerId = $("#customer").val();
        if (!selectedCustomerId) {
            alert("Veuillez d'abord sélectionner un client.");
            event.preventDefault(); // Prevent modal from opening
        }
    });

    // Handle new address form submission
    $("#add-address-form").on("submit", function(e) {
        e.preventDefault();

        const selectedCustomerId = $("#customer").val();
        if (!selectedCustomerId) {
            alert("Veuillez sélectionner un client avant d'ajouter une adresse.");
            return;
        }

        const formData = {};
        $(this).find("input, select, textarea").each(function() {
            const input = $(this);
            if (input.attr("name")) {
                if (input.attr("type") === "checkbox") {
                    formData[input.attr("name")] = input.is(':checked');
                } else {
                    formData[input.attr("name")] = input.val();
                }
            }
        });

        const saveAddressBtn = $("#saveAddressBtn");
        const addressLoader = $("#addressLoader");

        saveAddressBtn.prop("disabled", true);
        addressLoader.removeClass("d-none");

        ApiService.createCustomerDeliveryAddress(selectedCustomerId, formData)
            .done(function(response) {
                alert("Adresse ajoutée avec succès!");
                $('#addAddressModal').modal('hide'); // Close the modal
                $("#add-address-form")[0].reset(); // Clear the form
                refreshCustomersAndAddresses(selectedCustomerId); // Re-fetch customers and refresh addresses
            })
            .fail(function(jqXHR) {
                let errorMsg = "Erreur lors de l'ajout de l'adresse.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg += ` ${jqXHR.responseJSON.message}`;
                }
                alert(errorMsg);
                console.error("Add Address Error:", jqXHR.responseText);
            })
            .always(function() {
                saveAddressBtn.prop("disabled", false);
                addressLoader.addClass("d-none");
            });
    });
});