@extends('layout.master')
@section('title', 'Suivi et rapports')
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
            <div class="col-8 p-0">
                <h4 class="main-title">Suivi et rapports</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Rapport financier
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

        <!-- Financial report start -->
        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Chiffre d'affaires<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-currency-circle-dollar f-s-25 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">15.000.000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Dépenses<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-currency-circle-dollar f-s-25 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">8.000.000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Bénéfice<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-credit-card f-s-25 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">7.000.000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Dettes<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-bag f-s-25 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">2.000.000</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fiancial report table-->
            <div class="col-12">
                <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Rapport financier</h4>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive app-scroll app-datatable-default">
                        <table class="w-100 display ticket-app-table" id="ticketdatatable">
                            <thead>
                                <tr>
                                    <th class="w-15">Date</th>
                                    <th class="w-15">Type</th>
                                    <th class="w-30">Montant (en CFA)</th>
                                    <th class="w-20">Utilisateur</th>
                                    <th class="w-20 text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>01/05/2025</td>
                                    <td>Vente</td>
                                    <td>1.000.000</td>
                                    <td>Nguefack Jean</td>
                                    <td class="text-center"><span class="badge text-light-success">Terminé</span></td>
                                </tr>
                                <tr>
                                    <td>03/05/2025</td>
                                    <td>Achat</td>
                                    <td>500.000</td>
                                    <td>Eyoum Claire</td>
                                    <td class="text-center"><span class="badge text-light-success">Terminé</span></td>
                                </tr>
                                <tr>
                                    <td>05/05/2025</td>
                                    <td>Vente</td>
                                    <td>750.000</td>
                                    <td>Ndongmo Roger</td>
                                    <td class="text-center"><span class="badge text-light-warning">En attente</span></td>
                                </tr>
                                <tr>
                                    <td>07/05/2025</td>
                                    <td>Achat</td>
                                    <td>300.000</td>
                                    <td>Mbarga Elise</td>
                                    <td class="text-center"><span class="badge text-light-success">Terminé</span></td>
                                </tr>
                                <tr>
                                    <td>10/05/2025</td>
                                    <td>Vente</td>
                                    <td>1.200.000</td>
                                    <td>Fouda Mireille</td>
                                    <td class="text-center"><span class="badge text-light-warning">En attente</span></td>
                                </tr>
                                <tr>
                                    <td>12/05/2025</td>
                                    <td>Vente</td>
                                    <td>950.000</td>
                                    <td>Tchatchoua Paul</td>
                                    <td class="text-center"><span class="badge text-light-success">Terminé</span></td>
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
                            <h4 class="card-title me-auto">Revenus et dépenses</h4>
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
                    <div class="card-body">
                        <div>
                            <div id="revenue-expenses-chart"></div>
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

     <!-- Select js -->
     <script src="{{asset('assets/js/select.js')}}"></script>

     <!-- custom js -->
     <script src="{{asset('assets/js/pages/financial-report.js')}}"></script>

@endsection
