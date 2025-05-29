@props(['id' => 'activateModal', 'title' => 'Confirmation d\'activation', 'entity' => 'utilisateur'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir activer cet {{ $entity }} ?</p>
                <p>L'{{ $entity }} pourra à nouveau être utilisé dans l'application.</p>
                <input type="hidden" id="{{ $id }}Id" class="activate-item-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success confirm-activate-btn">Activer</button>
            </div>
        </div>
    </div>
</div>
