<div>
    <div class="card bg-white text-black p-4 mb-4">
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Enregistrement des produits</h5>
                <select wire:model.live="selectedFormType" class="form-select" style="width: 300px;">
                    <option value="{{ \App\Enums\ProductType::BOTTLE()->value }}">🛢️ Enregistrer les bouteilles</option>
                    <option value="{{ \App\Enums\ProductType::ACCESSORY()->value }}">🔧 Enregistrer les accessoires</option>
                </select>
            </div>
            
            <div class="w-100">
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
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <livewire:supply.products-list :supplierDelivery="$supplierDelivery" />
    </div>
</div>
