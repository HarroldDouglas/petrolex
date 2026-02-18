<div>
    @if($supplierDelivery->canBeEdited())
        <div class="mb-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <h5 class="mb-0">Enregistrement des produits</h5>
                <select wire:model.live="selectedFormType" class="form-select w-auto" style="min-width: 250px;">
                    <option value="{{ \App\Enums\ProductType::BOTTLE()->value }}">Enregistrer les bouteilles</option>
                    <option value="{{ \App\Enums\ProductType::ACCESSORY()->value }}">Enregistrer les accessoires</option>
                </select>
            </div>

            @if ($selectedFormType === \App\Enums\ProductType::BOTTLE()->value)
                <livewire:supply.product-form.gas-bottle-form
                    :supplierDelivery="$supplierDelivery"
                    :editProductId="$editProductId"
                    :editProductData="$editProductData" />
            @else
                <livewire:supply.product-form.accessory-form
                    :supplierDelivery="$supplierDelivery"
                    :editProductId="$editProductId"
                    :editProductData="$editProductData" />
            @endif
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <livewire:supply.products-list :supplierDelivery="$supplierDelivery" />
</div>
