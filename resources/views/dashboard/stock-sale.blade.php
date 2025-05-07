@extends('layout.master')
@section('title', 'Ticket')
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
            <div class="col-8 p-0">
                <h4 class="main-title">Suivi et Rapports</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Stock & Ventes
                            </span>
                        </a>
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

        <!-- Stock & Sales start -->
        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Chiffre d'affaires<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-currency-circle-dollar f-s-45 text-primary"></i>
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
                                    <p class="f-s-16 mb-0">Stock<br>disponible</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-package f-s-45 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">430</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Points de<br>distribution</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-map-pin f-s-45 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">05</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Seuil<br>minimal</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-warning-circle f-s-45 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">10</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock & Sales table-->
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-header">
                        <h4 class="card-title">Stock & Ventes</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Point de distrib.</th>
                                        <th>Ventes</th>
                                        <th>En stock</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket_key_body">
                                    <tr>
                                        <td>Point YDE 123</td>

                                        <td>2 350 000</td>
                                        <td>300</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point YDE 123</td>
                                        <td>2 350 000</td>
                                        <td>300</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point DLA 456</td>
                                        <td>3 100 000</td>
                                        <td>250</td>
                                        <td><span class="badge text-outline-danger">Inactif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point BAF 789</td>
                                        <td>1 750 000</td>
                                        <td>400</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point YDE 321</td>
                                        <td>2 500 000</td>
                                        <td>150</td>
                                        <td><span class="badge text-outline-danger">Inactif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point DLA 654</td>
                                        <td>3 200 000</td>
                                        <td>350</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point BAF 987</td>
                                        <td>1 900 000</td>
                                        <td>100</td>
                                        <td><span class="badge text-outline-danger">Inactif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point YDE 654</td>
                                        <td>2 700 000</td>
                                        <td>200</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point DLA 987</td>
                                        <td>3 500 000</td>
                                        <td>275</td>
                                        <td><span class="badge text-outline-danger">Inactif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point BAF 123</td>
                                        <td>2 050 000</td>
                                        <td>325</td>
                                        <td><span class="badge text-outline-success">Actif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Point YDE 789</td>
                                        <td>2 800 000</td>
                                        <td>180</td>
                                        <td><span class="badge text-outline-danger">Inactif</span></td>
                                    </tr>
                            </table>
                        </div>
                    </div>
                </div>

            <div class="col-md-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title me-auto">Rapport des ventes</h4>
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
                            <div id="order-sale-chart"></div>
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

     <!--Select js-->
     <script src="{{asset('assets/js/select.js')}}"></script>

     <!--Custom js -->
     <script src="{{asset('assets/js/pages/order-sale.js')}}"></script>

@endsection
