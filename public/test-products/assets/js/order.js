$(document).ready(function() {
    // Initialize CartService
    CartService.init();

    let allCustomers = []; // To store all customers with their addresses
    let allProducts = []; // To store all products for the selected distribution center
    let selectedCustomerData = null; // To store the currently selected customer's full data

    // Function to populate customer addresses
    function populateCustomerAddresses(customerData) {
        const customerAddressSelect = $("#customer_address");
        customerAddressSelect.empty().append('<option value="">Sélectionnez une adresse</option>');
        console.log("populateCustomerAddresses called with:", customerData);

        if (customerData && customerData.deliveryAddresses) {
            console.log("Found delivery addresses:", customerData.deliveryAddresses);
            customerData.deliveryAddresses.forEach(function(address) {
                customerAddressSelect.append(`<option value="${address.id}">${address.address}</option>`);
            });
            console.log("Number of addresses appended:", customerData.deliveryAddresses.length);
        } else {
            console.log("No delivery addresses found for this customer.");
        }
    }

    // Function to re-fetch customers and update UI
    function refreshCustomersAndAddresses(customerIdToRefresh = null) {
        console.log("refreshCustomersAndAddresses called with customerIdToRefresh:", customerIdToRefresh);
        if (customerIdToRefresh) {
            // Fetch only the specific customer to update their addresses
            ApiService.fetchCustomer(customerIdToRefresh)
                .done(function(response) {
                    console.log("Response from fetchCustomer:", response);
                    if (response && response.data) {
                        const updatedCustomer = response.data; // Now expects a single object
                        // Find and replace the updated customer in allCustomers array
                        const index = allCustomers.findIndex(cust => cust.id == updatedCustomer.id);
                        if (index !== -1) {
                            allCustomers[index] = updatedCustomer;
                            selectedCustomerData = updatedCustomer; // Update selectedCustomerData if it's the one being refreshed
                            console.log("allCustomers updated. selectedCustomerData:", selectedCustomerData);
                        } else {
                            console.warn("Updated customer not found in allCustomers array. This might indicate a data mismatch.");
                        }
                        populateCustomerAddresses(updatedCustomer);
                    } else {
                        console.warn("fetchCustomer response.data is empty or null.");
                    }
                })
                .fail(function(jqXHR) {
                    console.error("Error fetching single customer:", jqXHR.responseText);
                });
        } else {
            // Initial fetch of all customers for the dropdown
            ApiService.fetchCustomers()
                .done(function(response) {
                    console.log("Response from fetchCustomers (initial load):", response);
                    if (response && response.data) {
                        allCustomers = response.data; // Store all customers
                        const customerSelect = $("#customer");
                        customerSelect.empty().append('<option value="">Sélectionnez un client</option>');
                        response.data.forEach(function(customer) {
                            customerSelect.append(`<option value="${customer.id}">${customer.full_name}</option>`);
                        });
                        console.log("Initial customer dropdown populated.");
                    } else {
                        console.warn("fetchCustomers response.data is empty or null.");
                    }
                })
                .fail(function(jqXHR) {
                    console.error("Error fetching customers (initial load):", jqXHR.responseText);
                });
        }
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
        const selectedId = $(this).val();
        console.log("Customer selected:", selectedId);
        if (selectedId) {
            ApiService.fetchCustomer(selectedId)
                .done(function(response) {
                    if (response && response.data) {
                        selectedCustomerData = response.data; // Update selectedCustomerData with fresh data
                        populateCustomerAddresses(selectedCustomerData);
                        console.log("Customer data and addresses refreshed for ID:", selectedId);
                    }
                })
                .fail(function(jqXHR) {
                    console.error("Error fetching customer details on selection:", jqXHR.responseText);
                    selectedCustomerData = null; // Clear selected customer data on error
                    populateCustomerAddresses(null); // Clear addresses dropdown
                });
        } else {
            selectedCustomerData = null; // Clear selected customer data if no customer is selected
            populateCustomerAddresses(null); // Clear addresses dropdown
        }
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
        if (!selectedCustomerData || !selectedCustomerData.id) { // Use selectedCustomerData.id (which is customer_id)
            alert("Veuillez d'abord sélectionner un client.");
            event.preventDefault(); // Prevent modal from opening
        }
    });

    // Handle new address form submission
    $("#add-address-form").on("submit", function(e) {
        e.preventDefault();

        if (!selectedCustomerData || !selectedCustomerData.id) { // Use selectedCustomerData.id (which is customer_id)
            alert("Veuillez sélectionner un client avant d'ajouter une adresse.");
            return;
        }

        const customerIdForAddress = selectedCustomerData.id; // Use the correct customer_id

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

        console.log("Sending address data:", formData);
        ApiService.createCustomerDeliveryAddress(customerIdForAddress, formData)
            .done(function(response) {
                console.log("Add Address Success Response:", response);
                alert("Adresse ajoutée avec succès!");
                $('#addAddressModal').modal('hide'); // Close the modal
                $("#add-address-form")[0].reset(); // Clear the form
                refreshCustomersAndAddresses(selectedCustomerData.id); // Re-fetch *only* the selected customer and refresh addresses
            })
            .fail(function(jqXHR) {
                console.error("Add Address Error Response:", jqXHR);
                let errorMsg = "Erreur lors de l'ajout de l'adresse.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg += ` ${jqXHR.responseJSON.message}`;
                }
                alert(errorMsg);
            })
            .always(function() {
                saveAddressBtn.prop("disabled", false);
                addressLoader.addClass("d-none");
            });
    });
});
