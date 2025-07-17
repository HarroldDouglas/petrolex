window.ApiService = (function() {
    const apiToken = localStorage.getItem('api_token');

    function getHeaders() {
        if (!apiToken) {
            console.error("Token API non trouvé. Redirection vers la page de connexion.");
            window.location.href = '/test-products/index.html';
            return null;
        }
        return {
            'Authorization': `Bearer ${apiToken}`,
            'Accept': 'application/json'
        };
    }

    function fetchCustomers() {
        const headers = getHeaders();
        if (!headers) return $.Deferred().reject("Token manquant").promise();

        return $.ajax({
            url: "/api/customers",
            type: "GET",
            headers: headers
        });
    }

    function fetchDistributionCenters() {
        const headers = getHeaders();
        if (!headers) return $.Deferred().reject("Token manquant").promise();

        return $.ajax({
            url: "/api/distribution-centers",
            type: "GET",
            headers: headers
        });
    }

    return {
        fetchCustomers,
        fetchDistributionCenters
    };
})();
