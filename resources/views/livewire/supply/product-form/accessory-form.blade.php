<div>
    <div class="border rounded p-3">
        <form wire:submit="save">
            <div class="row g-3">
                <div class="col-12 col-md-5">
                    <label class="form-label">Type d'accessoire *</label>
                    <select wire:model.live="selectedAccessoryType"
                        class="form-select @error('selectedAccessoryType') is-invalid @enderror">
                        <option value="">Sélectionner un type</option>
                        @foreach ($accessoryTypes as $accessoryType)
                            @if (
                                !in_array($accessoryType['id'], $usedAccessoryTypes ?? []) ||
                                    ($isEditing && $selectedAccessoryType == $accessoryType['id']))
                                <option value="{{ $accessoryType['id'] }}"
                                    {{ $selectedAccessoryType == $accessoryType['id'] ? 'selected' : '' }}>
                                    {{ ucfirst($accessoryType['name']) }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    @error('selectedAccessoryType')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label">Quantité *</label>
                    <input type="number" wire:model="accessoryQuantity"
                        class="form-control @error('accessoryQuantity') is-invalid @enderror" placeholder="Ex: 10">
                    @error('accessoryQuantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-6 col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="ti ti-device-floppy me-1"></i>Enregistrer
                        </span>
                        <span wire:loading>
                            <i class="ti ti-loader-2 me-1"></i>Enregistrement...
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
