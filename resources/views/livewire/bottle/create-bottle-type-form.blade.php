<div>
    <form class="row app-form g-3" wire:submit.prevent="save">
        <div class="col-md-12">
            <label for="type_name" class="form-label">Nom du type de bouteille à ajouter</label>
            <input type="text" class="form-control" id="type_name" placeholder="Ex: Bouteille 6kg"
                wire:model="type_name" required>
        </div>
        
        <div class="col-md-6">
            <label for="bottle_capacity_price" class="form-label">Prix de la recharge</label>
            <input type="number" class="form-control" id="bottle_capacity_price" placeholder="Ex: 8500"
                wire:model="bottle_capacity_price" required>
        </div>
        
        <div class="col-md-6">
            <label for="bottle_price" class="form-label">Prix de la consigne + recharge</label>
            <input type="number" class="form-control" id="bottle_price" placeholder="Ex: 25000"
                wire:model="bottle_price" required>
        </div>
        
        <div class="col-md-12">
            <label for="product_images" class="form-label">Images du produit</label>
            <input type="file" class="form-control" id="product_images"
                wire:model="product_images" required>
        </div>
        
        <div class="col-md-12">
            <label for="description" class="form-label">Description du produit</label>
            <textarea class="form-control" id="description" rows="3" placeholder="Entrez la description ici" 
                wire:model="description" required></textarea>
        </div>
        
        <!-- City-Specific Pricing Section -->
        <div class="col-md-12 mt-4">
            <h5 class="mb-3">Prix spécifiques par ville</h5>
            
            <div class="row mb-3">
                <div class="col-md-8">
                    <select class="form-select" wire:model="selectedCity">
                        <option value="">Sélectionner une ville</option>
                        @foreach($availableCities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary w-100" wire:click="addCityPrice">
                        <i class="ti ti-plus"></i> Ajouter un prix spécifique
                    </button>
                </div>
            </div>
            
            @if(count($cityPrices) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ville</th>
                                <th>Prix de la recharge</th>
                                <th>Prix de la consigne + recharge</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cityPrices as $index => $cityPrice)
                                <tr>
                                    <td>{{ $cityPrice['city'] }}</td>
                                    <td>
                                        <input type="number" class="form-control" 
                                            wire:model="cityPrices.{{ $index }}.refill_price" 
                                            placeholder="Prix de la recharge">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" 
                                            wire:model="cityPrices.{{ $index }}.full_price" 
                                            placeholder="Prix complet">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger" 
                                            wire:click="removeCityPrice({{ $index }})">
                                            <i class="ti ti-trash"></i> Supprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <div class="col-md-12 mt-4">
            <button type="submit" class="btn btn-success">
                <i class="ti ti-device-floppy"></i> Créer
            </button>
        </div>
    </form>
</div>
