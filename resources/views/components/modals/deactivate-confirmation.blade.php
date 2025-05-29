@props(['id' => 'deactivateModal', 'title' => 'Confirmation de désactivation', 'entity' => 'utilisateur'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir désactiver cet {{ $entity }} ?</p>
                <p>L'{{ $entity }} ne pourra plus être utilisé, mais ses données seront conservées.</p>
                <input type="hidden" id="{{ $id }}Id" class="deactivate-item-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger confirm-deactivate-btn">Désactiver</button>
            </div>
        </div>
    </div>
</div>
