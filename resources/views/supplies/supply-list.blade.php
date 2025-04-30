@extends('layout.master')
@section('title', 'Liste des approvisionnements')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">

    <!-- Daterangepicker CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.css" rel="stylesheet" />
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" />

@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title"> liste des approvisionnements</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Approvisionnements</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste des approvisionnements</a>
                    </li>
                </ul>
            </div>
            <!-- Filter -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end ">
                    <a type="button" href="javascript!:" data-bs-toggle="collapse" data-bs-target="#collapseFilter" role="button"
                        aria-expanded="false" aria-controls="collapseFilter"
                        class="waves-effect btn text-info bg-white btn-md mb-2">
                        <i class="ti ti-chevron-down"></i> Filtrer
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Filter Options-->
        <div class="row m-1">
            <div class="col-12 collapse mb-4 p-0 bg-white" id="collapseFilter">
                <div class="card bg-white text-black">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="warehouse_filter" class="form-label"> Point de distribution : </label>
                                    <select id="warehouse_filter" name="warehouse" class="form-select">
                                        <option value="" selected>Tous</option>
                                        <option value="Point A">Point A</option>
                                        <option value="Point B">Point B</option>
                                        <option value="Point C">Point C</option>
                                        <option value="Point D">Point D</option>
                                        <option value="Point E">Point E</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="wfirstName2" class="form-label"> Date : </label>
                                    <div>
                                        <button type="button" class="btn btn-default"
                                            style="width: 100%; background-color: white; color: black; border: 1px solid lightgrey" id="daterange-btn">
                                            <span>
                                                <i class="fa fa-calendar"></i> Sélectionner une date
                                            </span>
                                            <i class="fa fa-caret-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card">
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
                                        <a href="{{ route('supplies.details', 3) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 4) }}">APR-1000</a></td>
                                    <td>Point Yaoundé 2</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>150</td>
                                    <td>LMN Gaz</td>
                                    <td>12 Avr,2025 10:15</td>
                                    <td><span class="badge text-outline-secondary">En attente</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 4) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 5) }}">APR-1001</a></td>
                                    <td>Point Bafoussam 3</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>300</td>
                                    <td>OPQ Gaz</td>
                                    <td>13 Avr,2025 11:45</td>
                                    <td><span class="badge text-outline-danger">Annulé</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 5) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 6) }}">APR-1002</a></td>
                                    <td>Point Douala 4</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>180</td>
                                    <td>RST Gaz</td>
                                    <td>14 Avr,2025 14:30</td>
                                    <td><span class="badge text-outline-success">Terminé</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 6) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 8) }}">APR-1004</a></td>
                                    <td>Point Yaoundé 6</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>250</td>
                                    <td>XYZ Gaz</td>
                                    <td>16 Avr,2025 08:30</td>
                                    <td><span class="badge text-outline-secondary">En attente</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 8) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 9) }}">APR-1005</a></td>
                                    <td>Point Douala 7</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>280</td>
                                    <td>LMN Gaz</td>
                                    <td>17 Avr,2025 14:45</td>
                                    <td><span class="badge text-outline-danger">Annulé</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 9) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('supplies.details', 10) }}">APR-1006</a></td>
                                    <td>Point Bafoussam 8</td>
                                    <td><span class="badge rounded-pill bg-light-secondary mb-1">Bouteille de 9kg</span>
                                        <span class="badge rounded-pill bg-light-secondary">Bouteille de 12kg</span></td>
                                    <td>190</td>
                                    <td>OPQ Gaz</td>
                                    <td>18 Avr,2025 12:15</td>
                                    <td><span class="badge text-outline-success">Terminé</span></td>
                                    <td>
                                        <a href="{{ route('supplies.details', 10) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                        </table>
                    </div>
                    <!-- table -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                                <input type="text" class="form-control" placeholder="email@example.com" id="email">
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
    <!--customizer-->
    <div id="customizer"></div>

    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.js"></script>
    <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- apexcharts js-->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

     <!-- Tooltips_popovers. Js -->
    <script src="{{ asset('assets/js/tooltips_popovers.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

    <!-- Dashboard js -->
    <script src="{{asset('assets/js/dashboard.js')}}"></script>

     <!-- select2 -->
     <script src="{{asset('assets/vendor/select/select2.min.js')}}"></script>

     <!--js-->
     <script src="{{asset('assets/js/select.js')}}"></script>

@endsection
