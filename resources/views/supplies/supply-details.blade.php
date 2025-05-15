@extends('layout.master')
@section('title', 'Détails de l\'approvisionnement')
@section('css')
    @livewireStyles
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row m-1">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="main-title">Détails de l'approvisionnement</h4>
                    <div>
                        <a href="{{ route('supplies.edit', ['supply_id' => 1]) }}" class="btn btn-primary b-r-22">
                            <i class="ti ti-edit me-1"></i>Éditer
                        </a>
                        <button type="button" class="btn btn-light-danger b-r-22" data-bs-toggle="modal"
                            data-bs-target="#deleteSupplyModal">
                            <i class="ti ti-trash me-1"></i>Supprimer
                        </button>
                    </div>
                </div>
                <ul class="app-line-breadcrumbs mb-3">
                    <li>
                        <a href="#" class="f-s-14 f-w-500">
                            <i class="ph-duotone ph-stack f-s-16"></i> Apps
                        </a>
                    </li>
                    <li><a href="#" class="f-s-14 f-w-500">Approvisionnements</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Détails</a></li>
                </ul>
            </div>
        </div>

        <!-- Supply Detail Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-white text-black p-4">
                    <div class="card-body">
                        <div class="app-product-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">Titre</h6>
                                    <p class="mb-0 fs-5">Approvisionnement du 13 Avril</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted mb-1">Date</h6>
                                    <p class="mb-0 fs-5">{{ date('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <h6 class="text-muted mb-1">Description</h6>
                                    <p class="mb-0">Approvisionnement de bouteilles de gaz et accessoires pour le point
                                        Bafoussam 3</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Livewire Component -->
        @livewire('supply-products')
    </div>

    <div class="modal fade" id="deleteSupplyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet approvisionnement? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('supplies.delete', ['supply_id' => 1]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @livewireScripts
    <script>
        // Script pour gérer le modal de suppression
        window.addEventListener('showDeleteModal', event => {
            $('#confirmDeleteModal').modal('show');
        });

        window.addEventListener('hideDeleteModal', event => {
            $('#confirmDeleteModal').modal('hide');
        });
    </script>
@endsection
