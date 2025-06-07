<div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuButton{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $user->id }}">
        <li>
            <a class="dropdown-item" href="{{ route('users.details', $user->id) }}">
                <i class="ti ti-eye text-primary me-2"></i> Détail
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal"
                data-user-id="{{ $user->id }}">
                <i class="ti ti-edit text-success me-2"></i> Editer
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#" onclick="toggleUser{{ $user->id }}(); return false;">
                @if ($user->is_active)
                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                @else
                    <i class="ti ti-check text-success me-2"></i> Activer
                @endif
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#" onclick="deleteUser{{ $user->id }}(); return false;">
                <i class="ti ti-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>

{{-- Composants Sweet Alert --}}
<x-sweet-alert-confirm 
    title="Changer le statut"
    text="Voulez-vous vraiment {{ $user->is_active ? 'désactiver' : 'activer' }} cet utilisateur ?"
    icon="question"
    confirmText="{{ $user->is_active ? 'Oui, désactiver' : 'Oui, activer' }}"
    method="toggleUserStatus"
    :parameters="[$user->id]"
    functionName="toggleUser{{ $user->id }}" />

<x-sweet-alert-delete 
    title="Supprimer l'utilisateur"
    text="Vous êtes sur le point de supprimer définitivement cet utilisateur."
    entityName="Utilisateur: {{ $user->name }} ({{ $user->email }})"
    method="deleteUser"
    :parameters="[$user->id]"
    functionName="deleteUser{{ $user->id }}" />