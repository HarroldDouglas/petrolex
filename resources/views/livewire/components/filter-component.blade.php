<div class="card bg-white text-black">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="warehouse_filter" class="form-label">Point de distribution :</label>
                    <select wire:model.live="warehouseId" class="form-select">
                        <option value="">Tous</option>
                        @foreach($warehouses ?? [] as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Période :</label>
                    <select wire:model.live="selectedPeriod" wire:change="$refresh" class="form-select">
                        <option value="">Sélectionner une période</option>
                        <option value="1week">Il y a une semaine</option>
                        <option value="2weeks">Il y a deux semaines</option>
                        <option value="1month">Il y a 1 mois</option>
                        <option value="2months">Il y a 2 mois</option>
                        <option value="3months">Il y a 3 mois</option>
                        <option value="custom">Date personnalisée</option>
                    </select>
                </div>
            </div>
        </div>

        @if($showCustomDate)
        <div class="row mt-3" wire:transition.fade.duration.100ms>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Date début :</label>
                    <input type="date" wire:model.live="startDate" class="form-control">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Date fin :</label>
                    <input type="date" wire:model.live="endDate" class="form-control">
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
