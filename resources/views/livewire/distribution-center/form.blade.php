<div class="app-product-section">
    <form wire:submit.prevent="submit" class="app-form">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" placeholder="Nom" id="name" wire:model.live="name">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="country_id" class="form-label">Pays</label>
                <select class="form-select" id="country_id" wire:model.live="country_id">
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('country_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="city_id" class="form-label">Ville</label>
                <div wire:loading.remove wire:target="country_id">
                    <select class="form-select select2 searchable" id="city_id" wire:model.live="city_id" data-placeholder="Rechercher une ville">
                        <option value="">Sélectionner une ville</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:loading wire:target="country_id">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> Chargement des villes...
                </div>
                @error('city_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="neighborhood_id" class="form-label">Quartier</label>
                <div wire:loading.remove wire:target="city_id">
                    <select class="form-select select2 searchable" id="neighborhood_id" wire:model.live="neighborhood_id" data-placeholder="Rechercher un quartier">
                        <option value="">Sélectionnez un quartier</option>
                        @foreach($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood->id }}">{{ $neighborhood->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:loading wire:target="city_id">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> Chargement des quartiers...
                </div>
                @error('neighborhood_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="address" class="form-label">Adresse</label>
                <input type="text" class="form-control" placeholder="Adresse" id="address" wire:model.live="address">
                @error('address') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" placeholder="690102030" id="phone" wire:model.live="phone">
                @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="email@example.com" id="email" wire:model.live="email">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                <input type="number" class="form-control" placeholder="Capacité de stockage" id="storage_capacity" wire:model.live="storage_capacity">
                @error('storage_capacity') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-12 mb-3">
                <label for="mapsLink" class="form-label">Lien Google Maps <span class="text-muted">(optionnel)</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="mapsLink"
                        placeholder="Collez ici un lien Google Maps — la latitude et la longitude seront remplies automatiquement"
                        wire:model.defer="mapsLink" wire:keydown.enter.prevent="extractCoordinates">
                    <button class="btn btn-outline-primary" type="button" wire:click="extractCoordinates"
                        wire:loading.attr="disabled" wire:target="extractCoordinates">
                        <span wire:loading.remove wire:target="extractCoordinates">Extraire</span>
                        <span wire:loading wire:target="extractCoordinates">Extraction…</span>
                    </button>
                </div>
                @if($mapsLinkMessage)
                    @php [$mapsType, $mapsText] = explode(':', $mapsLinkMessage, 2); @endphp
                    <span class="small text-{{ $mapsType === 'success' ? 'success' : 'danger' }}">{{ $mapsText }}</span>
                @else
                    <span class="small text-muted">Sur Google Maps, faites un clic droit sur le point puis
                        « Copier les coordonnées », ou copiez l'adresse de la page.</span>
                @endif
            </div>
            <div class="col-md-6 mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" class="form-control" placeholder="Longitude" id="longitude" wire:model.live="longitude">
                @error('longitude') <span class="text-danger">{{ $message }}</span> @enderror
            </div>            
            <div class="col-md-6 mb-3">
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" class="form-control" placeholder="Latitude" id="latitude" wire:model.live="latitude">
                @error('latitude') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-12 mb-3" wire:ignore>
                <label class="form-label">Position sur la carte</label>
                <p class="text-muted small mb-2 mt-0">Recherchez une adresse, cliquez sur la carte, ou déplacez le marqueur pour définir la position du centre.</p>
                <input type="text" id="dc-map-search" class="form-control mb-2" placeholder="Rechercher une adresse, une ville, un lieu...">
                <div id="dc-map-picker" style="height: 380px; width: 100%; border-radius: 8px; background:#eef2f7;"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" wire:model.live="is_active">
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
                @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-12 mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" wire:model.live="description" rows="3" placeholder="Description du centre de distribution"></textarea>
                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-12">
                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                    <a href="{{ route('distribution-centers.list') }}" class="btn btn-light-danger">
                        <i class="ti ti-x"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        initializeSelect2();
        
        Livewire.hook('morph.updated', ({ el }) => {
            initializeSelect2();
        });
    });
    
    function initializeSelect2() {
        $('.searchable').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            $(this).on('change', function (e) {
                const elementId = e.target.id;
                const value = $(this).val();
                @this.set(elementId, value);
            });
        });
    }

    let dcMap = null;
    let dcMarker = null;

    window.initDistributionCenterMap = function () {
        const mapEl = document.getElementById('dc-map-picker');
        if (!mapEl || typeof google === 'undefined' || !google.maps) {
            return;
        }

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const initialLat = parseFloat(latInput?.value);
        const initialLng = parseFloat(lngInput?.value);
        const hasInitial = !isNaN(initialLat) && !isNaN(initialLng);

        // Default fallback: Cameroon (Yaoundé)
        const center = hasInitial
            ? { lat: initialLat, lng: initialLng }
            : { lat: 3.848, lng: 11.502 };

        dcMap = new google.maps.Map(mapEl, {
            zoom: hasInitial ? 15 : 6,
            center: center,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            streetViewControl: false,
            mapTypeControl: false,
        });

        dcMarker = new google.maps.Marker({
            map: dcMap,
            position: center,
            draggable: true,
            visible: hasInitial,
        });

        dcMap.addListener('click', function (e) {
            placeDcMarker(e.latLng);
        });

        dcMarker.addListener('dragend', function (e) {
            updateDcCoords(e.latLng);
        });

        const searchInput = document.getElementById('dc-map-search');
        if (searchInput && google.maps.places) {
            const ac = new google.maps.places.Autocomplete(searchInput, {
                fields: ['geometry', 'name', 'formatted_address'],
            });
            ac.bindTo('bounds', dcMap);
            ac.addListener('place_changed', function () {
                const place = ac.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }
                if (place.geometry.viewport) {
                    dcMap.fitBounds(place.geometry.viewport);
                } else {
                    dcMap.setCenter(place.geometry.location);
                    dcMap.setZoom(15);
                }
                placeDcMarker(place.geometry.location);
            });
        }
    };

    function placeDcMarker(latLng) {
        if (!dcMarker) return;
        dcMarker.setPosition(latLng);
        dcMarker.setVisible(true);
        updateDcCoords(latLng);
    }

    function updateDcCoords(latLng) {
        const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
        const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
        @this.set('latitude', lat.toFixed(6));
        @this.set('longitude', lng.toFixed(6));
    }

    /* Keep the map in sync when coordinates come from a pasted Maps link. */
    document.addEventListener('livewire:init', function () {
        Livewire.on('coordinates-extracted', function (payload) {
            const data = Array.isArray(payload) ? payload[0] : payload;
            if (!data || !dcMap || !dcMarker) return;
            const position = { lat: Number(data.lat), lng: Number(data.lng) };
            dcMarker.setPosition(position);
            dcMarker.setVisible(true);
            dcMap.setCenter(position);
            dcMap.setZoom(16);
        });
    });
</script>
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.api_key') }}&libraries=places&callback=initDistributionCenterMap&v=weekly"
    async defer></script>
@endpush