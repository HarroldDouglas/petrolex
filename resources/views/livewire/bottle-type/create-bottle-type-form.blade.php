<div>
    <form wire:submit.prevent="save" class="row app-form g-3">
        <div class="col-md-8">
            <label for="name" class="form-label">Nom du type de bouteille à ajouter 
                <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" 
                wire:model.live.debounce.500ms="name" placeholder="Ex: Bouteille 6kg">
            @error('name')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="capacity" class="form-label">Capacité <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" 
            wire:model.live.debounce.500ms="capacity" placeholder="Ex: 14">
            @error('capacity')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="bottle_price" class="form-label">Prix de la bouteille vide 
                <span class="text-danger">*</span>
            </label>
            <input type="number" class="form-control @error('bottle_price') is-invalid @enderror" 
                id="bottle_price" wire:model.live.debounce.500ms="bottle_price" placeholder="Ex: 15000">
            @error('bottle_price')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="content_price" class="form-label">Prix de la contenance 
                <span class="text-danger">*</span>
            </label>
            <input type="number" class="form-control @error('content_price') is-invalid @enderror" 
            id="content_price" wire:model.live.debounce.500ms="content_price" placeholder="Ex: 5000">
            @error('content_price')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="bottle_with_content_price" class="form-label">Prix de la bouteille pleine 
                <span class="text-danger">*</span>
            </label>
            <input type="number" class="form-control @error('bottle_with_content_price') is-invalid @enderror" 
                id="bottle_with_content_price" wire:model.live.debounce.500ms="bottle_with_content_price" 
                placeholder="Ex: 20000">
            @error('bottle_with_content_price')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-12">
            <label for="description" class="form-label">Description du type de bouteille</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                wire:model="description" rows="3" placeholder="Entrez la description ici">
            </textarea>
        </div>
        <div class="col-md-4">
            <label for="height" class="form-label">Hauteur</label>
            <input type="number" class="form-control @error('height') is-invalid @enderror" id="height" wire:model="height" placeholder="Ex: 30">
            @error('height')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="width" class="form-label">Largeur</label>
            <input type="number" class="form-control @error('width') is-invalid @enderror" id="width" wire:model="width" placeholder="Ex: 30">
            @error('width')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label for="radius" class="form-label">Rayon</label>
            <input type="number" class="form-control @error('radius') is-invalid @enderror" id="radius" wire:model="radius" placeholder="Ex: 15">
            @error('radius')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-8">
            <label for="is_active" class="form-label">Statut <span class="text-danger">*</span></label>
            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" wire:model="is_active">
                <option value="1" selected>Actif</option>
                <option value="0">Inactif</option>
            </select>
            @error('is_active')
                <div class="invalid-feedback">{{ __($message) }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ti ti-device-floppy"></i> créer
            </button>
        </div>
    </form>
</div>
