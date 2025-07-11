<div class="app-order-section">
    <form wire:submit.prevent="submit" class="app-form">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="customer" class="form-label">Client</label>
                <select class="form-select select2 searchable" id="customer" wire:model.live="customer" data-placeholder="Rechercher un client">
                    <option value="">Sélectionnez un client</option>
                   @foreach ($customers as $key => $customer)
                       <option value="{{ $customer['id'] }}">{{ $customer['full_name'] }}</option>
                   @endforeach
                </select>
                @error('customer') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="distribution_center" class="form-label">Centre de distribution</label>
                <select class="form-select select2 searchable" id="distribution_center" wire:model.live="distribution_center" data-placeholder="Rechercher un centre de distribution">
                    <option value="">Sélectionnez un centre de distribution</option>
                   @foreach ($distributionCenters as $key => $center)
                       <option value="{{ $center['id'] }}">{{ $center['name'] }}</option>
                   @endforeach
                </select>
                @error('distribution_center') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="customer_address" class="form-label">Adresse du client</label>
                <select class="form-select select2 searchable" id="customer_address" wire:model.live="delivery_address_id" data-placeholder="Rechercher une adresse">
                    <option value="">Sélectionnez une adresse</option>
                   @foreach ($customerAddresses as $key => $address)
                       <option value="{{ $address['id'] }}">{{ $address['full_address'] }}</option>
                   @endforeach
                </select>
                @error('customer_address') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="payment_method" class="form-label">Mode de paiement</label>
                <select class="form-select" id="payment_method" wire:model.live="payment_method">
                    @foreach ($paymentMethods as $key => $paymentMethod)
                        <option value="{{ $paymentMethod }}">{{ $paymentMethod }}</option>
                    @endforeach
                </select>
                @error('payment_method') 
                    <span class="text-danger">{{ $message }}</span> 
                @enderror
            </div>
            <div class="col-md-6">
                <label for="delivery_type" class="form-label">Type de livraison</label>
                <select class="form-select" id="delivery_type" wire:model.live="delivery_type">
                    <option value="">Sélectionnez le type de livraison</option>
                    @foreach(\App\Enums\DeliveryType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label }}</option>
                    @endforeach
                </select>
                @error('delivery_type') 
                    <span class="text-danger">{{ $message }}</span> 
                @enderror
            </div>
           
            <div class="col-md-12 mt-4">
            <h5 class="mb-3">Produits</h5>
            
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="selectedProduct" class="form-label">Nom du produit</label>
                    <select class="form-select" id="selectedProduct" wire:model.live="selectedProduct">
                        <option value="">Sélectionner un produit</option>
                        @foreach($availableProducts as $product)
                            @if(!in_array($product, array_column($productOptionPrices, 'product')))
                                <option value="{{ $product }}">{{ $product }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="selectedOption" class="form-label">Option</label>
                    <select class="form-select" id="selectedOption" wire:model.live="selectedOption">
                        <option value="">Sélectionner une option</option>
                        @foreach(\App\Enums\BottleOrderType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="productOptionQuantity" class="form-label">Quantité</label>
                    <input type="number" class="form-control" id="productOptionQuantity" 
                           placeholder="Ex: 10" wire:model.live="productOptionQuantity">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success w-100" 
                           wire:click="addProduct"
                           @disabled($this->isProductAddButtonDisabled())>
                        <i class="ti ti-plus"></i> Ajouter
                    </button>
                </div>
            </div>
            
            @if(count($productOptionPrices) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Option</th>
                                <th>Prix Unitaire</th>
                                <th>Quantité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productOptionPrices as $index => $productOptionPrice)
                                <tr>
                                    <td>{{ $productOptionPrice['name'] }}</td>
                                    <td>{{\App\Enums\BottleOrderType::from( $productOptionPrice['option'])->label}}</td>
                                    <td>{{ $productOptionPrice['price'] }}</td>
                                    <td>
                                        <input type="number" class="form-control @error('productOptionPrices.'.$index.'.quantity') is-invalid @enderror" 
                                            wire:model.live.debounce.500ms="productOptionPrices.{{ $index }}.quantity">
                                        @error('productOptionPrices.'.$index.'.quantity')
                                            <div class="invalid-feedback">{{ __($message) }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                            wire:click="removeProductOptionPrice({{ $index }})">
                                            <i class="ti ti-trash"></i> Supprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Aucun produit n'a été ajouté dans le panier.
                </div>
            @endif
        </div>
        
            <div class="col-12">
                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                    <a href="{{ route('orders.list') }}" class="btn btn-light-danger">
                        <i class="ti ti-x"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                        <i class="ti ti-device-floppy"></i> 
                        Enregistrer
                        <span wire:loading wire:target="submit">...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>

    document.addEventListener('livewire:initialized', () => {
        initializeSelect2();
        
        Livewire.hook('morph.updated', ({ el }) => {
            initializeSelect2();
        });
    });
    
    function initializeSelect2() {
        $('.searchable').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });
            
            $(this).on('change', function (e) {
                const elementId = e.target.id;
                const value = $(this).val();
                @this.set(elementId, value);
            });
        });
    }
</script>
@endpush
