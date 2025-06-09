<div class="app-product-section">
    <form wire:submit.prevent="submit" class="app-form">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nom</label>
                <input type="text" class="form-control" placeholder="Nom" id="name" wire:model.live="name">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="country" class="form-label">Pays</label>
                <select class="form-select" id="country" wire:model="country" disabled>
                    @foreach($countries as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('country') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="city" class="form-label">Ville</label>
                <select class="form-select select2 searchable" id="city" wire:model.live="city" data-placeholder="Rechercher une ville">
                    <option value="">Sélectionnez une ville</option>
                    @foreach($cities as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('city') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="neighborhood" class="form-label">Quartier</label>
                <select class="form-select select2 searchable" id="neighborhood" wire:model.live="neighborhood" data-placeholder="Rechercher un quartier">
                    <option value="">Sélectionnez un quartier</option>
                    @foreach($neighborhoods as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('neighborhood') <span class="text-danger">{{ $message }}</span> @enderror
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
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" class="form-control" placeholder="Latitude" id="latitude" wire:model.live="latitude">
                @error('latitude') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" class="form-control" placeholder="Longitude" id="longitude" wire:model.live="longitude">
                @error('longitude') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                <input type="number" class="form-control" placeholder="Capacité de stockage" id="storage_capacity" wire:model.live="storage_capacity">
                @error('storage_capacity') <span class="text-danger">{{ $message }}</span> @enderror
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
                    <button type="button" class="btn btn-light-danger">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
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
