<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historique de la bouteille</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Code-barre:</strong> <span id="bottle-barcode">{{$bottle?->barcode}}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>État:</strong> <span id="bottle-status">{{ $bottle?->status?->label }}</span></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                          <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date et heure</th>
                                    <th>Centre de distribution</th>
                                    <th>Type</th>
                                    <th>Note</th>
                                    <th>Livreur</th>
                                    <th>Client</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                @forelse($bottleHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->movement_date)->format('d/m/Y \à H:i') }}</td>
                                        <td>{{ $history->distributionCenter?->name ?? '-' }}</td>
                                        <td>{{ $history->type->label }}</td>
                                        <td>{{ $history->notes }}</td>
                                        <td>{{ $history->deliveryPerson ? $history->deliveryPerson?->user?->first_name ." ". $history->deliveryPerson?->user?->last_name
                                                : "-"}}
                                        </td>
                                        <td>{{ $history->customer ? $history->customer?->first_name." ".$history->customer?->last_name : "-" }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun historique trouvé</td>
                                    </tr>
                                @endforelse
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