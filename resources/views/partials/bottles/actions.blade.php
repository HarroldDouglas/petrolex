@props(['bottle'])

<div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuButton{{ $bottle->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $bottle->id }}">
        <li>
            <a class="dropdown-item view-history" href="#" data-id="{{ $bottle->id }}"
                data-barcode="{{ $bottle->barcode }}" data-type="{{ optional($bottle->bottleType)->name }}"
                data-bs-toggle="modal" wire:click="$dispatch('showBottleHistory', { bottleId: {{ $bottle->id }} })"
                data-bs-target="#historyModal">
                <i class="iconoir-clock-rotate-right text-primary me-2"></i>
                Historique
            </a>
        </li>
        @if ($bottle->status->value !== \App\Enums\BottleStatus::LOST_STOLEN()->value)
            <li>
                <a class="dropdown-item mark-lost" href="#"
                    onclick="confirmActionWithInput({
                        method: 'changeBottleStatus',
                        parameters: [{{ $bottle->id }}, '{{ \App\Enums\BottleStatus::LOST_STOLEN()->value }}'],
                        componentId: '{{ $this->getId() }}',
                        title: 'Déclarer la bouteille perdue',
                        text: 'Vous êtes sur le point de déclarer cette bouteille comme perdue ou volée. Cette action est irréversible.',
                        confirmWord: 'perdue',
                        entityName: 'Bouteille: {{ str_replace("'", "\\'", $bottle->barcode) }}',
                        icon: 'warning',
                        confirmText: 'Oui, déclarer perdue',
                        confirmButtonIcon: 'ti ti-alert-triangle',
                        actionInProgressText: 'Déclaration en cours...'
                    }); return false;"
                    data-id="{{ $bottle->id }}">
                    <i class="iconoir-chat-bubble-question text-danger me-2"></i>
                    Déclarer perdu
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item mark-found" href="#"
                    onclick="confirmActionWithInput({
                        method: 'changeBottleStatus',
                        parameters: [{{ $bottle->id }}, '{{ \App\Enums\BottleStatus::IN_STOCK()->value }}'],
                        componentId: '{{ $this->getId() }}',
                        title: 'Marquer la bouteille comme retrouvée',
                        text: 'Vous êtes sur le point de marquer cette bouteille comme retrouvée. Elle sera remise en stock.',
                        confirmWord: 'retrouvee',
                        entityName: 'Bouteille: {{ str_replace("'", "\\'", $bottle->barcode) }}',
                        icon: 'info',
                        confirmText: 'Oui, marquer retrouvée',
                        confirmButtonIcon: 'ti ti-check',
                        actionInProgressText: 'Mise à jour en cours...'
                    }); return false;"
                    data-id="{{ $bottle->id }}">
                    <i class="iconoir-circle-spark text-success me-2"></i>
                    Marquer retrouvée
                </a>
            </li>
        @endif
    </ul>
</div>
