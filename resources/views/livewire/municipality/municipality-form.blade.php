<div>
    <form wire:submit.prevent="saveMunicipality">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nom de la Municipalité</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" wire:model.defer="name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="cityId">Ville</label>
                <select class="form-select @error('cityId') is-invalid @enderror" id="cityId" wire:model.live="cityId">
                    <option value="">Sélectionner une ville</option>
                    @foreach($cities as $city)
                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                    @endforeach
                </select>
                @error('cityId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label" for="selectedNeighborhoods">Quartiers</label>
               
                {{-- TODO: use multi select component --}}
                <select class="form-select @error('selectedNeighborhoods') is-invalid @enderror" 
                        id="selectedNeighborhoods" 
                        multiple 
                        wire:model.defer="selectedNeighborhoods"
                        {{ empty($neighborhoods) ? 'disabled' : '' }}>
                    @forelse($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood['id'] }}">{{ $neighborhood['name'] }}</option>
                    @empty
                        <option disabled>Sélectionnez d'abord une ville</option>
                    @endforelse
                </select>
                @if(!empty($neighborhoods))
                    <div class="form-text text-success">{{ count($neighborhoods) }} quartier(s) disponible(s) pour cette ville.</div>
                @else
                    <div class="form-text text-muted">Sélectionnez une ville pour voir les quartiers disponibles.</div>
                @endif
                
                {{-- Debug information --}}
                <div class="form-text text-info">
                    Debug: cityId={{ $cityId ?? 'null' }}, neighborhoods count={{ count($neighborhoods ?? []) }}, 
                    disabled={{ empty($neighborhoods) ? 'true' : 'false' }}
                </div>
                
                @error('selectedNeighborhoods') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">{{ $municipality ? 'Mettre à jour' : 'Créer' }}</button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('neighborhoodsUpdated', (event) => {
        console.log('Neighborhoods updated:', event);
        const select = document.getElementById('selectedNeighborhoods');
        if (select) {
            if (event.count > 0) {
                select.disabled = false;
                console.log('Select enabled - neighborhoods available:', event.count);
            } else {
                select.disabled = true;
                console.log('Select disabled - no neighborhoods');
            }
        }
    });
});
</script>