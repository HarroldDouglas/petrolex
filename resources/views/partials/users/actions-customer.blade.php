@props(['user'])

<div wire:key="user-actions-cell-customer-{{ $user->id }}">
    <a href="{{ route('users.customer.details', $user->id) }}" class="btn btn-sm btn-info">
        <i class="bi bi-eye"></i> Détails
    </a>
</div>