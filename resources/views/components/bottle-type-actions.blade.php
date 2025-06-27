@props(['bottleType'])

<div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuBottleType{{ $bottleType->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuBottleType{{ $bottleType->id }}">
        <li>
            <a class="dropdown-item" href="{{route('bottles.types.edit', $bottleType->id)}}">
                <i class="ti ti-edit text-primary me-2"></i> Modifier
            </a>
        </li>
        <li>
            @if ($bottleType->is_active)
                <a class="dropdown-item" href="#" onclick="confirmActionWithInput({
                    method: 'toggleBottleTypeStatus',
                    parameters: [{{ $bottleType->id }}, false],
                    title: 'Désactiver le type de bouteille',
                    text: 'Vous êtes sur le point de désactiver ce type de bouteille. Cette action rendra le type de bouteille indisponible.',
                    confirmWord: 'desactiver',
                    entityName: 'Type de bouteille: {{ str_replace("'", "\\'", $bottleType->name) }}',
                    icon: 'warning',
                    confirmText: 'Désactiver',
                    confirmButtonIcon: 'ti ti-ban',
                    actionInProgressText: 'Désactivation en cours...',
                    id: {{ $bottleType->id }}
                }); return false;">
                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                </a>
            @else
                <a class="dropdown-item" href="#" onclick="confirmAction({
                    method: 'toggleBottleTypeStatus',
                    parameters: [{{ $bottleType->id }}, true],
                    title: 'Activer le type de bouteille',
                    text: 'Voulez-vous vraiment activer ce type de bouteille {{ $bottleType->name }} ?',
                    icon: 'question',
                    confirmText: 'Oui, activer'
                }); return false;">
                    <i class="ti ti-check text-success me-2"></i> Activer
                </a>
            @endif
        </li>
        <li>
            <a class="dropdown-item" href="#" onclick="confirmActionWithInput({
                method: 'deleteBottleType',
                parameters: [{{ $bottleType->id }}],
                title: 'Supprimer le type de bouteille',
                text: 'Vous êtes sur le point de supprimer définitivement ce type de bouteille.',
                confirmWord: 'supprimer',
                entityName: 'Type de bouteille: {{ str_replace("'", "\\'", $bottleType->name) }} (Capacité: {{ $bottleType->capacity }})',
                icon: 'warning',
                confirmText: 'Supprimer définitivement',
                confirmButtonIcon: 'ti ti-trash',
                actionInProgressText: 'Suppression en cours...',
                id: {{ $bottleType->id }}
            }); return false;">
                <i class="ti ti-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>