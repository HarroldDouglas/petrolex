@extends('layout.master')
@section('title', 'Détails du produit')
@section('css')
    <!-- Lightbox CSS for image gallery -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/lightbox/css/lightbox.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title">Détails du produit</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('accessories.index') }}" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Produits
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Détails du produit</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('accessories.edit', 1) }}" class="btn btn-success">
                        <i class="ti ti-edit"></i> Modifier
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#deleteConfirmModal">
                        <i class="ti ti-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Informations de base -->
                        <h5 class="mb-3">Informations de base</h5>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="mb-2">Bouteille de gaz 12kg</h4>
                                <p class="text-muted mb-4">
                                    Bouteille de gaz de 12kg pour usage domestique. Idéale pour la cuisine et le chauffage.
                                    Cette bouteille est conforme aux normes de sécurité en vigueur et offre une autonomie
                                    prolongée.
                                </p>
                            </div>
                        </div>

                        <!-- Images du produit -->
                        <h5 class="mb-3">Images du produit</h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <a href="{{ asset('assets/images/team/1.jpg') }}" data-lightbox="product-gallery"
                                            data-title="Bouteille de gaz 12kg">
                                            <img src="{{ asset('assets/images/team/1.jpg') }}" class="img-fluid rounded"
                                                alt="Product Image">
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ asset('assets/images/team/2.jpg') }}" data-lightbox="product-gallery"
                                            data-title="Bouteille de gaz 12kg">
                                            <img src="{{ asset('assets/images/team/2.jpg') }}" class="img-fluid rounded"
                                                alt="Product Image">
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="{{ asset('assets/images/team/3.jpg') }}" data-lightbox="product-gallery"
                                            data-title="Bouteille de gaz 12kg">
                                            <img src="{{ asset('assets/images/team/3.jpg') }}" class="img-fluid rounded"
                                                alt="Product Image">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variations du produit -->
                        <h5 class="mb-3">Variations du produit</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom de la variation</th>
                                        <th>Prix</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Bouteille vide</td>
                                        <td>{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format(7500) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Bouteille avec gaz</td>
                                        <td>{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format(15000) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Recharge seule</td>
                                        <td>{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format(8000) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.</p>
                    <p>Pour confirmer, veuillez saisir <strong>supprimer</strong> ci-dessous :</p>
                    <input type="text" class="form-control" id="confirmText" placeholder="Saisir 'supprimer'">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled>Supprimer</button>
                </div>
            </div>
        </div>
    </div>

@section('script')
    <!-- Lightbox JS for image gallery -->
    <script src="{{ asset('assets/vendor/lightbox/js/lightbox.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize lightbox
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true
            });

            // Handle delete confirmation
            $('#confirmText').on('input', function() {
                const confirmBtn = $('#confirmDeleteBtn');
                if ($(this).val().toLowerCase() === 'supprimer') {
                    confirmBtn.prop('disabled', false);
                } else {
                    confirmBtn.prop('disabled', true);
                }
            });

            $('#confirmDeleteBtn').click(function() {
                if ($('#confirmText').val().toLowerCase() === 'supprimer') {
                    alert('Produit supprimé avec succès.');
                    window.location.href = "{{ route('accessories.index') }}";
                }
            });
        });
    </script>
@endsection
@endsection
