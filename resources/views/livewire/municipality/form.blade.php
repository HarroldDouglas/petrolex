<div>
    <form wire:submit.prevent="submit">
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
                
                <div>
                    <livewire:multiple-select
                        :options="$this->availableNeighborhoods"
                        :parent-event="'neighborhoods:selection-changed'"
                        :selected-options="$selectedNeighborhoods ?? []"
                        :wire:key="'neighborhoods-select-' . ($cityId ?? 'no-city')"
                    />
                </div>
                
                @if(!empty($neighborhoods))
                    <div class="form-text text-success">{{ count($neighborhoods) }} quartier(s) disponible(s) pour cette ville.</div>
                @else
                    <div class="form-text text-muted">Sélectionnez une ville pour voir les quartiers disponibles.</div>
                @endif
                
                @error('selectedNeighborhoods') 
                    <span class="text-danger text-sm d-block">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-12">
                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                    <a href="{{ route('municipalities.index') }}" class="btn btn-light-danger">
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