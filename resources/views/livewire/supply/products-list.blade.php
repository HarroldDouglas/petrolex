<div>
    @if (count($products) > 0)
        <div class="border rounded">
            <div class="px-3 py-2 border-bottom">
                <h6 class="mb-0">
                    <i class="ti ti-list me-2"></i>
                    Produits enregistrés ({{ count($products) }})
                </h6>
            </div>
            <div class="p-0 pb-3">
                {{-- Table desktop --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Type</th>
                                <th>Produit</th>
                                <th style="width: 120px;">Qté entrante</th>
                                <th style="width: 120px;">Qté sortante</th>
                                @if($supplierDelivery->canBeEdited())
                                <th style="width: 100px;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $productType = $this->getProductTypeDisplay($product);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge {{ $productType === 'bottle' ? 'bg-primary' : 'bg-secondary' }}">
                                            <i class="{{ $productType === 'bottle' ? 'iconoir-cylinder' : 'ti ti-tool' }} me-1"></i>
                                            {{ $productType === 'bottle' ? 'Bouteille' : 'Accessoire' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $this->getProductDisplayName($product) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">{{ $product['expected_quantity'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($productType === 'bottle')
                                            <span class="badge bg-warning fs-6">{{ $product['bottles_out_quantity'] ?? 0 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @if($supplierDelivery->canBeEdited())
                                    <td>
                                        <div class="btn-group dropdown-icon-none">
                                            <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
                                                id="dropdownMenuButton{{ $product['id'] }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $product['id'] }}">
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="editProduct({{ $product['id'] }})">
                                                        <i class="iconoir-edit text-info me-2"></i> Modifier
                                                    </a>
                                                </li>
                                                @if($productType === 'bottle')
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="scanBottles({{ $product['id'] }})">
                                                        <i class="ti ti-scan text-primary me-2"></i> Scanner les bouteilles
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="confirmDeleteProduct({{ $product['id'] }})">
                                                        <i class="iconoir-trash text-danger me-2"></i> Supprimer
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards mobile --}}
                <div class="d-md-none px-3 pt-3">
                    @foreach ($products as $product)
                        @php
                            $productType = $this->getProductTypeDisplay($product);
                        @endphp
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge {{ $productType === 'bottle' ? 'bg-primary' : 'bg-secondary' }} mb-1">
                                        <i class="{{ $productType === 'bottle' ? 'iconoir-cylinder' : 'ti ti-tool' }} me-1"></i>
                                        {{ $productType === 'bottle' ? 'Bouteille' : 'Accessoire' }}
                                    </span>
                                    <div class="fw-bold mt-1">{{ $this->getProductDisplayName($product) }}</div>
                                </div>
                                @if($supplierDelivery->canBeEdited())
                                <div class="btn-group dropdown-icon-none">
                                    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
                                        id="dropdownMenuButtonMobile{{ $product['id'] }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButtonMobile{{ $product['id'] }}">
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="editProduct({{ $product['id'] }})">
                                                <i class="iconoir-edit text-info me-2"></i> Modifier
                                            </a>
                                        </li>
                                        @if($productType === 'bottle')
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="scanBottles({{ $product['id'] }})">
                                                <i class="ti ti-scan text-primary me-2"></i> Scanner
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="confirmDeleteProduct({{ $product['id'] }})">
                                                <i class="iconoir-trash text-danger me-2"></i> Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-3">
                                <div>
                                    <small class="text-muted d-block">Qté entrante</small>
                                    <span class="badge bg-success fs-6">{{ $product['expected_quantity'] ?? 0 }}</span>
                                </div>
                                @if($productType === 'bottle')
                                <div>
                                    <small class="text-muted d-block">Qté sortante</small>
                                    <span class="badge bg-warning fs-6">{{ $product['bottles_out_quantity'] ?? 0 }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="ti ti-package-off" style="font-size: 3rem; color: #6c757d;"></i>
            </div>
            <h6 class="text-muted">Aucun produit enregistré</h6>
            <p class="text-muted small">Ajoutez des bouteilles de gaz ou des accessoires pour commencer</p>
        </div>
    @endif

    <!-- Modal de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Confirmer la suppression</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Êtes-vous sûr de vouloir supprimer ce produit ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button wire:click="deleteProduct" class="btn btn-danger">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('show-delete-modal', () => {
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
        
        $wire.on('hide-delete-modal', () => {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        });
    </script>
    @endscript
</div>
