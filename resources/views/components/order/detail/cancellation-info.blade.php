<div class="cancellation-info">
    <div class="d-flex flex-column gap-1 mt-2">
        @if ($user)
            <div class="d-flex align-items-center">
                <span class="text-dark"><i class="ti ti-user me-2"></i>Annulée par:</span>
                <span class="ms-2 fw-medium">{{ $user->fullName }}, <span class="badge bg-dark">{{ $user->roles->first()->name }}</span></span>
            </div>
        @endif

        @if ($cancellationReason)
            <div class="d-flex align-items-center">
                <span class="text-dark"><i class="ti ti-info-circle me-2"></i>Raison:</span>
                <span class="ms-2 fw-medium">{{ $cancellationReason }}</span>
            </div>
        @endif
    </div>

    @if ($refund)
        <div class="mt-3">
            <h6 class="fw-medium mb-2 text-dark">Détails du remboursement</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr class="table-dark">
                            <th class="fw-medium">Montant</th>
                            <th class="fw-medium">Méthode</th>
                            <th class="fw-medium">Date</th>
                            <th class="fw-medium">Référence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-dark">{{ number_format($refund->amount, 0, ',', ' ') }} CFA</span></td>
                            <td><span class="badge bg-success">{{ $refund->refund_method->label }}</span></td>
                            <td><span class="badge bg-dark">{{ $refund->completed_at->format('d/m/Y H:i') }}</span></td>
                            <td><span class="badge bg-success">{{ $refund->refund_identifier }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
