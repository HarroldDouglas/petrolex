<div class="card bg-white text-black">
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="distribution_center_filter" class="form-label">Centre de distribution :</label>
                    <select wire:model.live="distributionCenterId" class="form-select">
                        <option value="">Tous</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}">{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Période :</label>
                    <select wire:model.live="selectedPeriod" class="form-select">
                        @foreach(\App\Enums\PeriodFilterStats::cases() as $period)
                            <option value="{{ $period->value }}">{{ $period->label }}</option>
                        @endforeach
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