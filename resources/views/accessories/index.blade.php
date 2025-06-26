@extends('layout.master')
@section('title', 'Liste des accessoires')
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
                <h4 class="main-title"> Liste des accessoires</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Accessoires
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-success-dark">
                    <a type="button" href="{{ route('accessories.create') }}"
                        class="waves-effect btn btn-success btn-md mb-2">
                        <i class="ti ti-plus"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0"> <span class="text-primary-dark">{{$accessoryStats->activeCount}}</span> Accessoires Actifs</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-check-circle f-s-45 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0"> <span class="text-danger-dark">{{$accessoryStats->inactiveCount}}</span> Accessoires Inactifs</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-minus-square-dashed f-s-45"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <livewire:accessory.accessory-data-table />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-sweet-alert-notification-listener />

@section('script')
    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/ticket.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="{{ asset('assets/js/custom/sweet_alert_confirm_actions.js') }}" defer></script>
@endsection
@endsection
