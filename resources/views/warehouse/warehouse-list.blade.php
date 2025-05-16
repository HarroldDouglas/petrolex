@extends('layout.master')
@section('title', 'Liste des points de distribution')
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
                <h4 class="main-title"> liste des points de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Points de distribution</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste des points de distribution</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th class="text-start">Nom</th>
                                        <th>Ville</th>
                                        <th>Adresse</th>
                                        <th>Téléphone</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point A
                                        </td>
                                        <td>Yaoundé</td>
                                        <td>Bastos, derrière l'usine</td>
                                        <td>+237 690 123 456</td>
                                        <td>10 Avr,2024 08:30</td>
                                        <td><span class="badge text-light-info">ACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 2) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="2">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="2">
                                                            <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point B
                                        </td>
                                        <td>Douala</td>
                                        <td>Akwa, près de la banque</td>
                                        <td>+237 691 234 567</td>
                                        <td>11 Avr,2024 09:45</td>
                                        <td><span class="badge text-light-danger">INACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 3) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="3">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="3">
                                                            <i class="ti ti-check text-success me-2"></i> Activer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point C
                                        </td>
                                        <td>Yaoundé</td>
                                        <td>Mokolo, derrière le marché</td>
                                        <td>+237 692 345 678</td>
                                        <td>12 Avr,2024 11:00</td>
                                        <td><span class="badge text-light-info">ACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 4) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="4">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="4">
                                                            <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point D
                                        </td>
                                        <td>Douala</td>
                                        <td>Bonanjo, près du port</td>
                                        <td>+237 693 456 789</td>
                                        <td>13 Avr,2024 14:15</td>
                                        <td><span class="badge text-light-danger">INACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 5) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="5">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="5">
                                                            <i class="ti ti-check text-success me-2"></i> Activer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point E
                                        </td>
                                        <td>Yaoundé</td>
                                        <td>Nlongkak, près de l'école</td>
                                        <td>+237 694 567 890</td>
                                        <td>14 Avr,2024 10:30</td>
                                        <td><span class="badge text-light-info">ACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton6" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 6) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="6">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="6">
                                                            <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point F
                                        </td>
                                        <td>Douala</td>
                                        <td>Bonamoussadi, près du supermarché</td>
                                        <td>+237 695 678 901</td>
                                        <td>15 Avr,2024 16:45</td>
                                        <td><span class="badge text-light-danger">INACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton7" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 7) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="7">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="7">
                                                            <i class="ti ti-check text-success me-2"></i> Activer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point G
                                        </td>
                                        <td>Yaoundé</td>
                                        <td>Essos, près de la gare</td>
                                        <td>+237 696 789 012</td>
                                        <td>16 Avr,2024 09:00</td>
                                        <td><span class="badge text-light-info">ACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton8" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton8">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 8) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="8">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="8">
                                                            <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point H
                                        </td>
                                        <td>Douala</td>
                                        <td>Bonaberi, près du pont</td>
                                        <td>+237 697 890 123</td>
                                        <td>17 Avr,2024 15:30</td>
                                        <td><span class="badge text-light-danger">INACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton9" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton9">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 9) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="9">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="9">
                                                            <i class="ti ti-check text-success me-2"></i> Activer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-flex align-items-center gap-2">
                                            Point I
                                        </td>
                                        <td>Yaoundé</td>
                                        <td>Ekounou, près de l'église</td>
                                        <td>+237 698 901 234</td>
                                        <td>18 Avr,2024 13:20</td>
                                        <td><span class="badge text-light-info">ACTIF</span></td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button
                                                    class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                    type="button" id="dropdownMenuButton10" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton10">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('warehouses.details', 10) }}">
                                                            <i class="ti ti-eye text-primary me-2"></i> Détail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#editWarehouseModal" data-warehouse-id="10">
                                                            <i class="ti ti-edit text-success me-2"></i> Editer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-status-btn" href="#"
                                                            data-warehouse-id="10">
                                                            <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- table -->
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
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier un point de distribution</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form class="app-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" placeholder="Nom" id="name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" placeholder="Prénom" id="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Adresse</label>
                                <input type="text" class="form-control" placeholder="Adresse" id="adress">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" placeholder="690102030" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" placeholder="email@example.com"
                                    id="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="point_vente" class="form-label">Code postal</label>
                                <input type="text" class="form-control" placeholder="Code postal" id="postal_code">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" placeholder="Latitude" id="latitude">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="Longitiude" class="form-label">Longitiude</label>
                                <input type="text" class="form-control" placeholder="Longitiude" id="Longitiude">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                                <input type="text" class="form-control" placeholder="storage_capacity"
                                    id="storage_capacity">
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
