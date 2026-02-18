<div>
    <div class="border rounded p-3">
        <form wire:submit="save">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Type de bouteille *</label>
                    <select wire:model.live="selectedBottleType"
                        class="form-select @error('selectedBottleType') is-invalid @enderror">
                        <option value="">Sélectionner un type</option>
                        @foreach ($bottleTypes as $bottleType)
                            @if (!in_array($bottleType['id'], $usedBottleTypes ?? []) || ($isEditing && $selectedBottleType == $bottleType['id']))
                                <option value="{{ $bottleType['id'] }}"
                                    {{ $selectedBottleType == $bottleType['id'] ? 'selected' : '' }}>
                                    {{ ucfirst($bottleType['name']) }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    @error('selectedBottleType')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label">Quantité entrante *</label>
                    <input type="number" wire:model="incomingQuantity"
                        class="form-control @error('incomingQuantity') is-invalid @enderror" placeholder="Ex: 50">
                    @error('incomingQuantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label">Quantité sortante</label>
                    <input type="number" wire:model="outgoingQuantity"
                        class="form-control @error('outgoingQuantity') is-invalid @enderror" placeholder="Ex: 10">
                    @error('outgoingQuantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="ti ti-device-floppy"></i> Enregistrer
                        </span>
                        <span wire:loading>
                            <i class="ti ti-loader-2 me-1"></i>Enregistrement ...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
