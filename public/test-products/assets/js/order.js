$(document).ready(function() {
    const apiToken = localStorage.getItem('api_token');
    if (!apiToken) {
        window.location.href = '/test-products/index.html';
        return;
    }

    const cart = [];

    function updateCartView() {
        const cartItemsContainer = $("#cart-items");
        const emptyCartMsg = $("#cart-empty-msg");
        cartItemsContainer.empty();

        if (cart.length === 0) {
            emptyCartMsg.show();
        } else {
            emptyCartMsg.hide();
            cart.forEach((item, index) => {
                const totalPrice = item.price * item.quantity;
                const row = `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.option_name || 'N/A'}</td>
                        <td>${item.price} XAF</td>
                        <td>${item.quantity}</td>
                        <td>${totalPrice} XAF</td>
                        <td>
                            <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                cartItemsContainer.append(row);
            });
        }
    }

    $("#addProductBtn").on("click", function() {
        // This is a placeholder logic. Integration with API will come next.
        const newProduct = {
            name: `Produit exemple ${cart.length + 1}`,
            option_name: "Recharge",
            price: 6500, // Example price
            quantity: parseInt($("#productQuantity").val())
        };

        cart.push(newProduct);
        updateCartView();
    });

    $("#cart-items").on("click", ".remove-item", function() {
        const index = $(this).data("index");
        cart.splice(index, 1);
        updateCartView();
    });

    // Initial view
    updateCartView();
});
