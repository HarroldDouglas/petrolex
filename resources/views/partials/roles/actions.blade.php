  <div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuButton{{ $role->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $role->id }}">
        
        <li>
            <a class="dropdown-item" href="{{ route('roles.edit', $role->id) }}">
                <i class="ti ti-edit text-success me-2"></i> Modifier
            </a>
        </li>
        
        @can('roles.manage')
            <li>
                <a class="dropdown-item" href="#"
                    onclick="confirmActionWithInput({
                    method: 'deleteRole',
                    parameters: [{{ $role->id }}],
                    componentId: '{{ $this->getId() }}',
                    title: 'Supprimer le rôle',
                    text: 'Vous êtes sur le point de supprimer ce rôle. Cette action est irréversible et ne peut être effectuée que si aucun utilisateur n\'est assigné à ce rôle.',
                    confirmWord: 'supprimer',
                    entityName: 'Rôle: {{ str_replace("'", "\\'", $role->name) }}',
                    icon: 'warning',
                    confirmText: 'Oui, supprimer',
                    confirmButtonIcon: 'ti ti-trash',
                    actionInProgressText: 'Suppression en cours...'
                }); return false;">
                    <i class="ti ti-trash text-danger me-2"></i> Supprimer
                </a>
            </li>
        @endcan
    </ul>
</div>