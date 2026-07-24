<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>NB:</strong> L'approvisionnement ne sera considéré comme terminé uniquement lorsque toutes les bouteilles (entrantes et sortantes) auront été scannées.
                Tant que l'approvisionnement n'est pas terminé, aucune bouteille enregistrée ne sera active ou disponible et ne seront donc pas utilisées pour les commandes/livraison.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <h5>Scan des codes-barres</h5>
                <span class="badge bg-primary">{{ $supplyTitle }} -
                    {{ $supplyDate ? \Carbon\Carbon::parse($supplyDate)->format('d/m/Y H:i') : '17/06/2025 04:58' }}</span>
            </div>
        </div>

        <div class="card-body">
            <!-- Bottle Selection Row -->
            <div class="row mb-4 align-items-center">
                <label class="col-sm-3 col-form-label">Choisissez les bouteilles à scanner:</label>
                <div class="col-sm-5">
                    @if (count($availableProducts) > 0)
                        <select class="form-select" wire:model.live="selectedProductId"
                            wire:change="updateSelectedProduct">
                            @foreach ($availableProducts as $product)
                                <option value="{{ $product['id'] }}">
                                    {{ $product['bottle_type_name'] }}
                                    ({{ $product['quantity'] }} entrantes
                                    <span
                                        class="badge bg-{{ $product['incoming_done'] ? 'success' : 'warning' }}">{{ $product['incoming_done'] ? 'terminé' : 'non terminé' }}</span>
                                    - {{ $product['outgoing_quantity'] }} sortantes
                                    <span
                                        class="badge bg-{{ $product['outgoing_done'] ? 'success' : 'warning' }}">{{ $product['outgoing_done'] ? 'terminé' : 'non terminé' }}</span>)
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="ti ti-alert-circle me-2"></i>
                            Aucun type de bouteille disponible. Veuillez d'abord
                            <a href="{{ route('supplies.register-products', request()->route('supply_id')) }}"
                                class="alert-link">
                                enregistrer des produits
                            </a> pour cette livraison.
                        </div>
                    @endif
                </div>
                <div class="col-sm-4">
                    @if (count($availableProducts) > 0)
                        @if (count($availableProducts) > 0)
                            <select class="form-select" wire:model.live="isIncomingMode">
                                <option value="1">Scanner les {{ $quantity }} bouteilles entrantes</option>
                                <option value="0">Scanner les {{ $outgoingQuantity }} bouteilles sortantes
                                </option>
                            </select>
                        @endif
                    @endif
                </div>
            </div>

            @if ($productId && $bottleType)
                <!-- Bottle Scanning Info Row -->
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-8">
                        <h6 class="mb-0">
                            {{ $isIncomingMode ? 'Bouteilles entrantes' : 'Bouteilles sortantes' }} de
                            {{ $bottleType }}
                            ({{ $scanned }} scannés / {{ $isIncomingMode ? $quantity : $outgoingQuantity }})
                        </h6>
                    </div>
                    @if ($supply->canBeEdited())
                        <div class="col-sm-4 text-end">
                            <button type="button" class="btn btn-primary me-2" id="scanButton">
                                <i class="ti ti-scan me-1"></i>Scanner
                            </button>
                            <button type="button" class="btn btn-dark" wire:click="toggleManualForm">
                                <i class="ti ti-keyboard me-1"></i>Ajouter manuellement
                            </button>
                        </div>
                    @endif
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
                @if ($showManualForm)
                    <div class="card mb-4 border-primary">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="manualBarcode" class="form-label">Entrer un code-barres
                                    manuellement:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="manualBarcode"
                                        wire:model="manualBarcode" placeholder="Scannez ou entrez un code-barres">
                                    <button class="btn btn-primary" type="button"
                                        wire:click="addManualBarcode">Ajouter</button>
                                </div>
                                @error('manualBarcode')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
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
                                                wire:model.live="selectedBottles" value="{{ $bottle['id'] }}">
                                        </div>
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bottle['barcode'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bottle['timestamp'])->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Aucune bouteille scannée pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Action Buttons -->
                @if ($supply->canBeEdited())
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" {{ count($selectedBottles) ? '' : 'disabled' }}
                            wire:click="removeSelected"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer les bouteilles sélectionnées?">
                            <i class="ti ti-trash me-1"></i> Supprimer la sélection
                        </button>
                    </div>
                @endif
            @elseif(count($availableProducts) > 0)
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Le système est en train de charger les informations des bouteilles...
                </div>
            @else
                <div class="alert alert-danger">
                    <i class="ti ti-alert-triangle me-2"></i>
                    Vous devez d'abord enregistrer des types de bouteilles pour cette livraison.
                    @if ($supply->canBeEdited())
                        <a href="{{ route('supplies.register-products', request()->route('supply_id')) }}"
                            class="btn btn-sm btn-danger mt-2">
                            <i class="ti ti-plus me-1"></i> Ajouter des produits
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- DO NOT REMOVE: empty hidden anchor. Without it Livewire morph re-renders
         the scanButton in a way that breaks the click-after-warning flow, and
         the camera scanner stops reopening on the next click. Cause unclear
         (likely a morph child-count edge case in Livewire 3 + html5-qrcode).
         Diagnosed 2026-06-01. Keep until root cause is properly understood. --}}
    <div id="scan-debug-bar" style="display:none;"></div>

    @include('partials.mobile-app-scan-prompt')

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script src="{{ asset('assets/js/scan-code-bar.js') }}?v={{ filemtime(public_path('assets/js/scan-code-bar.js')) }}"></script>

        <script>
            // Initialize scanner button on every component update
            document.addEventListener('livewire:init', function() {
                Livewire.hook('morph.updated', (el) => {
                    initScanButton();
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                initScanButton();
            });

            function initScanButton() {
                const scanButton = document.getElementById('scanButton');

                if (scanButton) {
                    scanButton.removeEventListener('click', scanButtonClickHandler);
                    scanButton.addEventListener('click', scanButtonClickHandler);
                }
            }

            function scanButtonClickHandler() {
                if (typeof window.initBarcodeScanner === 'function') {
                    window.promptMobileAppThenScan(window.initBarcodeScanner);
                } else {
                    alert('Erreur: La fonction de scan n\'est pas disponible');
                }
            }

            // Set up bottle added animation
            window.addEventListener('bottleAdded', event => {
                const progress = document.querySelector('.progress-bar');
                if (progress) {
                    progress.classList.add('progress-bar-animated');
                    setTimeout(() => {
                        progress.classList.remove('progress-bar-animated');
                    }, 1000);
                }
            });
        </script>
    @endpush
</div>
