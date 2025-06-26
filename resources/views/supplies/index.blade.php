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
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-success-dark">
                    <a type="button" href="{{ route('supplies.create') }}"
                        class="waves-effect btn btn-success btn-md mb-2">
                        <i class="ti ti-plus"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row ticket-app">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-header">
                        <h4 class="card-title">Les Approvisionnements</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            @livewire('supply.supplies-data-table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-sweet-alert-notification-listener />
@endsection

@section('script')
    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/ticket.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/custom/sweet_alert_confirm_actions.js') }}" defer></script>
@endsection
