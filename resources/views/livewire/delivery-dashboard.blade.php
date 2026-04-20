<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Livraisons Actives</h5>
                <button class="btn btn-sm btn-outline-primary" wire:click="refreshDeliveries">
                    🔄 Actualiser
                </button>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                @forelse($deliveries as $delivery)
                    <div class="delivery-item p-3 mb-2 border rounded cursor-pointer {{ $selectedDelivery && $selectedDelivery['order_number'] === $delivery['order_number'] ? 'bg-primary text-white' : 'bg-light' }}"
                         wire:click="selectDelivery('{{ $delivery['order_number'] }}')"
                         style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $delivery['order_number'] }}</h6>
                                <p class="mb-1"><small>Client: {{ $delivery['customer_name'] ?? 'N/A' }}</small></p>
                                <p class="mb-1"><small>Livreur: {{ $delivery['driver_name'] ?? 'Non assigné' }}</small></p>
                                @if(isset($delivery['driver_lat']) && isset($delivery['driver_lng']) && $delivery['driver_lat'] && $delivery['driver_lng'])
                                    <p class="mb-0"><small class="text-success">📍 Position disponible</small></p>
                                @else
                                    <p class="mb-0"><small class="text-muted">📍 Position non disponible</small></p>
                                @endif
                            </div>
                            <div>
                                <span class="badge 
                                    @if($delivery['status'] === 'pending') bg-secondary
                                    @elseif($delivery['status'] === 'assigned') bg-primary
                                    @elseif($delivery['status'] === 'picked_up') bg-warning
                                    @elseif($delivery['status'] === 'in_transit') bg-info
                                    @endif">
                                    {{ ucfirst($delivery['status']) }}
                                </span>
                                @if(isset($delivery['estimated_duration']) && $delivery['estimated_duration'])
                                    <div class="mt-1"><small>{{ $delivery['estimated_duration'] }} min</small></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-muted mb-2">Aucune livraison active</p>
                        <small class="text-muted">Les livraisons avec statut 'pending', 'assigned', 'picked_up' ou 'in_transit' apparaîtront ici</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Carte des livraisons</h5>
                @if($selectedDelivery)
                    <small class="text-muted">Livraison sélectionnée: {{ $selectedDelivery['order_number'] }}</small>
                @endif
            </div>
            <div class="card-body">
                <div id="delivery-map" style="height: 600px; width: 100%; border-radius: 8px;"></div>
                @if(count($deliveries) === 0)
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <p class="text-muted">Aucune livraison à afficher sur la carte</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let map;
let deliveryMarkers = {};
let routes = {};
let mapLoaded = false;

// Initialiser la carte
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});

function initMap() {
    google_mapsgl.accessToken = 'pk.eyJ1IjoiaGFycm9sZHdhZm8iLCJhIjoiY21kcjkwenJxMGVtYzJsczY0aXgzbGN6OCJ9.WGRlvNUJFaEFbmvoeTTGlQ';
    
    map = new google_mapsgl.Map({
        container: 'delivery-map',
        style: 'google_maps://styles/google_maps/streets-v11',
        center: [11.502, 3.848], // Yaoundé, Cameroun
        zoom: 12
    });

    map.on('load', function() {
        console.log('Carte chargée sur le dashboard manager');
        mapLoaded = true;
        
        setTimeout(() => {
            map.resize();
            console.log('Carte redimensionnée');
        }, 100);
        
        loadInitialDeliveries();
    });

    map.on('error', function(e) {
        console.error('Erreur de carte:', e);
    });
}

function loadInitialDeliveries() {
    if (!mapLoaded) return;
    
    @if($deliveries && count($deliveries) > 0)
        @foreach($deliveries as $delivery)
            @if(isset($delivery['driver_lat']) && isset($delivery['driver_lng']) && $delivery['driver_lat'] && $delivery['driver_lng'])
                addDeliveryToMap(@json($delivery));
            @endif
        @endforeach
    @endif
}

function addDeliveryToMap(delivery) {
    if (!mapLoaded) return;
    
    const orderNumber = delivery.order_number;
    console.log('Ajout de la livraison sur la carte:', orderNumber);
    
    // Supprimer l'ancien marqueur s'il existe
    if (deliveryMarkers[orderNumber]) {
        if (deliveryMarkers[orderNumber].driver) {
            deliveryMarkers[orderNumber].driver.remove();
        }
        if (deliveryMarkers[orderNumber].destination) {
            deliveryMarkers[orderNumber].destination.remove();
        }
    }

    // Ajouter marqueur livreur
    if (delivery.driver_lat && delivery.driver_lng) {
        const driverPopup = new google_mapsgl.Popup()
            .setHTML(`
                <h6>${delivery.driver_name || 'Livreur'}</h6>
                <p>Commande: ${orderNumber}</p>
                <p>Client: ${delivery.customer_name || 'N/A'}</p>
                <p>ETA: ${delivery.estimated_duration || 'N/A'} min</p>
            `);

        const driverMarker = new google_mapsgl.Marker({ color: '#1E88E5' })
            .setLngLat([parseFloat(delivery.driver_lng), parseFloat(delivery.driver_lat)])
            .setPopup(driverPopup)
            .addTo(map);

        deliveryMarkers[orderNumber] = deliveryMarkers[orderNumber] || {};
        deliveryMarkers[orderNumber].driver = driverMarker;

        // Ajouter marqueur destination si disponible
        if (delivery.destination_lat && delivery.destination_lng) {
            const destPopup = new google_mapsgl.Popup()
                .setHTML(`
                    <h6>Destination</h6>
                    <p>Client: ${delivery.customer_name || 'N/A'}</p>
                    <p>${delivery.destination_address || 'Adresse non spécifiée'}</p>
                `);

            const destinationMarker = new google_mapsgl.Marker({ color: '#E53935' })
                .setLngLat([parseFloat(delivery.destination_lng), parseFloat(delivery.destination_lat)])
                .setPopup(destPopup)
                .addTo(map);

            deliveryMarkers[orderNumber].destination = destinationMarker;

            // Ajouter la route si disponible
            if (delivery.route_geometry) {
                addRouteToMap(orderNumber, delivery.route_geometry);
            }
        }
    }
}

function addRouteToMap(orderNumber, geometry) {
    if (!mapLoaded) return;
    
    const sourceId = `route-${orderNumber}`;
    const layerId = `route-layer-${orderNumber}`;

    // Supprimer l'ancienne route
    if (map.getLayer(layerId)) {
        map.removeLayer(layerId);
    }
    if (map.getSource(sourceId)) {
        map.removeSource(sourceId);
    }

    // Ajouter la nouvelle route
    map.addSource(sourceId, {
        type: 'geojson',
        data: {
            type: 'Feature',
            properties: {},
            geometry: geometry
        }
    });

    map.addLayer({
        id: layerId,
        type: 'line',
        source: sourceId,
        layout: {
            'line-join': 'round',
            'line-cap': 'round'
        },
        paint: {
            'line-color': '#1E88E5',
            'line-width': 4,
            'line-opacity': 0.8
        }
    });
}

// Écouter les événements Livewire - VERSION SIMPLE
document.addEventListener('livewire:initialized', function () {
    console.log('Livewire initialisé dans le dashboard manager');
    
    // Réactualiser après les actions Livewire
    @this.on('deliveries-refreshed', () => {
        console.log('Livraisons actualisées');
        // Pas de réinitialisation, juste attendre et recharger
        setTimeout(() => {
            if (mapLoaded) {
                loadInitialDeliveries();
            }
        }, 500);
    });
    
    @this.on('delivery-selected', (delivery) => {
        console.log('Livraison sélectionnée:', delivery);
        
        let deliveryData = Array.isArray(delivery) ? delivery[0] : delivery;
        
        if (deliveryData && deliveryData.driver_lat && deliveryData.driver_lng && mapLoaded) {
            // Centrer sur la livraison
            map.flyTo({
                center: [parseFloat(deliveryData.driver_lng), parseFloat(deliveryData.driver_lat)],
                zoom: 15,
                duration: 1000
            });
        }
    });
});
</script>
@endpush
