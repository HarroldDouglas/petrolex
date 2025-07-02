<div>
    <div class="app-product-section">
        <div class="main-title">
            <h6>Informations de l'approvisionnement</h6>
        </div>
        
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form wire:submit="save" class="app-form">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="distribution_center_id" class="form-label">Centre de distribution</label>
                    <select class="form-select" id="distribution_center_id" wire:model="distribution_center_id">
                        <option value="">-- Sélectionner un centre --</option>
                        @foreach($distributionCenters as $center)
                            <option value="{{ $center['id'] }}">{{ $center['name'] }}</option>
                        @endforeach
                    </select>
                    @error('distribution_center_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="title" wire:model="title"
                        placeholder="Ex: Approvisionnement abc">
                    @error('title')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="supply_date" class="form-label">Date</label>
                    <input type="datetime-local" class="form-control" id="supply_date" wire:model="supply_date">
                    @error('supply_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-10">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" rows="6" wire:model="description"
                        placeholder="Ex: Description de l'approvisionnement"></textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-2 d-flex align-items-end mb-3">
                    @if ($supply->canBeEdited())
                        <button type="submit" class="btn btn-success w-100"><i class="ti ti-device-floppy"></i> Enregistrer</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>