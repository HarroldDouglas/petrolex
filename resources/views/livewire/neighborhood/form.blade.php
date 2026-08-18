<div>
    <form wire:submit.prevent="submit">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nom du Quartier</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" wire:model.defer="name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="is_active">Statut</label>
                <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" wire:model.defer="is_active">
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
                @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

            <div class="col-md-6">
                <label class="form-label" for="municipalityId">Municipalité</label>
                <select class="form-select @error('municipalityId') is-invalid @enderror" id="municipalityId" wire:model.defer="municipalityId">
                    <option value="">Sélectionner une municipalité</option>
                    @foreach($municipalities as $municipality)
                        <option value="{{ $municipality['id'] }}">{{ $municipality['name'] }}</option>
                    @endforeach
                </select>
                @error('municipalityId') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if(!empty($municipalities))
                    <div class="form-text text-success">{{ count($municipalities) }} municipalité(s) disponible(s) pour cette ville.</div>
                @else
                    <div class="form-text text-muted">Sélectionnez une ville pour voir les municipalités disponibles.</div>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label" for="mapsLink">Lien Google Maps <span class="text-muted">(optionnel)</span></label>
                <div class="input-group">
                    <input class="form-control" id="mapsLink" type="text"
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
                    <div class="form-text text-{{ $mapsType === 'success' ? 'success' : 'danger' }}">{{ $mapsText }}</div>
                @else
                    <div class="form-text text-muted">Sur Google Maps, faites un clic droit sur le point puis
                        « Copier les coordonnées », ou copiez simplement l'adresse de la page.</div>
                @endif
            </div>

            <div class="col-md-6">
                <label class="form-label" for="latitude">Latitude</label>
                <input class="form-control @error('latitude') is-invalid @enderror" id="latitude" type="text"
                    inputmode="decimal" placeholder="Ex : 4.0700000" wire:model.defer="latitude">
                @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="longitude">Longitude</label>
                <input class="form-control @error('longitude') is-invalid @enderror" id="longitude" type="text"
                    inputmode="decimal" placeholder="Ex : 9.6830000" wire:model.defer="longitude">
                @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text text-muted">Obligatoire : les applications mobiles ont besoin des coordonnées de
                    chaque quartier. Utilisez le champ « Lien Google Maps » ci-dessus pour les remplir automatiquement.</div>
            </div>

            {{-- Polygon OSM --}}
            <div class="col-12">
                <label class="form-label">Polygone géographique</label>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if($hasPolygon)
                        <span class="badge bg-success"><i class="ti ti-check"></i> Polygone défini</span>
                    @else
                        <span class="badge bg-warning text-dark"><i class="ti ti-alert-triangle"></i> Aucun polygone</span>
                    @endif

                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="fetchPolygonFromOsm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="fetchPolygonFromOsm"><i class="ti ti-map-search"></i> Récupérer depuis OpenStreetMap</span>
                        <span wire:loading wire:target="fetchPolygonFromOsm"><i class="ti ti-loader ti-spin"></i> Récupération en cours...</span>
                    </button>
                </div>

                @if($polygonMessage)
                    @php [$type, $msg] = explode(':', $polygonMessage, 2); @endphp
                    <div class="mt-2 alert alert-{{ $type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : 'danger') }} py-2">
                        {{ $msg }}
                    </div>
                @endif

                <div class="form-text text-muted">Le polygone délimite la zone géographique du quartier et sert à valider les coordonnées GPS des adresses de livraison.</div>
            </div>

            <div class="col-12">
                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                    <a href="{{ route('neighborhoods.index') }}" class="btn btn-light-danger">
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
