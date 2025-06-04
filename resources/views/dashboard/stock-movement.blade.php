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
            <div class="col-8 p-0">
                <h4 class="main-title">Mouvement de stock</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Mouvements
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

        <!-- Filter Options-->
        <div class="row m-1">
            <div class="col-12 collapse mb-4 p-0" id="collapseFilter">
                @livewire('components.filter-component', ['scope' => 'stockmovement']){{--['period' => $period, 'startDate' => $startDate, 'endDate' => $endDate]--}}
            </div>
        </div>

        <!-- Stock Movement Stats -->
        <div class="row ticket-app">
            @livewire('dashboard.stats-stock-movement')
            {{--<div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des Entrées<br>(Bouteilles Pleines)</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-arrow-circle-down f-s-45 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">{{ $stats['total_entries'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des Bouteilles <br>Pleines Vendues</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-arrow-circle-up f-s-45 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">{{ $stats['total_exits'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des Recharges<br> ___</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-arrows-left-right f-s-45 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">{{ $stats['total_exchanges'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Stock Actuel<br>(Bouteilles pleines / vides)</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="ph-bold ph-package f-s-45 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">{{ $stats['current_stock'] }} / 2000</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>--}}

            <!-- Movements Table -->
            <div class="col-12">
                <div class="card card-border">
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
                                        <th>Destination</th>
                                        <th>Utilisateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                    <tr>
                                        <td>{{ $movement['date'] }}</td>
                                        <td><span class="badge text-outline-{{ $movement['type_class'] }}">{{ $movement['type'] }}</span></td>
                                        <td><span class="badge rounded-pill bg-light-secondary mb-1">{{ $movement['products'] }}</span></td>
                                        <td>{{ $movement['destination'] }}</td>
                                        <td>{{ $movement['user'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/ticket.js') }}"></script>
    <script src="{{ asset('assets/vendor/moment/moment.min.js') }}"></script>
@endsection
