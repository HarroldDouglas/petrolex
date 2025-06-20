<div class="d-inline-block">
    <div class="dropdown">
        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Actions
        </button>
        <ul class="dropdown-menu">
            @if ($order->canScanBottles())
                <li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#collapseScanBottles"
                        aria-expanded="false" aria-controls="collapseScanBottles">
                        <i class="ti ti-scan me-2"></i>Scanner les bouteilles
                        <span class="badge bg-{{ $order->allBottlesScanned() ? 'success' : 'warning' }} ms-1">
                            {{ $order->bottle_scan_progress }}%
                        </span>
                    </a>
                </li>
            @endif
            <li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMap"
                    aria-expanded="false" aria-controls="collapseMap">
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
                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                        data-bs-target="#cancelOrderModal">
                        <i class="ti ti-receipt-refund me-2"></i>Annuler
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                        data-bs-target="#changeDeliveryPersonModal">
                        <i class="ti ti-user me-2"></i>Changer le livreur
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>

@if ($order->canBeCancelled())
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Confirmation d'annulation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-0">Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif


<!-- Modal pour changer de livreur -->
@if ($order->canChangeDeliveryPerson())
    <div class="modal fade" id="changeDeliveryPersonModal" tabindex="-1"
        aria-labelledby="changeDeliveryPersonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeDeliveryPersonModalLabel">Changer le livreur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="#" method="POST">
                    @csrf
                    @method('GET')
                    <div class="modal-body" style="text-align: left;">
                        <div class="mb-3">
                            <label class="form-label">Livreur actuel</label>
                            <input type="text" class="form-control"
                                value="{{ $order->deliveryPerson ? $order->deliveryPerson->name : 'Kelvin Ngoh' }}"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_person_id" class="form-label">Nouveau livreur</label>
                            <select class="form-select" id="delivery_person_id" name="delivery_person_id" required>
                                <option value="">Sélectionner un livreur</option>
                                <option value="1">John Doe</option>
                                <option value="2">Jane Smith</option>
                                <option value="3">Michael Brown</option>
                                <option value="4">Sarah Johnson</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="change_reason" class="form-label">Raison du changement</label>
                            <textarea class="form-control" id="change_reason" name="change_reason" rows="3"></textarea>
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
@endpush
