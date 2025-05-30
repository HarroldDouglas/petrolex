@extends('layout.master')
@section('title', 'Ticket')
@section('css')
    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Accueil</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Tableau de bord
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
                @livewire('components.filter-component')
            </div>
        </div>

        <!-- Ticket start -->
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
                                    <p class="f-s-16 mb-0">Commandes<br>en attente</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-clock-countdown f-s-45 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">185</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Commandes<br>livrées</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-check-circle f-s-45 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">185</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Commandes<br>annulées</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-x-circle f-s-45 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">185</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ticket table-->
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-header">
                        <h4 class="card-title">Les Commandes</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            @livewire('dashboard.dashboard-data-table')
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

    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

    <script src="{{ asset('assets/vendor/moment/moment.min.js') }}"></script>

@endsection