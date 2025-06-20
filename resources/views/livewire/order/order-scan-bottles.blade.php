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

    <!-- Sélection du type de bouteille -->
    <div class="row mb-4 align-items-center">
        <label class="col-sm-4 col-form-label">Sélectionnez le type de bouteille :</label>
        <div class="col-sm-8">
            @if (count($bottleTypes) > 0)
                <select class="form-select" wire:model.live="selectedBottleTypeId" wire:change="updateSelectedBottleType">
                    @foreach ($bottleTypes as $type)
                        <option value="{{ $type['id'] }}">
                            {{ $type['name'] }}
                            ({{ $type['scanned_quantity'] }} / {{ $type['total_quantity'] }} scannées)
                            @if ($type['is_complete'])
                                <span class="badge bg-success">terminé</span>
                            @else
                                <span class="badge bg-warning">en cours</span>
                            @endif
                        </option>
                    @endforeach
                </select>
            @else
                <div class="alert alert-warning mb-0">
                    Aucun type de bouteille disponible pour cette commande.
                </div>
            @endif
        </div>
    </div>

    @if ($selectedBottleTypeId)
        <!-- Informations sur les bouteilles à scanner -->
        <div class="row mb-3 align-items-center">
            <div class="col-sm-8">
                <h6 class="mb-0">
                    Bouteilles à scanner
                    ({{ $scannedBottlesForType }} scannées / {{ $totalBottlesForType }} requises)
                </h6>
            </div>
            <div class="col-sm-4 text-end">
                <button type="button" class="btn btn-primary me-2" id="scanButton">
                    <i class="ti ti-scan me-1"></i>Scanner
                </button>
                <button type="button" class="btn btn-dark" wire:click="toggleManualForm">
                    <i class="ti ti-keyboard me-1"></i>Manuel
                </button>
            </div>
        </div>

        <!-- Barre de progression -->
        <div class="progress mb-4" style="height: 8px;">
            @php
                $percentage = $totalBottlesForType > 0 ? ($scannedBottlesForType / $totalBottlesForType) * 100 : 0;
            @endphp
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"
                aria-valuenow="{{ $scannedBottlesForType }}" aria-valuemin="0" aria-valuemax="{{ $totalBottlesForType }}">
            </div>
        </div>

        <!-- Formulaire de saisie manuelle -->
        @if ($showManualForm)
            <div class="card mb-4 border-primary">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="manualBarcode" class="form-label">Entrer un code-barres manuellement :</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="manualBarcode" wire:model="manualBarcode"
                                placeholder="Entrez un code-barres">
                            <button class="btn btn-primary" type="button" wire:click="addManualBarcode">Ajouter</button>
                        </div>
                        @error('manualBarcode')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        <!-- Tableau des bouteilles scannées -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:click="selectAll">
                            </div>
                        </th>
                        <th width="50px">#</th>
                        <th>Code-barres</th>
                        <th width="180px">Date/Heure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scannedBottles as $index => $bottle)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="selectedBottles"
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

        <!-- Boutons d'action -->
        <div class="mt-3">
            <button type="button" class="btn btn-danger" {{ count($selectedBottles) ? '' : 'disabled' }}
                wire:click="removeSelected"
                wire:confirm="Êtes-vous sûr de vouloir supprimer les bouteilles sélectionnées?">
                <i class="ti ti-trash me-1"></i> Supprimer la sélection
            </button>
        </div>
    @endif

    @push('scripts')
        <script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
        <script>
            document.addEventListener('livewire:initialized', function () {
                initScanButton();
                
                // Réinitialiser le bouton de scan à chaque mise à jour du composant
                Livewire.hook('morph.updated', (el) => {
                    initScanButton();
                });
            });

            function initScanButton() {
                const scanButton = document.getElementById('scanButton');
                if (scanButton) {
                    scanButton.removeEventListener('click', scanButtonClickHandler);
                    scanButton.addEventListener('click', scanButtonClickHandler);
                }
            }

            function scanButtonClickHandler() {
                // Créer un div modal pour le scanner
                const scannerContainer = document.createElement('div');
                scannerContainer.id = 'scanner-container';
                scannerContainer.style.position = 'fixed';
                scannerContainer.style.top = '0';
                scannerContainer.style.left = '0';
                scannerContainer.style.width = '100%';
                scannerContainer.style.height = '100%';
                scannerContainer.style.backgroundColor = 'rgba(0,0,0,0.8)';
                scannerContainer.style.zIndex = '9999';
                scannerContainer.style.display = 'flex';
                scannerContainer.style.flexDirection = 'column';
                scannerContainer.style.justifyContent = 'center';
                scannerContainer.style.alignItems = 'center';

                // Créer l'interface du scanner
                scannerContainer.innerHTML = `
                    <div style="position: relative; width: 80%; max-width: 640px;">
                        <div id="scanner-close-btn" style="position: absolute; top: 10px; right: 10px; color: white; font-size: 24px; cursor: pointer; z-index: 10000;">
                            <i class="ti ti-x"></i>
                        </div>
                        <div id="interactive" class="viewport" style="width: 100%; height: 300px; background: #000; overflow: hidden;"></div>
                        <div style="text-align: center; margin-top: 10px; color: white;">
                            Positionnez le code-barres dans le cadre
                        </div>
                    </div>
                `;

                document.body.appendChild(scannerContainer);

                // Fermer le scanner quand on clique sur le bouton de fermeture
                document.getElementById('scanner-close-btn').addEventListener('click', function() {
                    closeScannerModal();
                });

                // Initialiser le scanner
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#interactive'),
                        constraints: {
                            width: 640,
                            height: 480,
                            facingMode: "environment"
                        },
                    },
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader",
                            "code_39_vin_reader",
                            "codabar_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "i2of5_reader"
                        ],
                        multiple: false,
                    },
                    locate: true
                }, function(err) {
                    if (err) {
                        console.error(err);
                        alert("Erreur d'initialisation du scanner: " + err);
                        closeScannerModal();
                        return;
                    }
                    console.log("Scanner démarré");
                    Quagga.start();
                });

                // Traiter les codes-barres scannés
                Quagga.onDetected(function(result) {
                    const code = result.codeResult.code;
                    console.log("Code détecté:", code);
                    
                    // Dispatcher l'événement au composant Livewire
                    if (code) {
                        @this.processBarcode({barcode: code});
                        
                        // Ajouter un effet visuel de succès
                        const viewport = document.querySelector('#interactive');
                        viewport.classList.add('scan-success');
                        setTimeout(() => {
                            viewport.classList.remove('scan-success');
                        }, 500);
                        
                        // Fermer le scanner après le scan réussi
                        closeScannerModal();
                    }
                });

                function closeScannerModal() {
                    Quagga.stop();
                    document.body.removeChild(scannerContainer);
                }
            }

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('bottleScanned', (event) => {
                    const progressBar = document.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.classList.add('progress-bar-striped');
                        progressBar.classList.add('progress-bar-animated');
                        setTimeout(() => {
                            progressBar.classList.remove('progress-bar-striped');
                            progressBar.classList.remove('progress-bar-animated');
                        }, 1000);
                    }
                });
                
                Livewire.on('scanError', (data) => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.maxWidth = '80%';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    
                    document.body.appendChild(alertDiv);
                    
                    setTimeout(() => {
                        alertDiv.classList.remove('show');
                        setTimeout(() => {
                            if (document.body.contains(alertDiv)) {
                                document.body.removeChild(alertDiv);
                            }
                        }, 150);
                    }, 5000);
                });
            });
        </script>

        <style>
            .scan-success {
                animation: flash 0.5s;
            }
            
            @keyframes flash {
                0% { opacity: 1; }
                50% { opacity: 0.5; background: rgba(0, 255, 0, 0.5); }
                100% { opacity: 1; }
            }
            
            .viewport canvas, .viewport video {
                width: 100%;
                height: auto;
            }
        </style>
    @endpush
</div>