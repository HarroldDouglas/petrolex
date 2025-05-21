@extends('layout.master')
@section('title', 'Order Details')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
    
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">

    
    <!-- google map js AIzaSyBBjJDwpFHkFRB5zyckO2rQWjyMqV3sKtI
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBjJDwpFHkFRB5zyckO2rQWjyMqV3sKtI&callback=initMap"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBjJDwpFHkFRB5zyckO2rQWjyMqV3sKtI&libraries=places,directions&callback=initMap"></script>
    -->
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin=""/>
    <style>
        #mapid { height: 500px; }
        #search-container {
            position: absolute;
            top: 20px;
            left: 27px;
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
        .leaflet-control-zoom.leaflet-control{
            top: 120px;
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
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1 d-flex justify-content-between align-items-center">
            <div class="col">
                <h4 class="main-title">Orders Details</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Orders Details</a>
                    </li>
                </ul>
            </div>
            <div class="col mt-3 mb-3 text-end">
                <button class="btn btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMap" aria-expanded="false" aria-controls="collapseMap">
                    Voir en temp réel
                </button>
            </div>
        </div>
        <!-- Breadcrumb end -->

        @if ($order)
            <!-- Order Details start -->
            <div class="row order-details">
                
                <div class="col-12 mb-3">
                    <div class="collapse" id="collapseMap">
                        <div class="card card-body">
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
                        </div>
                    </div>
                    {{--<div id="map-elements" style="display: none;">
                        <div class="map-container">
                            <label for="origin">Origin:</label>
                            <input type="text" id="origin" name="origin">

                            <label for="destination">Destination:</label>
                            <input type="text" id="destination" name="destination">

                            <button onclick="getDirections()">Get Directions</button>

                            <div id="map"></div>

                            <div id="directions-panel"></div>
                        </div>
                    </div>--}}
                </div>
                <div class="col-xxl-8 mt-3">
                    <div class="row">
                        <!-- Order Details start -->
                        <div class="col-lg-6">
                            <div class="card order-details-card">
                                <div class="card-header">
                                    <h5 class="text-nowrap">Order Details ({{ $order['id'] }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-calendar f-s-18 me-2 text-secondary"></i>Date</h6>
                                        <div class="text-end">
                                            <p>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $order['order_date'])->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-credit-card f-s-18 me-2"></i>Payment</h6>
                                        <div class="text-end">
                                            <p>Online</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-truck-delivery f-s-18 me-2"></i>Shipping</h6>
                                        <div class="text-end">
                                            <p> Fast Shipping</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Order Details end -->

                        <!-- Customer Details start -->
                        <div class="col-lg-6">
                            <div class="card order-details-card">
                                <div class="card-header">
                                    <h5>Customer Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-file-invoice text-secondary f-s-18 me-2"></i>Customer</h6>
                                        <div class="text-end">
                                            <p>{{ $order['customer_name'] }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-mail f-s-18 text-secondary me-2"></i>Email</h6>
                                        <div class="text-end">
                                            <p>{{ str_replace(' ', '', strtolower($order['customer_name'])) }}@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-device-mobile f-s-18 text-secondary me-2"></i>contact</h6>
                                        <div class="text-end">
                                            <p>+1 111 134 111</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Customer Details end -->
                    </div>

                    <!-- Order start -->
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                Order : {{ $order['id'] }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="orders-details-datatable app-datatable-default app-scroll table-responsive">
                                <table class="table table-bottom-border text-center align-middle mb-0" id="ticketdatatable">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="text-start">Items Detail</th>
                                        <th scope="col">Order Date</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order['items'] as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="text-start">
                                                            <h6 class="mb-0"> {{ $item['name'] }}</h6>
                                                            <p class="f-w-500 m-0 text-muted f-s-13">Color:
                                                                <span class="text-secondary">White</span>
                                                            </p>
                                                            <p class="f-w-500 m-0 text-muted f-s-13">Size: <span
                                                                    class="text-secondary">Small</span></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $order['order_date'])->format('d/m/Y') }}</td>
                                                <td class="text-success f-w-500">{{ number_format($item['price'], 2) }}</td>
                                                <td class="f-w-600">{{ $item['quantity'] }}</td>
                                                <td class="text-success f-w-500">
                                                    {{ number_format($item['price'] * $item['quantity'], 2) }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-success icon-btn b-r-4">
                                                        <i class="ti ti-edit"></i> </button>
                                                    <button type="button" class="btn btn-danger icon-btn b-r-4 delete-btn">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr class="text-start">
                                        <td colspan="2" class="text-start">Subtotal</td>
                                        <td colspan="4" class="text-center f-w-500">
                                            <strong>{{ number_format($order['total_amount'], 2) }}</strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Order end -->

                </div>
                <!-- Order Status start -->
                <div class="col-xxl-4 mt-3">
                    <div class="card equal-card">
                        <div class="card-header">
                            <h5>Order Status</h5>
                        </div>
                        <div class="card-body">
                            <ul class="app-timeline-box">

                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-shopping-cart f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-primary b-1-primary">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-primary">Order Placed</h6>
                                            <span class="badge text-bg-primary ms-2">20 Min ago</span>
                                        </div>
                                        <p class="mt-2 text-dark">An order has been placed.</p>
                                        <p class="text-secondary">Wed, 15 Dec 2024 - 05:34PM</p>
                                    </div>
                                </li>
                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-secondary h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-checks f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-secondary b-1-secondary">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-secondary">Packed</h6>
                                            <span class="color-light">50 Min ago</span>
                                        </div>
                                        <p class="mt-2">
                                            Your Item has been picked up by courier partner
                                        </p>
                                        <p class="text-secondary">Thu, 20 Dec 2024 - 6:48AM</p>
                                        <span class="badge bg-primary-900">Design</span>
                                        <span class="badge bg-primary-900">HTML</span>
                                    </div>
                                </li>
                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-success h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-truck-delivery f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-success b-1-success">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-success">Shipping</h6>
                                            <span class="badge text-bg-success ms-2">1 hours ago</span>
                                        </div>
                                        <p class="mt-2 text-dark">
                                            Your Item has been picked up by courier partner
                                        </p>
                                        <p class="text-secondary">Thu, 20 Dec 2024 - 5:48AM</p>
                                    </div>
                                </li>
                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-info h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-package f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-info b-1-info">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-info">Delivered</h6>
                                            <span class="badge text-bg-dark ms-2">Nov 10, 14:00</span>
                                        </div>
                                        <p class="text-secondary">Mon, 26 Dec 2024 - 5:00AM</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Order Status end -->
            </div>
            <!-- Order Details end -->
        @else
            <p>Order not found.</p>
        @endif
        <a class="btn btn-info" href="{{ route('dashboard') }}">Back to Order List</a>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- data table js-->
<script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

<!-- js-->
<script src="{{ asset('assets/js/orders_details.js') }}"></script>

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
            }, 1500); 
        }
    </script>
@endsection
