<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Scan des codes-barres</h5>
                <span class="badge bg-primary">{{ $supplyTitle }} - {{ $supplyDate ? \Carbon\Carbon::parse($supplyDate)->format('d/m/Y H:i') : '17/06/2025 04:58' }}</span>
            </div>
        </div>

        <div class="card-body">
            <!-- Bottle Selection Row -->
            <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label">Choisissez les bouteilles à scanner:</label>
                <div class="col-sm-5">
                    <select class="form-select" wire:model.live="selectedProductId" wire:change="updateSelectedProduct">
                        <option value="">-- Sélectionnez un type de bouteille --</option>
                        @foreach($availableProducts as $product)
                            <option value="{{ $product['id'] }}">
                                {{ $product['bottle_type_name'] }} 
                                ({{ $product['quantity'] }} entrantes 
                                <span class="badge bg-{{ $product['incoming_done'] ? 'success' : 'warning' }}">{{ $product['incoming_done'] ? 'terminé' : 'non terminé' }}</span> 
                                - {{ $product['outgoing_quantity'] }} sortantes 
                                <span class="badge bg-{{ $product['outgoing_done'] ? 'success' : 'warning' }}">{{ $product['outgoing_done'] ? 'terminé' : 'non terminé' }}</span>)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <select class="form-select" wire:model.live="isIncomingMode" wire:change="changeMode({{ $isIncomingMode ? 'incoming' : 'outgoing' }})">
                        <option value="1">Scanner les {{ $quantity }} bouteilles entrantes</option>
                        <option value="0">Scanner les {{ $outgoingQuantity }} bouteilles sortantes</option>
                    </select>
                </div>
            </div>

            @if($productId && $bottleType)
                <!-- Bottle Scanning Info Row -->
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-8">
                        <h6 class="mb-0">
                            {{ $isIncomingMode ? 'Bouteilles entrantes' : 'Bouteilles sortantes' }} de {{ $bottleType }} 
                            ({{ $scanned }} scannés / {{ $isIncomingMode ? $quantity : $outgoingQuantity }})
                        </h6>
                    </div>
                    <div class="col-sm-4 text-end">
                        <button type="button" class="btn btn-primary" wire:click="toggleManualForm">
                            <i class="ti ti-barcode me-1"></i> Scanner
                        </button>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 10px;">
                    @php 
                        $max = $isIncomingMode ? $quantity : $outgoingQuantity;
                        $percentage = $max > 0 ? ($scanned / $max) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" 
                         aria-valuenow="{{ $scanned }}" aria-valuemin="0" aria-valuemax="{{ $max }}">
                    </div>
                </div>

                <!-- Manual Barcode Form -->
                @if($showManualForm)
                    <div class="card mb-4 border-primary">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="manualBarcode" class="form-label">Entrer un code-barres manuellement:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="manualBarcode" wire:model="manualBarcode" placeholder="Scannez ou entrez un code-barres">
                                    <button class="btn btn-primary" type="button" wire:click="addManualBarcode">Ajouter</button>
                                </div>
                                @error('manualBarcode') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Bottles Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="40px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:click="selectAll">
                                    </div>
                                </th>
                                <th width="80px">#</th>
                                <th>Code-barres</th>
                                <th width="180px">Date/Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bottles as $index => $bottle)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                wire:model.live="selectedBottles" 
                                                value="{{ $bottle['id'] }}">
                                        </div>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bottle['barcode'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bottle['timestamp'])->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Aucune bouteille scannée pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3">
                    <button type="button" class="btn btn-danger" 
                        {{ count($selectedBottles) ? '' : 'disabled' }}
                        wire:click="removeSelected" wire:confirm="Êtes-vous sûr de vouloir supprimer les bouteilles sélectionnées?">
                        <i class="ti ti-trash me-1"></i> Supprimer la sélection
                    </button>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Veuillez sélectionner un type de bouteille pour commencer le scan.
                </div>
            @endif
        </div>
    </div>

    @script
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const handleBarcodeInput = (e) => {
                if (e.keyCode === 13 && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    if (window.barcodeBuffer) {
                        @this.addBottle(window.barcodeBuffer);
                        window.barcodeBuffer = '';
                    }
                } else if (e.keyCode !== 13) {
                    // Only add to buffer if it's not Enter key
                    if (!window.barcodeBuffer) window.barcodeBuffer = '';
                    window.barcodeBuffer += e.key;
                }
            };

            document.addEventListener('keypress', handleBarcodeInput);
            
            // Clear buffer after 100ms of no input
            let bufferTimeout;
            document.addEventListener('keypress', function () {
                clearTimeout(bufferTimeout);
                bufferTimeout = setTimeout(() => { 
                    window.barcodeBuffer = '';
                }, 100);
            });
        });
    </script>
    @endscript
</div>
