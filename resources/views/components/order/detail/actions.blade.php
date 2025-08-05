<div class="col mt-3 mb-3 text-end">
    <div class="d-flex align-items-center justify-content-end">
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu">
                @if ($order->canScanBottles())
                    <li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#collapseScanBottles"
                            aria-expanded="false" aria-controls="collapseScanBottles">
                            <i class="ti ti-scan me-2"></i>Scanner les bouteilles
                            <span class="badge bg-{{ $order->areAllBottlesScanned() ? 'success' : 'warning' }} ms-1">
                                {{ $order->bottle_scan_progress }}%
                            </span>
                        </a>
                    </li>
                @endif
                <li><a class="dropdown-item" href="{{ route('orders.track.realtime', $order->id) }}" target="_blank">
                        <i class="ti ti-map-pin me-2"></i>Voir en temps réel
                    </a></li>
                <li><a class="dropdown-item text-success" href="#" onclick="printOrder({{ $order->id }})">
                        <i class="ti ti-printer me-2"></i>Imprimer la facture
                    </a></li>
                <li><a class="dropdown-item text-success"
                        href="{{ route('orders.download.invoice', ['order' => $order->id]) }}">
                        <i class="ti ti-download me-2"></i>Télécharger la facture
                    </a></li>

                @if ($order->canBeCancelled())
                    <li>
                        <a class="dropdown-item text-danger" href="#" 
                            onclick="promptForCancellationReason({ 
                                method: 'cancelOrder', 
                                parameters: [{{ $order->id }}], 
                                componentId: '{{ $this->getId() }}' 
                            })">
                            <i class="ti ti-receipt-refund me-2"></i>Annuler
                        </a>
                    </li>
                @endif

                @if ($order->canChangeDeliveryPerson())
                <li>
                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                        data-bs-target="#changeDeliveryPersonModal">
                        <i class="ti ti-user me-2"></i>Changer le livreur
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <a class="btn btn-primary ms-2" href="{{ route('orders.list') }}">
            <i class="ti ti-arrow-back"></i> Retourner à la liste
        </a>
    </div>




    <!-- Modal pour changer de livreur -->
    @if ($order->canChangeDeliveryPerson())
        <div class="modal fade" id="changeDeliveryPersonModal" tabindex="-1"
            aria-labelledby="changeDeliveryPersonModalLabel" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changeDeliveryPersonModalLabel">Changer le livreur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form wire:submit.prevent="assignDeliveryPerson">
                        <div class="modal-body" style="text-align: left;">
                            <div class="mb-3">
                                <label class="form-label">Livreur actuel</label>
                                <input type="text" class="form-control"
                                    value="{{ $order->deliveryPerson ? $order->deliveryPerson->user->fullname : 'Non assigné' }}"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_person_id" class="form-label">Nouveau livreur</label>
                                
                                <select class="form-select" id="delivery_person_id" wire:model="newDeliveryPersonId" required>
                                    <option value="">Sélectionner un livreur</option>
                                    @foreach($allDeliveryPersons as $key=>$person)
                                        <option value="{{ $person->id }}">{{ $person->user->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('newDeliveryPersonId') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="change_reason" class="form-label">Raison du changement</label>
                                <textarea class="form-control" id="change_reason" wire:model="updateReason" rows="3"></textarea>
                                @error('updateReason') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Changer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function printOrder(orderId, withStub = true) {
                const url = `/orders/${orderId}/print?withStub=${withStub}`;

                const printWindow = window.open(url, '_blank', 'width=800,height=600');

                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.print();
                    }, 500);
                };

                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }
        </script>

        <script src="{{ asset('assets/js/custom/sweet_alert_confirm_actions.js') }}" defer></script>
    <!-- sweet-alert cancel reason script -->
        <script>
            /**
             * Affiche une première boîte de dialogue pour demander la raison de l'annulation,
             * puis enchaîne avec la confirmation de saisie.
             *
             * @param {Object} params - Les paramètres de configuration
             * @param {string} params.method - Nom de la méthode Livewire à appeler
             * @param {Array} params.parameters - Paramètres initiaux à passer (l'ID de la commande, etc.)
             * @param {string} params.componentId - ID du composant Livewire
             * @param {string} params.confirmWord - Mot à taper pour la confirmation finale (ex: "Annuler")
             */
            function promptForCancellationReason(params) {
                Swal.fire({
                    title: 'Motif de l\'annulation',
                    input: 'textarea',
                    inputLabel: 'Veuillez spécifier la raison de l\'annulation de cette commande.',
                    inputPlaceholder: 'Entrez la raison ici...',
                    inputAttributes: {
                        'aria-label': 'Entrez la raison ici'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Continuer',
                    cancelButtonText: 'Fermer',
                    confirmButtonColor: "#198754",
                    cancelButtonColor: "#6c757d",
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Vous devez fournir une raison !'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const reason = result.value;
                        const finalParams = {
                            ...params,
                            parameters: [...params.parameters, reason], // Ajoute la raison aux paramètres
                            title: 'Êtes-vous absolument sûr ?',
                            text: 'Cette action est irréversible et annulera la commande.',
                            entityName: `Raison : ${reason}`,
                            icon: 'warning',
                            confirmWord: 'Annuler',
                            confirmButtonIcon: 'ti ti-receipt-refund',
                            confirmText: 'Oui, annuler la commande'
                        };
                        confirmActionWithInput(finalParams);
                    }
                });
            }
        </script>
    @endpush
</div>