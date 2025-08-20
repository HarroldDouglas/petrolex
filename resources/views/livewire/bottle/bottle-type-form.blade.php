<div>
    <form class="row app-form g-3" wire:submit.prevent="save">
        @csrf
        
        <div class="col-md-6">
            <label for="weight" class="form-label required">Poids (En Kg)</label>
            <input type="number" step="any" class="form-control @error('weight') is-invalid @enderror" 
            id="weight" placeholder="Ex: 6" wire:model.live.debounce.500ms="weight">
            @error('weight')
            <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="capacity" class="form-label required">Capacité (en Litre)</label>
            <input type="number" step="any" class="form-control @error('capacity') is-invalid @enderror" 
            id="capacity" placeholder="Ex: 50" wire:model.live.debounce.500ms="capacity">
            @error('capacity')
            <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <label for="name" class="form-label required">Nom du type de bouteille à ajouter</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
            id="name" placeholder="Ex: Bouteille 6kg" wire:model.live.debounce.500ms="name" readonly>
            @error('name')
            <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="content_price" class="form-label required">Prix de la recharge</label>
            <input type="number" class="form-control @error('content_price') is-invalid @enderror" 
                id="content_price" placeholder="Ex: 8500"
                wire:model.live.debounce.500ms="content_price">
            @error('content_price')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="bottle_with_content_price" class="form-label required">Prix de la consigne + recharge</label>
            <input type="number" class="form-control @error('bottle_with_content_price') is-invalid @enderror" 
                id="bottle_with_content_price" placeholder="Ex: 25000"
                wire:model.live.debounce.500ms="bottle_with_content_price">
            @error('bottle_with_content_price')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>

        <div class="col-md-12">
            <label for="product_images" class="form-label">Images du produit</label>
            <input type="file" class="form-control @error('product_images') is-invalid @enderror" id="product_images"
                wire:model="product_images" multiple accept="image/*">
            @error('product_images')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
            
            <!-- Affichage des aperçus des fichiers sélectionnés -->
            @if ($product_images)
                <div class="mt-2">
                    <p class="text-sm text-gray-500 mb-2">Aperçu des nouvelles images:</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product_images as $image)
                            <div class="position-relative">
                                <img src="{{ $image->temporaryUrl() }}" alt="Aperçu" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Affichage des images existantes (en mode édition) -->
            @if (!empty($existingImages))
                <div class="mt-3">
                    <h6 class="mb-2">Images actuelles:</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($existingImages as $image)
                            @if (!in_array($image['id'], $imagesIdsToDelete))
                                <div class="position-relative">
                                    <img src="{{ $image['original_url'] }}" alt="{{ $image['name'] }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-12">
            <label for="description" class="form-label">Description du produit</label>
            <textarea class="form-control @error('description') is-invalid @enderror"
             id="description" rows="3" placeholder="Entrez la description ici" 
                wire:model.live.debounce.500ms="description"></textarea>
        </div>
        
        <!-- City-Specific Pricing Section -->
        <div class="col-md-12 mt-4">
            <h5 class="mb-3">Prix spécifiques par ville</h5>
            
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="row mb-3 align-items-end">
                {{-- Masquer le champ Pays --}}
                <div class="col-md-2" style="display: none;">
                    <label for="selectedCountryId" class="form-label">Pays</label>
                    <select class="form-select" id="selectedCountryId" wire:model.live="selectedCountryId">
                        @foreach($availableCountries as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"> {{-- Ajusté de col-md-4 à col-md-3 --}}
                    <label for="selectedCityId" class="form-label">Ville</label>
                    <select class="form-select" id="selectedCityId" wire:model.live="selectedCityId" wire:key="{{ $selectedCountryId }}">
                        <option value="">Sélectionner une ville</option>
                        @foreach($availableCities as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"> {{-- Ajusté de col-md-3 à col-md-3 --}}
                    <label for="tempCityContentPrice" class="form-label">Prix de la recharge</label>
                    <input type="number" class="form-control" id="tempCityContentPrice" 
                           placeholder="Ex: 8500" wire:model.live="tempCityContentPrice">
                </div>
                <div class="col-md-3"> {{-- Ajusté de col-md-3 à col-md-3 --}}
                    <label for="tempCityContentWithBottlePrice" class="form-label">Prix consigne + recharge</label>
                    <input type="number" class="form-control" id="tempCityContentWithBottlePrice" 
                           placeholder="Ex: 25000" wire:model.live="tempCityContentWithBottlePrice">
                </div>
                <div class="col-md-3"> {{-- Ajusté de col-md-2 à col-md-3 pour compenser --}}
                    <button type="button" class="btn btn-primary w-100" 
                           wire:click="addCityPrice"
                           @if($this->isCityPriceAddButtonDisabled()) disabled @endif>
                        <i class="ti ti-plus"></i> Ajouter
                    </button>
                </div>
            </div>
            
            @if(count($cityPrices) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                {{-- Masquer la colonne Pays --}}
                                <th style="display: none;">Pays</th>
                                <th>Ville</th>
                                <th>Prix de la recharge</th>
                                <th>Prix de la consigne + recharge</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cityPrices as $index => $cityPrice)
                                <tr>
                                    {{-- Masquer la colonne Pays --}}
                                    <td style="display: none;">{{ $cityPrice['country_name'] ?? '' }}</td>
                                    <td>{{ $cityPrice['city_name'] ?? '' }}</td>
                                    <td>
                                        <input type="number" class="form-control @error('cityPrices.'.$index.'.content_price') is-invalid @enderror" 
                                            wire:model.live.debounce.500ms="cityPrices.{{ $index }}.content_price">
                                        @error('cityPrices.'.$index.'.content_price')
                                            <div class="invalid-feedback">{{ __($message) }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" class="form-control @error('cityPrices.'.$index.'.content_with_bottle_price') is-invalid @enderror"
                                            wire:model.live.debounce.500ms="cityPrices.{{ $index }}.content_with_bottle_price">
                                        @error('cityPrices.'.$index.'.content_with_bottle_price')
                                            <div class="invalid-feedback">{{ __($message) }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                            wire:click="removeCityPrice({{ $index }})">
                                            <i class="ti ti-trash"></i> Supprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Aucun prix spécifique par ville n'a été défini.
                </div>
            @endif
        </div>
        
        <div class="col-md-12 mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('bottles.types.index') }}" class="btn btn-danger">
                <i class="ti ti-x"></i> Annuler
            </a>
            <button type="submit" class="btn btn-success">
                <i class="ti ti-device-floppy"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
