@extends('layout.master')
@section('title', 'Liste des utilisateurs')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/livewire-tables.css') }}">
    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/css/custom-datatable.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Liste des utilisateurs</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Utilisateurs
                            </span>
                        </a>
                    </li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Liste</a></li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('users.create') }}" class="btn btn-success">
                        <i class="iconoir-plus me-2"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>

        <div class="row ticket-app">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <livewire:users-table />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier un utilisateur</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form class="app-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" placeholder="Nom" id="last_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" placeholder="Prénom" id="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" placeholder="email@example.com"
                                    id="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" placeholder="690102030" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="point_vente" class="form-label">Fonction</label>
                                <select class="form-select" id="point_vente">
                                    <option value="Responsable de point de distribution">Responsable de point de
                                        distribution</option>
                                    <option value="Responsable Gaz">Responsable Gaz</option>
                                    <option value="Responsable informatique">Responsable informatique</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="point_vente" class="form-label">Point de distribution</label>
                                <select class="form-select" id="point_vente">
                                    <option value="Point A">Point A</option>
                                    <option value="Point B">Point B</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary">Modifier</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

    <script src="{{ asset('assets/vendor/moment/moment.min.js') }}"></script>

@endsection
