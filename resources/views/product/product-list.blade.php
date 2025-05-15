@extends('layout.master')
@section('title', 'Liste des produits')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title"> Liste des produits</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Produits
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste</a>
                    </li>
                </ul>
            </div>
            <!-- Filter -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-success-dark">
                    <a type="button" href="{{ route('products.create') }}"
                        class="waves-effect btn btn-success btn-md mb-2">
                        <i class="ti ti-plus"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-border ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0"> <span class="text-primary-dark">3</span> Produits Actifs</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-check-circle f-s-45 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0"> <span class="text-danger-dark">3</span> Produits Inactifs</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-minus-square-dashed f-s-45"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Nom du produit</th>
                                        <th>Prix</th>
                                        <th>Date d'enregistrement</th>
                                        <th>État</th>
                                        <th>En Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product['name'] }}</td>
                                            <td>{{ number_format((float) $product['price'], 0, ',', ' ') }} FCFA</td>
                                            <td>{{ date('d/m/Y H:i', strtotime($product['created_at'])) }}</td>
                                            <td>
                                                @php
                                                    $badgeClass =
                                                        $product['status'] === 'Actif'
                                                            ? 'text-bg-success'
                                                            : 'text-bg-danger';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $product['status'] }}</span>
                                            </td>
                                            <td>
                                                @if ($product['stock'] > 0)
                                                    <span class="badge text-bg-success">{{ $product['stock'] }}</span>
                                                @else
                                                    <span class="badge text-bg-danger">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group dropdown-icon-none">
                                                    <button
                                                        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                        type="button" id="dropdownMenuButton{{ $product['id'] }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $product['id'] }}">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('products.details', $product['id']) }}">
                                                                <i class="ti ti-eye text-primary me-2"></i> Détails
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('products.edit', $product['id']) }}">
                                                                <i class="iconoir-edit text-info me-2"></i> Modifier
                                                            </a>
                                                        </li>
                                                        @if ($product['status'] === 'Actif')
                                                            <li>
                                                                <a class="dropdown-item change-status" href="#"
                                                                    data-id="{{ $product['id'] }}"
                                                                    data-action="deactivate">
                                                                    <i class="iconoir-xmark-circle text-warning me-2"></i>
                                                                    Désactiver
                                                                </a>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <a class="dropdown-item change-status" href="#"
                                                                    data-id="{{ $product['id'] }}" data-action="activate">
                                                                    <i class="iconoir-check-circle text-success me-2"></i>
                                                                    Activer
                                                                </a>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a class="dropdown-item delete-product" href="#"
                                                                data-id="{{ $product['id'] }}">
                                                                <i class="iconoir-trash text-danger me-2"></i> Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

@section('script')
    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/ticket.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Change status dropdown item click handler
            $('.change-status').click(function(e) {
                e.preventDefault();
                const productId = $(this).data('id');
                const action = $(this).data('action');
                const statusText = action === 'activate' ? 'activer' : 'désactiver';

                // Update modal content
                $('#statusConfirmText').text(`Êtes-vous sûr de vouloir ${statusText} ce produit ?`);
                $('#statusProductId').val(productId);
                $('#statusAction').val(action);

                // Show the modal
                $('#statusModal').modal('show');
            });

            // Status confirmation button click handler
            $('#confirmStatusBtn').click(function() {
                const productId = $('#statusProductId').val();
                const action = $('#statusAction').val();
                const statusText = action === 'activate' ? 'activé' : 'désactivé';

                // Close modal
                $('#statusModal').modal('hide');

                // Show success toast notification
                Toastify({
                    text: `Le produit a été ${statusText} avec succès.`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    stopOnFocus: true
                }).showToast();

                // In a real application, you would make an AJAX request here
                // For the mockup, we can simulate a page reload after a delay
                setTimeout(() => {
                    // Uncomment the line below in a real application
                    // location.reload();
                }, 1000);
            });

            // Delete product dropdown item click handler
            $('.delete-product').click(function(e) {
                e.preventDefault();
                const productId = $(this).data('id');

                // Set the product ID in the modal
                $('#deleteProductId').val(productId);

                // Reset the confirmation text field
                $('#deleteConfirmText').val('');
                $('#confirmDeleteBtn').prop('disabled', true);

                // Show the modal
                $('#deleteModal').modal('show');
            });

            // Enable/disable delete button based on confirmation text
            $('#deleteConfirmText').on('input', function() {
                $('#confirmDeleteBtn').prop('disabled', $(this).val().toLowerCase() !== 'supprimer');
            });

            // Delete confirmation button click handler
            $('#confirmDeleteBtn').click(function() {
                const productId = $('#deleteProductId').val();

                // Close modal
                $('#deleteModal').modal('hide');

                // Show success toast notification
                Toastify({
                    text: "Le produit a été supprimé avec succès.",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                    stopOnFocus: true
                }).showToast();

                // In a real application, you would make an AJAX request here
                // For the mockup, we can simulate a page reload after a delay
                setTimeout(() => {
                    // Uncomment the line below in a real application
                    // location.reload();
                }, 1000);
            });
        });
    </script>
    <!-- Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection
@endsection
