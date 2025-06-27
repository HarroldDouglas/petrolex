<div>
    <!-- Product Registration Card -->
    <div class="card bg-white text-black p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Enregistrer les produits</h5>
            <div>
                <button wire:click="toggleGasForm" class="btn {{ $showGasForm ? 'btn-primary' : 'btn-dark' }} b-r-22">
                    <i class="iconoir-cylinder me-1"></i>Enregistrer les bouteilles de gaz
                </button>
                <button wire:click="toggleAccessoryForm"
                    class="btn {{ $showAccessoryForm ? 'btn-primary' : 'btn-dark' }} b-r-22">
                    <i class="ti ti-tool me-1"></i>Enregistrer un accessoire
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
        @endif

        <!-- Gas Bottle Registration Form -->
        @if ($showGasForm)
            <div class="p-3 rounded mb-3">
                <form wire:submit.prevent="registerGasBottle" class="row align-items-end">
                    <div class="col-md-3">
                        <label for="bottleType" class="form-label">Type de bouteille</label>
                        <select class="form-select" id="bottleType" wire:model="selectedBottleType">
                            <option value="">Sélectionner un type</option>
                            @foreach ($bottleTypes as $type)
                                @if (!in_array($type['id'], $usedBottleTypes ?? []) || ($isEditing && $selectedBottleType == $type['id']))
                                    <option value="{{ $type['id'] }}"
                                        {{ $selectedBottleType == $type['id'] ? 'selected' : '' }}>
                                        {{ $type['name'] }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('selectedBottleType')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="quantity" class="form-label">Quantité entrante</label>
                        <input type="number" class="form-control" id="quantity" wire:model="quantity">
                        @error('quantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="outgoingQuantity" class="form-label">Quantité sortante</label>
                        <input type="number" class="form-control" id="outgoingQuantity" wire:model="outgoingQuantity"
                            max="{{ $quantity }}">
                        @error('outgoingQuantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-device-floppy me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Accessory Registration Form -->
        @if ($showAccessoryForm)
            <div class="p-3 rounded mb-3">
                <form wire:submit.prevent="registerAccessory" class="row align-items-end">
                    <div class="col-md-5">
                        <label for="accessoryName" class="form-label">Type d'accessoire</label>
                        <select class="form-select" id="accessoryName" wire:model="accessoryName">
                            <option value="">Sélectionner un type</option>
                            @foreach ($accessoryTypes as $type)
                                @if (!in_array($type, $usedAccessoryTypes ?? []) || ($isEditing && $accessoryName == $type))
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('accessoryName')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="accessoryQuantity" class="form-label">Quantité</label>
                        <input type="number" class="form-control" id="accessoryQuantity"
                            wire:model="accessoryQuantity">
                        @error('accessoryQuantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-device-floppy me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Products Table -->
        @if (count($products ?? []) > 0)
            <div class="table-responsive mt-3">
                <table class="table table-bordered">
                    <thead class="bg-primary">
                        <tr>
                            <th>Type</th>
                            <th>Détail</th>
                            <th>Quantité entrante</th>
                            <th>Quantité sortante</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product['type'] }}</td>
                                <td>{{ $product['detail'] }}</td>
                                <td>{{ $product['quantity'] }}</td>
                                <td>{{ $product['outgoingQuantity'] ?? 0 }}</td>
                                <td>
                                    <div class="btn-group dropdown-icon-none">
                                        <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                            type="button" id="dropdownMenuButton-{{ $product['id'] }}"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu"
                                            aria-labelledby="dropdownMenuButton-{{ $product['id'] }}">
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="editProduct({{ $product['id'] }})">
                                                    <i class="ti ti-edit text-success me-2"></i> Éditer
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="confirmDeleteProduct({{ $product['id'] }})">
                                                    <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                </a>
                                            </li>
                                            @if ($product['type'] === 'Bouteilles')
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        wire:click.prevent="scanBottles({{ $product['id'] }})">
                                                        <i class="ti ti-scan text-info me-2"></i> Scanner les bouteilles
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mt-3">
                Aucun produit enregistré pour cet approvisionnement.
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce produit? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteProduct">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Pour Livewire v3
                window.addEventListener('showDeleteModal', event => {
                    var myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    myModal.show();
                });

                window.addEventListener('hideDeleteModal', event => {
                    var myModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                    if (myModal) {
                        myModal.hide();
                    }
                });

                // Pour la compatibilité avec les deux versions de Livewire
                Livewire.on('showDeleteModal', () => {
                    var myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                    myModal.show();
                });

                Livewire.on('hideDeleteModal', () => {
                    var myModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                    if (myModal) {
                        myModal.hide();
                    }
                });
            });
        </script>
    @endpush
</div>
