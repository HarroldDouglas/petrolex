@extends('layout.master')
@section('title', 'Liste des approvisionnements')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title"> liste des approvisionnements</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Approvisionnements
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
                <div class="d-flex justify-content-end text-primary-dark">
                    <a type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" role="button"
                        aria-expanded="false" aria-controls="collapseFilter"
                        class="waves-effect btn text-primary-dark bg-white btn-md mb-2">
                        <i class="ti ti-chevron-down"></i> Filtrer
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Filter Options-->
        <div class="row m-1">
            <div class="col-12 collapse mb-4 p-0" id="collapseFilter">
                @livewire('dashboard.filter-component')
            </div>
        </div>

        <div class="row ticket-app">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th class="w-10">Reference</th>
                                        <th class="w-10">Point de distr.</th>
                                        <th class="w-20">Produits</th>
                                        <th class="w-5">Quantité</th>
                                        <th class="w-10">Fournisseur</th>
                                        <th class="w-10">Date</th>
                                        <th class="w-5">Status</th>
                                        <th class="w-30">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 1) }}">APR-999</a></td>
                                        <td>Point Douala 1</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>200</td>
                                        <td>XYZ Gaz</td>
                                        <td>11 Avr,2025 09:00</td>
                                        <td><span class="badge text-outline-warning">En cours</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 4) }}">APR-1000</a></td>
                                        <td>Point Yaoundé 2</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>150</td>
                                        <td>LMN Gaz</td>
                                        <td>12 Avr,2025 10:15</td>
                                        <td><span class="badge text-outline-secondary">En attente</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 5) }}">APR-1001</a></td>
                                        <td>Point Bafoussam 3</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>300</td>
                                        <td>OPQ Gaz</td>
                                        <td>13 Avr,2025 11:45</td>
                                        <td><span class="badge text-outline-danger">Annulé</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 6) }}">APR-1002</a></td>
                                        <td>Point Douala 4</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de
                                                9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>180</td>
                                        <td>RST Gaz</td>
                                        <td>14 Avr,2025 14:30</td>
                                        <td><span class="badge text-outline-success">Terminé</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 8) }}">APR-1004</a></td>
                                        <td>Point Yaoundé 6</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de
                                                9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>250</td>
                                        <td>XYZ Gaz</td>
                                        <td>16 Avr,2025 08:30</td>
                                        <td><span class="badge text-outline-secondary">En attente</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 9) }}">APR-1005</a></td>
                                        <td>Point Douala 7</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de
                                                9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>280</td>
                                        <td>LMN Gaz</td>
                                        <td>17 Avr,2025 14:45</td>
                                        <td><span class="badge text-outline-danger">Annulé</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('supplies.details', 10) }}">APR-1006</a></td>
                                        <td>Point Bafoussam 8</td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de
                                                9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span>
                                        </td>
                                        <td>190</td>
                                        <td>OPQ Gaz</td>
                                        <td>18 Avr,2025 12:15</td>
                                        <td><span class="badge text-outline-success">Terminé</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('supplies.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('supplies.edit', 3) }}">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#">
                                                            <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                            </table>
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

    @endsection

    @section('script')
        <!-- slick-file -->
        <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/js/ticket.js') }}"></script>
        <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    @endsection
