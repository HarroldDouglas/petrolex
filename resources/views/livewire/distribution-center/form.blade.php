<div class="app-product-section">
    <form wire:submit.prevent="submit" class="app-form">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" placeholder="Nom" id="name" wire:model.live="name">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="countryId" class="form-label">Pays</label>
                <select class="form-select" id="countryId" wire:model.live="countryId">
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('countryId') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="cityId" class="form-label">Ville</label>
                <div wire:loading.remove wire:target="countryId">
                    <select class="form-select select2 searchable" id="cityId" wire:model.live="cityId" data-placeholder="Rechercher une ville">
                        <option value="">Sélectionnez une ville</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:loading wire:target="countryId">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> Chargement des villes...
                </div>
                @error('cityId') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="neighborhoodId" class="form-label">Quartier</label>
                <div wire:loading.remove wire:target="cityId">
                    <select class="form-select select2 searchable" id="neighborhoodId" wire:model.live="neighborhoodId" data-placeholder="Rechercher un quartier">
                        <option value="">Sélectionnez un quartier</option>
                        @foreach($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood->id }}">{{ $neighborhood->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div wire:loading wire:target="cityId">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> Chargement des quartiers...
                </div>
                @error('neighborhoodId') <span class="text-danger">{{ $message }}</span> @enderror
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
</script>
@endpush
