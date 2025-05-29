<div class="btn-group dropdown-icon-none">
    <button
        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
        type="button" id="dropdownMenuButton{{ $user->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu"
        aria-labelledby="dropdownMenuButton{{ $user->id }}">
        <li>
            <a class="dropdown-item"
                href="{{ route('users.details', $user->id) }}">
                <i class="ti ti-eye text-primary me-2"></i> Détail
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                data-bs-target="#editUserModal"
                data-user-id="{{ $user->id }}">
                <i class="ti ti-edit text-success me-2"></i> Editer
            </a>
        </li>
        @if ($user->is_active)
            <li>
                <a class="dropdown-item toggle-status-btn" href="#"
                    data-user-id="{{ $user->id }}"
                    data-status="active">
                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item toggle-status-btn" href="#"
                    data-user-id="{{ $user->id }}"
                    data-status="inactive">
                    <i class="ti ti-check text-success me-2"></i> Activer
                </a>
            </li>
        @endif
    </ul>
</div>
