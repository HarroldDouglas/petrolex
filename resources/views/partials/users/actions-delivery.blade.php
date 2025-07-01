@props(['user'])

<div wire:key="user-actions-cell-delivery-{{ $user->id }}">
    <a href="{{ route('users.delivery.details', $user->id) }}" class="btn btn-sm btn-info">
        <i class="bi bi-eye"></i> Détails
    </a>
</div>