<div class="btn-group dropdown-icon-none">
    <button class="btn btn-primary b-r-22 dropdown-toggle" type="button"
        id="userActionsDropdown{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-settings"></i> Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="userActionsDropdown{{ $user->id }}">
        <li>
            <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                <i class="ti ti-edit text-success me-2"></i> Editer
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
                onclick="confirmAction({
                method: 'toggleUserStatus',
                parameters: [],
                componentId: '{{ $this->getId() }}',
                title: 'Changer le statut',
                text: 'Voulez-vous vraiment {{ $user->is_active ? 'désactiver' : 'activer' }} l\'utilisateur {{ $user->full_name }} ?',
                icon: 'question',
                confirmText: '{{ $user->is_active ? 'Oui, désactiver' : 'Oui, activer' }}'
            }); return false;">
                @if ($user->is_active)
                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                @else
                    <i class="ti ti-check text-success me-2"></i> Activer
                @endif
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#"
                onclick="confirmActionWithInput({
                method: 'deleteUser',
                parameters: [],
                componentId: '{{ $this->getId() }}',
                title: 'Supprimer l\'utilisateur',
                text: 'Vous êtes sur le point de supprimer définitivement cet utilisateur.',
                confirmWord: 'supprimer',
                entityName: 'Utilisateur: {{ str_replace("'", "\\'", $user->full_name) }} ({{ $user->email ?: $user->phone_number }})',
                icon: 'warning',
                confirmText: 'Supprimer définitivement',
                confirmButtonIcon: 'ti ti-trash',
                actionInProgressText: 'Suppression en cours...',
                id: {{ $user->id }}
            }); return false;">
                <i class="ti ti-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>
