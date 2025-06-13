<div>
    <form class="row app-form g-3" wire:submit.prevent="submit" enctype="multipart/form-data">
        @csrf
        <!-- Informations de base -->
        <div class="col-md-12">
            <h5 class="mb-3">Informations de base</h5>
        </div>

        <div class="col-md-8">
            <label for="product_name" class="form-label">Nom du type d'accessoire</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                id="product_name" wire:model="name" placeholder="Entrez le nom du type d'accessoire">
            @error('name') <span class="error text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-4">
            <label for="product_price" class="form-label">Prix</label>
            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                id="product_price" wire:model="price" placeholder="Prix">
            @error('price') <span class="error text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-12">
            <label for="description" class="form-label">Description du type d'accessoire</label>
            <textarea class="form-control @error('description') is-invalid @enderror" 
                id="description" wire:model="description" rows="3"
                placeholder="Entrez la description du type d'accessoire"></textarea>
            @error('description') <span class="error text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                <label class="form-check-label" for="is_active">Actif</label>
            </div>
            @error('is_active') <span class="error text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-12">
            <label for="product_images" class="form-label">Images du type d'accessoire</label>
            <input type="file" class="form-control @error('images') is-invalid @enderror" 
                id="product_images" wire:model="images" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
            <small class="text-muted">Vous pouvez sélectionner plusieurs images.</small>
            @error('images') <span class="error text-danger">{{ $message }}</span> @enderror
            
            <div wire:loading wire:target="images">Chargement en cours...</div>
            
            @if($images)
                <div class="row mt-2">
                    @foreach($images as $image)
                    <div class="col-md-3 mb-2">
                        <img src="{{ $image->temporaryUrl() }}" class="img-fluid rounded" alt="Preview">
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Affichage des images existantes en mode édition -->
        @if(!empty($existingImages))
            <div class="col-md-12 mt-4">
                <h5>Images existantes</h5>
                <div class="row">
                    @foreach($existingImages as $image)
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <img src="{{ $image['url'] }}" class="card-img-top" alt="{{ $image['name'] }}">
                            <div class="card-body">
                                <h6 class="card-title">{{ $image['name'] }}</h6>
                                <button type="button" class="btn btn-sm btn-danger" 
                                    wire:click="removeImage({{ $image['id'] }})"
                                    wire:loading.attr="disabled">
                                    <i class="ti ti-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Boutons de soumission -->
        <div class="col-12 mt-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('accessories.index') }}" class="btn btn-danger">
                    <i class="ti ti-x"></i> Annuler
                </a>
                <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                    <i class="ti ti-device-floppy"></i> 
                     Enregistrer le produit
                    <span wire:loading wire:target="submit">...</span>
                </button>
            </div>
        </div>
    </form>
</div>
