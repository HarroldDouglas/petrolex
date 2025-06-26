@php
    use App\Enums\SupplierDeliveryStatus;
    
    // Check if the supply isn't completed or cancelled
    $isEditable = !in_array($supply->status->value, [
        SupplierDeliveryStatus::COMPLETED()->value, 
        SupplierDeliveryStatus::CANCELLED()->value
    ]);
@endphp

@props(['supply'])

<div class="btn-group dropdown-icon-none">
    @if ($isEditable)
        <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                type="button" id="dropdownMenuButton{{ $supply->id }}" data-bs-toggle="dropdown"
                aria-expanded="false">
            <i class="ti ti-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $supply->id }}">
            <li>
                <a class="dropdown-item" href="{{ route('supplies.edit', $supply->id) }}">
                    <i class="ti ti-edit text-success me-2"></i> Editer
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" onclick="confirmActionWithInput({
                    method: 'deleteSupply',
                    parameters: [{{ $supply->id }}],
                    title: 'Supprimer l\'approvisionnement',
                    text: 'Vous êtes sur le point de supprimer l\'approvisionnement &quot;{{ str_replace("'", "\\'", $supply->delivery_number) }}&quot;. Cette action est irréversible et affectera les stocks.',
                    confirmWord: 'supprimer',
                    entityName: 'Approvisionnement: {{ str_replace("'", "\\'", $supply->delivery_number) }}',
                    icon: 'error',
                    confirmText: 'Oui, supprimer',
                    confirmButtonIcon: 'ti ti-trash',
                    actionInProgressText: 'Suppression en cours...',
                }); return false;"> 
                    <i class="ti ti-trash text-danger me-2"></i> Supprimer
                </a>
            </li>
            <li>
                <a class="dropdown-item mark-cancelled" href="#" onclick="confirmAction({
                        method: 'cancelSupply',
                        parameters: [{{ $supply->id }}],
                        title: 'Confirmer l\'annulation',
                        text: 'Voulez-vous vraiment annuler cette livraison fournisseur (Référence: {{ $supply->delivery_number }}) ?',
                        icon: 'warning',
                        confirmText: 'Oui, Annuler la Livraison',
                        cancelText: 'Annuler',
                    }); return false;">
                    <i class="ti ti-ban text-warning me-2"></i> Annuler
                </a>
            </li>
        </ul>
    @else
        <button class="btn btn-light-secondary icon-btn w-30 h-30 me-0" disabled
                type="button" title="Action non disponible">
            <i class="ti ti-lock"></i>
        </button>
    @endif
</div>
