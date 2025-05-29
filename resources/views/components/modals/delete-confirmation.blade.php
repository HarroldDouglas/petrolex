@props(['id' => 'deleteModal', 'title' => 'Confirmation de suppression', 'entity' => 'élément'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer {{ $entity }} <strong id="{{ $id }}Name"></strong> ?</p>
                <p>Cette action est irréversible.</p>
                <div class="alert alert-warning" role="alert">
                    <p>Pour confirmer, veuillez saisir <strong>supprimer</strong> ci-dessous.</p>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control delete-confirm-input" 
                        placeholder="Tapez 'supprimer' pour confirmer">
                    <input type="hidden" id="{{ $id }}Id" class="delete-item-id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger confirm-delete-btn" disabled>Supprimer</button>
            </div>
        </div>
    </div>
</div>
