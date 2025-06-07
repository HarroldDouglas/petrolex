@extends('layout.master')
@section('title', 'Liste des centres dedistribution')
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
                <h4 class="main-title"> liste des centres de Distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Centre de distribution
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Ajouter</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-success-dark">
                    <a type="button" href="{{ route('distribution-centers.create') }}"
                        class="waves-effect btn btn-success btn-md mb-2">
                        <i class="ti ti-plus"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            @livewire('distribution-center.distribution-center-data-table')
                        </div>
                        <!-- table -->
                    </div>
                </div>
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
