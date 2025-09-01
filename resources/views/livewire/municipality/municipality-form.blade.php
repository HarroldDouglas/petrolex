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
                <select class="form-select @error('cityId') is-invalid @enderror" id="cityId" wire:model.defer="cityId">
                    <option value="">Sélectionner une ville</option>
                    @foreach($cities as $city)
                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                    @endforeach
                </select>
                @error('cityId') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label" for="selectedNeighborhoods">Quartiers</label>
                <select class="form-select @error('selectedNeighborhoods') is-invalid @enderror" id="selectedNeighborhoods" multiple wire:model.defer="selectedNeighborhoods">
                    @foreach($neighborhoods as $neighborhood)
                        <option value="{{ $neighborhood['id'] }}">{{ $neighborhood['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedNeighborhoods') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <button class="btn btn-primary" type="submit">{{ $municipality ? 'Mettre à jour' : 'Créer' }}</button>
            </div>
        </div>
    </form>
</div>