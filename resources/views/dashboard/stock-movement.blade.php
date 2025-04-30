@extends('layout.master')
@section('title', 'Mouvement de stock')
@section('css')
    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

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
            <div class="col-8  p-0">
                <h4 class="main-title">Suivi et Rapports</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Mouvement de stock
                            </span>
                        </a>
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

        <!-- Stock 1 Sales start -->
        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des Entrées<br>(Bouteille)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-arrow-circle-down f-s-25 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">4500</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des Sorties<br>(Bouteille)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-arrow-circle-up f-s-25 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">2000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Mouvement de <br>Stock Net</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-arrows-left-right f-s-25 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">2500</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Taux d’occupation <br>de l’entrepôt</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-warehouse f-s-25 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">88%</h3>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Stock & Sales table-->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Mouvement de stock</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Produits</th>
                                        <th>Quantité</th>
                                        <th>Origine</th>
                                        <th>Destination</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket_key_body">
                                    <tr>
                                        <td>22/04/2025 - 10:14</td>
                                        <td>Entrée</td>
                                        <td>Bouteille 9kg</td>
                                        <td>+100</td>
                                        <td>Achat fournisseur</td>
                                        <td>Point YDE A</td>
                                        <td>Ntang Luc</td>
                                    </tr>
                                    <tr>
                                        <td>22/04/2025 - 11:30</td>
                                        <td>Sortie</td>
                                        <td>Bouteille 6kg</td>
                                        <td>-50</td>
                                        <td>Point YDE A</td>
                                        <td>Client final</td>
                                        <td>Mbarga Elise</td>
                                    </tr>
                                    <tr>
                                        <td>22/04/2025 - 14:45</td>
                                        <td>Transfert</td>
                                        <td>Bouteille 12kg</td>
                                        <td>-30/+30</td>
                                        <td>Point YDE A</td>
                                        <td>Point DLA B</td>
                                        <td>Kamga Lionel</td>
                                    </tr>
                                    <tr>
                                        <td>22/04/2025 - 16:00</td>
                                        <td>Entrée</td>
                                        <td>Bouteille 6kg</td>
                                        <td>+80</td>
                                        <td>Retour client</td>
                                        <td>Point DLA B</td>
                                        <td>Ndongo Carine</td>
                                    </tr>
                                    <tr>
                                        <td>22/04/2025 - 17:15</td>
                                        <td>Sortie</td>
                                        <td>Bouteille 12kg</td>
                                        <td>-60</td>
                                        <td>Point DLA B</td>
                                        <td>Client final</td>
                                        <td>Ekani Paul</td>
                                    </tr>
                                    <tr>
                                        <td>22/04/2025 - 18:30</td>
                                        <td>Transfert</td>
                                        <td>Bouteille 9kg</td>
                                        <td>-20/+20</td>
                                        <td>Point DLA B</td>
                                        <td>Point YDE A</td>
                                        <td>Fouda Mireille</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <div class="col-md-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title me-auto">Mouvement de stock</h4>
                            <div class="dropdown">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="chartType" data-bs-toggle="dropdown" aria-expanded="false">
                                    Graphe
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="chartType">
                                    <li><a class="dropdown-item" href="javascript:;" data-type="column">Bande</a></li>
                                    <li><a class="dropdown-item" href="javascript:;" data-type="Pie">Camembert</a></li>
                                    <li><a class="dropdown-item" href="javascript:;" data-type="line">Ligne</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div>
                            <div id="basic-colum"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ticket table end -->
        </div>
        <!-- Ticket end -->
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

     <!--js-->
     <script src="{{asset('assets/js/pages/stock-movement.js')}}"></script>

@endsection
