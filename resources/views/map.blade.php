{{--<!DOCTYPE html>
<html>
<head>
    <title>Laravel Map with Full Options</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin=""/>
    <style>
        #mapid { height: 500px; }
    </style>
</head>
<body>
    <h1>Laravel Map with Full Options</h1>
    <div id="mapid"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin=""></script>
    <script>
        var map = L.map('mapid').setView([3.8775, 11.5468], 13);

        var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        var darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        });

        osmLayer.addTo(map); // Default layer

        var baseMaps = {
            "OpenStreetMap": osmLayer,
            "Dark Mode": darkLayer
        };

        L.control.layers(baseMaps, null).addTo(map);

        var marker = L.marker([3.9, 11.5]).addTo(map);
        marker.bindPopup("<b>Hello world!</b><br>I am a popup.").openPopup();

        var circle = L.circle([3.8775, 11.5468], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 500
        }).addTo(map).bindPopup("I am a circle.");

        var polygon = L.polygon([
            [3.704, 11.347],
            [3.570, 10.874],
            [3.24, 9.487]
        ]).addTo(map).bindPopup("I am a polygon.");

        map.on('click', function(e) {
            alert("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);
        });
    </script>
</body>
</html>--}}


<!DOCTYPE html>
<html>
<head>
    <title>Laravel Map with Search</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin=""/>
    <style>
        #mapid { height: 500px; }
        #search-container {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.6);
        }
        #search-results {
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .route-option {
            margin-bottom: 5px;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .route-option:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <h1>Laravel Map with Search</h1>
    <div id="search-container">
        <div>
            <label for="start-point">Start:</label>
            <input type="text" id="start-point" placeholder="Latitude, Longitude or Address">
        </div>
        <div>
            <label for="end-point">End:</label>
            <input type="text" id="end-point" placeholder="Latitude, Longitude or Address">
        </div>
        <button onclick="findRoute()">Search Route</button>
        <div id="search-results" style="display: none;"></div>
    </div>
    <div id="mapid"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin=""></script>
    <script>
        var map = L.map('mapid').setView([3.8775, 11.5468], 13);
        var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        });

        var baseMaps = {
            "OpenStreetMap": osmLayer,
            "Dark Mode": darkLayer
        };

        L.control.layers(baseMaps, null).addTo(map);

        var currentCircle;
        var popup = L.popup();

        function onMapClick(e) {
            popup
                .setLatLng(e.latlng)
                .setContent("You clicked the map at " + e.latlng.toString())
                .openOn(map);

            // Move the circle to the clicked location
            if (currentCircle) {
                map.removeLayer(currentCircle);
            }
            currentCircle = L.circle(e.latlng, {
                color: 'blue',
                fillColor: '#3388ff',
                fillOpacity: 0.5,
                radius: 500
            }).addTo(map);
        }

        map.on('click', onMapClick);

        var marker = L.marker([3.9, 11.5]).addTo(map)
            .bindPopup("<b>Hello world!</b><br>I am a popup.").openPopup();

        var polygon = L.polygon([
            [3.901637, 11.529275],
            [3.901391, 11.530144],
            [3.90229, 11.530499],
            [3.902365, 11.529747]
        ]).addTo(map).bindPopup("I am a polygon.");

        function findRoute() {
            var start = document.getElementById('start-point').value;
            var end = document.getElementById('end-point').value;
            var searchResultsDiv = document.getElementById('search-results');
            searchResultsDiv.style.display = 'block';
            searchResultsDiv.innerHTML = '<p>Searching for routes...</p>';

            // In a real application, you would send these coordinates/addresses
            // to a backend Laravel route that uses a routing service (like OSRM).
            // For this frontend example, we'll simulate some results.

            // Simulate fetching route data (replace with actual API call)
            setTimeout(function() {
                var routes = [
                    {
                        name: "Fastest Route",
                        distance: "5 km",
                        duration: "10 minutes",
                        steps: ["Head north", "Turn left on Main St", "Arrive at destination"]
                    },
                    {
                        name: "Scenic Route",
                        distance: "7 km",
                        duration: "15 minutes",
                        steps: ["Go south", "Follow the river road", "Turn right", "Destination ahead"]
                    }
                ];

                var resultsHTML = '<h3>Available Routes:</h3>';
                routes.forEach(function(route, index) {
                    resultsHTML += '<div class="route-option">';
                    resultsHTML += `<strong>${route.name}</strong><br>`;
                    resultsHTML += `Distance: ${route.distance}<br>`;
                    resultsHTML += `Estimated Time: ${route.duration}<br>`;
                    resultsHTML += 'Steps: ' + route.steps.join(', ') + '<br>';
                    resultsHTML += '</div>';
                });

                searchResultsDiv.innerHTML = resultsHTML;
            }, 1500); // Simulate API delay
        }
    </script>
</body>
</html>










<!DOCTYPE html>
<html>
<head>
    <title>Google Maps Integration</title>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <input id="search-input" type="text" placeholder="Enter a location">
    <div id="map"></div>
    <div id="directions-panel"></div>

    <script>
        let map;
        let directionsService;
        let directionsRenderer;
        let autocomplete;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: -1.9403, lng: 30.0619}, // Initial map center (e.g., Yaoundé)
                zoom: 12
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({ map: map, panel: document.getElementById('directions-panel') });

            // Autocomplete for search
            const searchInput = document.getElementById('search-input');
            autocomplete = new google.maps.places.Autocomplete(searchInput);

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);  // Why 17? Because it looks good.
                }
            });
        }

        function calculateAndDisplayRoute(destination) {
            directionsService.route(
                {
                    origin: map.getCenter(), // Or a fixed starting point
                    destination: destination,
                    travelMode: google.maps.TravelMode.DRIVING // You can change this (TRANSIT, WALKING, BICYCLING)
                },
                (response, status) => {
                    if (status === "OK") {
                        directionsRenderer.setDirections(response);
                    } else {
                        window.alert("Directions request failed due to " + status);
                    }
                }
            );
        }

        // Event listener for submitting the search (you might want a button instead)
        document.getElementById('search-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const destination = this.value;
                if (destination) {
                    calculateAndDisplayRoute(destination);
                }
            }
        });

        window.initMap = initMap;
    </script>
</body>
</html>