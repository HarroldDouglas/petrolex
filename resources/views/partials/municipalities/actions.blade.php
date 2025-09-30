<div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuButton{{ $municipality->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $municipality->id }}">
        <li>
            <a class="dropdown-item" href="{{ route('municipalities.edit', $municipality->id) }}">
                <i class="ti ti-edit text-success me-2"></i> Éditer
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
                onclick="confirmActionWithInput({
                method: 'deleteMunicipality',
                parameters: [{{ $municipality->id }}],
                componentId: '{{ $this->getId() }}',
                title: 'Supprimer la municipalité',
                text: 'Vous êtes sur le point de supprimer cette municipalité. Cette action est irréversible et supprimera également toutes les associations avec les quartiers.',
                confirmWord: 'supprimer',
                entityName: 'Municipalité: {{ str_replace("'", "\\'", $municipality->name) }}',
                icon: 'warning',
                confirmText: 'Oui, supprimer',
                confirmButtonIcon: 'ti ti-trash',
                actionInProgressText: 'Suppression en cours...'
            }); return false;">
                <i class="ti ti-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>