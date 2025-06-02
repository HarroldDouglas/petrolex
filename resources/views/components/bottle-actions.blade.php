@props(['bottle'])

<div class="btn-group dropdown-icon-none">
    <button
        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
        type="button" id="dropdownMenuButton{{ $bottle->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu"
        aria-labelledby="dropdownMenuButton{{ $bottle->id }}">
        <li>
            <a class="dropdown-item view-history" href="#"
                data-id="{{ $bottle->id }}"
                data-barcode="{{ $bottle->barcode }}"
                data-type="{{ optional($bottle->bottleType)->name }}"
                data-bs-toggle="modal"
                data-bs-target="#historyModal">
                <i class="iconoir-clock-rotate-right text-primary me-2"></i>
                Historique
            </a>
        </li>
        @if($bottle->status->value !== \App\Enums\BottleStatus::LOST_STOLEN()->value)
            <li>
                <a class="dropdown-item mark-lost" href="#"
                    data-id="{{ $bottle->id }}">
                    <i class="iconoir-chat-bubble-question text-danger me-2"></i>
                    Déclarer perdu
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item mark-found" href="#"
                    data-id="{{ $bottle->id }}">
                    <i class="iconoir-circle-spark text-success me-2"></i>
                    Marquer retrouvée
                </a>
            </li>
        @endif
    </ul>
</div>