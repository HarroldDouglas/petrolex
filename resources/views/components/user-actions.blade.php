<div class="btn-group">
    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
        <i class="fas fa-edit"></i>
    </a>

    <button class="btn btn-sm btn-danger"
            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?') || event.stopImmediatePropagation()"
            wire:click="$emit('deleteUser', {{ $user->id }})">
        <i class="fas fa-trash"></i>
    </button>
</div>
