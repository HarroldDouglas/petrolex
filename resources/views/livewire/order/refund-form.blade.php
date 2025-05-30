<div class="modal fade" id="refundModal{{ $order->id }}" tabindex="-1"
    aria-labelledby="refundModalLabel{{ $order->id }}" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel{{ $order->id }}">Rembourser la commande
                    #{{ $order->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="direction: ltr; text-align: left;">
                <form class="app-form" wire:submit.prevent="processRefund">
                    <div class="mb-3">
                        <label for="refund_method" class="form-label">Méthode de remboursement</label>
                        <select class="form-select" id="refund_method" wire:model="refund_method">
                            <option value="">Sélectionner une méthode</option>
                            @foreach ($availablePaymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('refund_method') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="refund_identifier" class="form-label">Identifiant de remboursement</label>
                        <input type="text" class="form-control" id="refund_identifier" wire:model="refund_identifier"
                            placeholder="Numéro de transaction, référence, etc.">
                        @error('refund_identifier') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Raison du remboursement</label>
                        <textarea class="form-control" id="reason" wire:model="reason" rows="3"></textarea>
                        @error('reason') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="confirmation" wire:model.live="confirmation">
                        <label class="form-check-label" for="confirmation">Je confirme vouloir poursuivre le remboursement
                        </label>
                        @error('confirmation') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-danger" @if(!$confirmation) disabled @endif>
                            <i class="ti ti-receipt-refund me-1"></i> 
                            <span wire:loading.remove wire:target="processRefund">Rembourser</span>
                            <span wire:loading wire:target="processRefund">Traitement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
