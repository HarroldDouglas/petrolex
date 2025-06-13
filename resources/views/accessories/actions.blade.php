<div class="btn-group dropdown-icon-none">
    <button
        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
        type="button" id="dropdownMenuButton{{ $product->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu"
        aria-labelledby="dropdownMenuButton{{ $product->id }}">
        <li>
            <a class="dropdown-item"
                href="{{ route('accessories.edit', $product->id) }}">
                <i class="iconoir-edit text-info me-2"></i> Modifier
            </a>
        </li>
        @if ($product->is_active)
            <li>
                <a class="dropdown-item change-status" href="#"
                    data-id="{{ $product->id }}"
                    data-action="deactivate">
                    <i class="iconoir-xmark-circle text-warning me-2"></i>
                    Désactiver
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item change-status" href="#"
                    data-id="{{ $product->id }}" data-action="activate">
                    <i class="iconoir-check-circle text-success me-2"></i>
                    Activer
                </a>
            </li>
        @endif
        <li>
            <a class="dropdown-item delete-product" href="#"
                data-id="{{ $product->id }}">
                <i class="iconoir-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation de suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.</p>
                <p>Pour confirmer, veuillez saisir <strong>supprimer</strong> ci-dessous :</p>
                <input type="text" class="form-control" id="deleteConfirmText" placeholder="Saisir 'supprimer'">
                <input type="hidden" id="deleteProductId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>Supprimer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Status Change Confirmation -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="statusConfirmText">Êtes-vous sûr de vouloir modifier le statut de ce produit ?</p>
                <input type="hidden" id="statusProductId">
                <input type="hidden" id="statusAction">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="confirmStatusBtn">Confirmer</button>
            </div>
        </div>
    </div>
</div>
