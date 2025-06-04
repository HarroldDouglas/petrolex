@props(['id' => 'historyModal', 'title' => 'Historique de la bouteille'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">{{ $title}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Code-barre:</strong> <span id="bottle-barcode"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong> <span id="bottle-type"></span></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                          <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date et heure</th>
                                    <th>Description</th>
                                    <th>État précédent</th>
                                    <th>État actuel</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                <!-- History records will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>