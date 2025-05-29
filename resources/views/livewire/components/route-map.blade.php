<div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="me-3 mb-2">
                            <i class="ti ti-car text-primary me-1"></i> 
                            <strong>Mode:</strong> Car
                        </div>
                        <div class="me-3 mb-2">
                            <i class="ti ti-map-pin text-primary me-1"></i>
                            <strong>Departure:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $pointA['lat'] }},{{ $pointA['lng'] }}" 
                               target="_blank" title="Open in Google Maps" class="text-primary">
                                {{ $pointA['name'] }} <i class="ti ti-external-link text-primary"></i>
                            </a>
                        </div>
                        <div class="me-3 mb-2">
                            <i class="ti ti-arrow-right text-secondary"></i>
                        </div>
                        <div class="mb-2">
                            <i class="ti ti-flag text-danger me-1"></i>
                            <strong>Destination:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $pointB['lat'] }},{{ $pointB['lng'] }}" 
                               target="_blank" title="Open in Google Maps" class="text-danger">
                                {{ $pointB['name'] }} <i class="ti ti-external-link text-danger"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="route-info mb-3" id="route-info-panel">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-ruler"></i> Distance:</strong> <span id="distance-text">Loading...</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="ti ti-clock"></i> Estimated time:</strong> <span id="duration-text">Loading...</span></p>
                    </div>
                </div>
            </div>

            <div id="map" class="map-container"></div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/route-map.css') }}">
    @endpush

    @push('scripts')
    <script src="{{ asset('assets/js/route-map.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const routeMapConfig = {
                origin: {
                    lat: {{ $pointA['lat'] }}, 
                    lng: {{ $pointA['lng'] }},
                    name: "{{ $pointA['name'] }}"
                },
                destination: {
                    lat: {{ $pointB['lat'] }}, 
                    lng: {{ $pointB['lng'] }},
                    name: "{{ $pointB['name'] }}"
                },
            };
            
            const routeMap = new RouteMap(routeMapConfig);
            
            function initMap() {
                routeMap.initialize();
            }
            
            window.initMap = initMap;
        });
    </script>
    <script async defer 
            src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMap&loading=async">
    </script>
    @endpush
</div>
