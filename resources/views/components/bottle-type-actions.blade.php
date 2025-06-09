@props(['bottleType'])

<div class="btn-group dropdown-icon-none">
    <button
        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
        type="button"
        id="dropdownMenuBottleType{{ $bottleType->id }}"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul
        class="dropdown-menu"
        aria-labelledby="dropdownMenuBottleType{{ $bottleType->id }}"
    >
        <li>
            <a class="dropdown-item edit-bottle-type" href="#"
                wire:click.prevent="$dispatch('showEditBottleTypeModal', { bottleTypeId: {{ $bottleType->id }} })"
                data-bs-toggle="modal"
                data-bs-target="#editBottleTypeModal"
            >
                <i class="ti ti-edit text-primary me-2"></i>
                Modifier
            </a>
        </li>
        <li>
            <a class="dropdown-item delete-bottle-type" href="#"
                wire:click.prevent="deleteBottleType({{ $bottleType->id }})"
                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type de bouteille : {{ $bottleType->name }}?') || event.stopImmediatePropagation()"
            >
                <i class="ti ti-trash text-danger me-2"></i>
                Supprimer
            </a>
        </li>
        
        @if(!$bottleType->is_active)
            <li>
                <a class="dropdown-item activate-bottle-type" href="#"
                   wire:click.prevent="toggleBottleTypeStatus({{ $bottleType->id }}, true)"
                   onclick="return confirm('Voulez-vous vraiment activer ce type de bouteille : {{ $bottleType->name }}?')"
                >
                    <i class="ti ti-check text-success me-2"></i>
                    Activer
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item deactivate-bottle-type" href="#"
                   wire:click.prevent="toggleBottleTypeStatus({{ $bottleType->id }}, false)"
                   onclick="return confirm('Voulez-vous vraiment désactiver ce type de bouteille : {{ $bottleType->name }}?')"
                >
                    <i class="ti ti-ban text-warning me-2"></i>
                    Désactiver
                </a>
            </li>
        @endif
    </ul>
</div>