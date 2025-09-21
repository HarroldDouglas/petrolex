$(document).ready(function() {
    $("#login_button").click(function() {
        const login = $("#login").val();
        const password = $("#password").val();
        const loginStatus = $("#login_status");

        loginStatus.text("Connexion en cours...").removeClass("alert-danger alert-success").addClass("alert-warning");

        $.ajax({
            url: "/api/login",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                login: login,
                password: password,
                device_name: "test-page"
            }),
            success: function(response) {
                // Log the full response for debugging
                console.log("Login Response:", response);

                if (response && response.data && response.data.access_token) {
                    localStorage.setItem('api_token', response.data.access_token);
                    localStorage.setItem('user_full_name', response.data.user.first_name + ' ' + response.data.user.last_name);
                    localStorage.setItem('test-products-user-email', response.data.user.email);
                    if (response.data.user.phone_number) {
                        localStorage.setItem('test-products-user-phone', response.data.user.phone_number);
                    }
                    console.log("User data from API:", response.data.user);
                    
                    const user = response.data.user;
                    if (user && user.roles) {
                        if (user.roles.includes('delivery_person') && user.delivery_person_id) {
                            localStorage.setItem('delivery_person_id', user.delivery_person_id);
                            window.location.href = '/test/products/delivery-person-orders.html';
                        } else if (user.roles.includes('customer') && user.customer_id) {
                            localStorage.setItem('customer_id', user.customer_id);
                            window.location.href = '/test/products/list.html';
                        } else {
                            // Default redirection if no specific role or ID is found
                            window.location.href = '/test/products/list.html';
                        }
                    } else {
                        // Fallback if roles are not present
                        window.location.href = '/test/products/list.html';
                    }
                    loginStatus.text("Connexion réussie! Redirection...").removeClass("alert-warning").addClass("alert-success");
                } else {
                    loginStatus.text("Échec de la connexion: Jeton non reçu. Vérifiez la console pour la réponse.").removeClass("alert-warning").addClass("alert-danger");
                }
            },
            error: function(jqXHR) {
                let errorMsg = "Erreur de connexion.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg += ` ${jqXHR.responseJSON.message}`;
                }
                loginStatus.text(errorMsg).removeClass("alert-warning").addClass("alert-danger");
                console.error("Login Error Response:", jqXHR.responseText);
            }
        });
    });
});
