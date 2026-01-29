<div class="d-flex gap-2">
    <!-- Edit Button -->
    <a href="{{ route('neighborhoods.edit', $neighborhood->id) }}"
       class="btn btn-sm btn-info"
       title="Modifier">
        <i class="ti ti-edit"></i>
    </a>

    <!-- Toggle Status Button -->
    <button
        type="button"
        class="btn btn-sm {{ $neighborhood->is_active ? 'btn-warning' : 'btn-success' }}"
        title="{{ $neighborhood->is_active ? 'Désactiver' : 'Activer' }}"
        onclick="confirmToggleStatus({{ $neighborhood->id }}, '{{ $neighborhood->name }}', {{ $neighborhood->is_active ? 'true' : 'false' }})"
    >
        <i class="ti ti-{{ $neighborhood->is_active ? 'ban' : 'check' }}"></i>
    </button>

    <!-- Delete Button -->
    <button
        type="button"
        class="btn btn-sm btn-danger"
        title="Supprimer"
        onclick="confirmDelete({{ $neighborhood->id }}, '{{ $neighborhood->name }}')"
    >
        <i class="ti ti-trash"></i>
    </button>
</div>

@push('scripts')
<script>
    function confirmToggleStatus(neighborhoodId, neighborhoodName, isActive) {
        const action = isActive ? 'désactiver' : 'activer';
        const title = isActive ? 'Désactiver ce quartier ?' : 'Activer ce quartier ?';

        Swal.fire({
            title: title,
            text: `Voulez-vous vraiment ${action} le quartier "${neighborhoodName}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isActive ? '#f59e0b' : '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: `Oui, ${action}`,
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('toggleNeighborhoodStatus', neighborhoodId);
            }
        });
    }

    function confirmDelete(neighborhoodId, neighborhoodName) {
        Swal.fire({
            title: 'Supprimer ce quartier ?',
            text: `Voulez-vous vraiment supprimer le quartier "${neighborhoodName}" ? Cette action est irréversible.`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteNeighborhood', neighborhoodId);
            }
        });
    }
</script>
@endpush
