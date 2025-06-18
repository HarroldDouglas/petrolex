<div>
    @if (count($products) > 0)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="ti ti-list me-2"></i>
                    Produits enregistrés ({{ count($products) }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Type</th>
                                <th>Produit</th>
                                <th style="width: 120px;">Qté entrante</th>
                                <th style="width: 120px;">Qté sortante</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <span class="badge {{ $product['type'] === 'bottle' ? 'bg-primary' : 'bg-secondary' }}">
                                            <i class="{{ $product['type'] === 'bottle' ? 'iconoir-cylinder' : 'ti ti-tool' }} me-1"></i>
                                            {{ $product['type'] === 'bottle' ? 'Bouteille' : 'Accessoire' }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $product['detail_name'] ?? $product['detail'] }}</strong>
                                    </td>
                                    <td>
                                        @if($product['type'] === 'bottle')
                                            <span class="badge bg-success fs-6">{{ $product['incomingQuantity'] ?? 0 }}</span>
                                        @else
                                            <span class="badge bg-success fs-6">{{ $product['quantity'] ?? 0 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product['type'] === 'bottle')
                                            <span class="badge bg-warning fs-6">{{ $product['outgoingQuantity'] ?? 0 }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button wire:click="editProduct({{ $product['id'] }})" 
                                                    class="btn btn-outline-primary"
                                                    title="Modifier">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button wire:click="confirmDeleteProduct({{ $product['id'] }})" 
                                                    class="btn btn-outline-danger"
                                                    title="Supprimer">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                            @if($product['type'] === 'bottle')
                                                <button wire:click="scanBottles({{ $product['id'] }})" 
                                                        class="btn btn-outline-info"
                                                        title="Scanner les bouteilles">
                                                    <i class="ti ti-scan"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
