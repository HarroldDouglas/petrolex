<div>
    <div class="card bg-white text-black p-4 mb-4">
        <!-- Supply info and bottle type info combined -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0">{{ $supplyTitle }}</h5>
                <p class="text-muted mb-0">
                    {{ $supplyDate ? date('d/m/Y H:i', strtotime($supplyDate)) : date('d/m/Y H:i') }}</p>
            </div>
            <div>
                <button type="button" class="btn btn-{{ $isIncomingMode ? 'success' : 'primary' }}"
                    wire:click="toggleMode">
                    <i class="ti ti-{{ $isIncomingMode ? 'arrow-up-circle' : 'arrow-down-circle' }} me-1"></i>
                    Gérer les bouteilles {{ $isIncomingMode ? 'sortantes' : 'entrantes' }}
                </button>
            </div>
        </div>

        <!-- Bottle type info with progress -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h6 class="mb-2"><strong>Bouteilles {{ $isIncomingMode ? 'entrantes' : 'sortantes' }} de</strong>
                    {{ $bottleType }}
                    <span class="text-muted">({{ $isIncomingMode ? $scannedIncoming : $scannedOutgoing }} scannés /
                        {{ $isIncomingMode ? $quantity : $outgoingQuantity }})</span>
                </h6>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar"
                        style="width: {{ ($isIncomingMode ? $scannedIncoming / max(1, $quantity) : $scannedOutgoing / max(1, $outgoingQuantity)) * 100 }}%;"
                        aria-valuenow="{{ $isIncomingMode ? $scannedIncoming : $scannedOutgoing }}" aria-valuemin="0"
                        aria-valuemax="{{ $isIncomingMode ? $quantity : $outgoingQuantity }}">
                        {{ $isIncomingMode ? $scannedIncoming : $scannedOutgoing }} /
                        {{ $isIncomingMode ? $quantity : $outgoingQuantity }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions buttons -->
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary me-2" id="scanButton">
                    <i class="ti ti-scan me-1"></i>Scanner
                </button>
                <button type="button" class="btn btn-dark" wire:click="toggleManualForm">
                    <i class="ti ti-keyboard me-1"></i>Ajouter manuellement
                </button>
            </div>
        </div>

        <!-- Formulaire d'ajout manuel -->
        @if ($showManualForm)
            <div class="p-3 rounded mb-3">
                <form wire:submit.prevent="addManualBarcode" class="row align-items-end">
                    <div class="col-md-9">
                        <label for="manualBarcode" class="form-label">Code-barre</label>
                        <input type="text" class="form-control" id="manualBarcode" wire:model="manualBarcode"
                            placeholder="Entrez le code-barre manuellement" autofocus>
                        @error('manualBarcode')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-device-floppy me-1"></i>Valider
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Messages flash -->
        @if (session()->has('message'))
            <div class="alert alert-success mb-3">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('warning'))
            <div class="alert alert-warning mb-3">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Section datatable des bouteilles scannées -->
        <div class="table-responsive mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Bouteilles {{ $isIncomingMode ? 'entrantes' : 'sortantes' }} scannées</h6>
                <button class="btn btn-danger" wire:click="removeSelected"
                    @if (empty($selectedBottles)) disabled @endif>
                    <i class="ti ti-trash me-1"></i>Supprimer la sélection
                </button>
            </div>

            <table class="table table-bordered">
                <thead class="bg-primary">
                    <tr>
                        <th style="width: 50px;">
                            <div class="form-check">
                                <input class="form-check-input select-all" type="checkbox"
                                    @if (count($selectedBottles) === count($bottles) && count($bottles) > 0) checked @endif>
                            </div>
                        </th>
                        <th>Code-barre</th>
                        <th>Date et heure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bottles as $bottle)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input checkbox-item" type="checkbox"
                                        value="{{ $bottle['id'] }}" @if (in_array($bottle['id'], $selectedBottles)) checked @endif>
                                </div>
                            </td>
                            <td>{{ $bottle['barcode'] }}</td>
                            <td>{{ $bottle['timestamp'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Aucune bouteille
                                {{ $isIncomingMode ? 'entrante' : 'sortante' }} scannée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript pour intégrer Quagga et le scanner -->
    @push('scripts')
        <script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
        <script>
            if (typeof window.scanScriptsLoaded === 'undefined') {
                window.scanScriptsLoaded = true;

                const script = document.createElement('script');
                script.src = "{{ asset('assets/js/scan-code-bar.js') }}?v=" + new Date().getTime();
                script.onload = function() {
                    console.log('scan-code-bar.js loaded successfully');
                };
                document.head.appendChild(script);

                console.log('Setting up event handlers');
                document.addEventListener('DOMContentLoaded', function() {
                    setupScanner();
                });

                function setupScanner() {
                    console.log('Setting up scanner components');

                    // Set up scan button with debouncing
                    let scanInitializing = false;
                    const scanButton = document.getElementById('scanButton');

                    if (scanButton) {
                        console.log('Scan button found, adding event listener');
                        scanButton.addEventListener('click', function() {
                            console.log('Scan button clicked');
                            if (scanInitializing) {
                                console.log('Already initializing, ignoring click');
                                return;
                            }

                            scanInitializing = true;
                            console.log('Setting up scanner initialization');

                            setTimeout(() => {
                                scanInitializing = false;
                                console.log('Reset scan initialization flag');
                            }, 2000);

                            if (typeof window.initBarcodeScanner === 'function') {
                                console.log('Calling initBarcodeScanner function');
                                window.initBarcodeScanner();
                            } else {
                                console.error('initBarcodeScanner function not found!');
                            }
                        });
                    } else {
                        console.error('Scan button not found!');
                    }

                    // Set up bottle added animation
                    window.addEventListener('bottleAdded', event => {
                        console.log('Bottle added event received');
                        const progress = document.querySelector('.progress-bar');
                        if (progress) {
                            progress.classList.add('progress-bar-animated');
                            setTimeout(() => {
                                progress.classList.remove('progress-bar-animated');
                            }, 1000);
                        }
                    });

                    console.log('Scanner setup complete');
                }
            } else {
                console.log('Scan scripts already loaded, not reloading');
            }

            document.addEventListener('DOMContentLoaded', function() {
                setupCheckboxes();

                // Re-setup checkboxes after each Livewire update
                document.addEventListener('livewire:load', function() {
                    Livewire.hook('message.processed', () => {
                        setupCheckboxes();
                    });
                });

                function setupCheckboxes() {
                    const selectAll = document.querySelector('.select-all');
                    const checkboxItems = document.querySelectorAll('.checkbox-item');

                    if (selectAll && checkboxItems.length > 0) {
                        // Handle "select all" checkbox click
                        selectAll.addEventListener('click', function() {
                            const isChecked = this.checked;

                            // Update all checkboxes to match the select all state
                            checkboxItems.forEach(checkbox => {
                                checkbox.checked = isChecked;
                            });

                            // Get all selected IDs
                            const selectedIds = isChecked ?
                                Array.from(checkboxItems).map(checkbox => parseInt(checkbox.value)) :
                                [];

                            // Update Livewire component
                            @this.set('selectedBottles', selectedIds);
                        });

                        // Handle individual checkbox clicks
                        checkboxItems.forEach(checkbox => {
                            checkbox.addEventListener('click', function() {
                                const selectedIds = Array.from(checkboxItems)
                                    .filter(checkbox => checkbox.checked)
                                    .map(checkbox => parseInt(checkbox.value));

                                // Update select all checkbox
                                selectAll.checked = selectedIds.length === checkboxItems.length;

                                // Update Livewire component
                                @this.set('selectedBottles', selectedIds);
                            });
                        });

                        // Initial state for select all checkbox
                        selectAll.checked = checkboxItems.length > 0 &&
                            Array.from(checkboxItems).every(checkbox => checkbox.checked);
                    }
                }
            });
        </script>
    @endpush
</div>
