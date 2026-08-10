@extends('layout.master')
@section('title', 'Logs des applications mobiles')
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
                <h4 class="main-title">Logs des applications mobiles</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-bug f-s-16"></i> Logs mobiles
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste</a>
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
                            @livewire('mobile-app-log.mobile-app-log-data-table')
                        </div>
                        <!-- table -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<x-sweet-alert-notification-listener />
@section('script')
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom/sweet_alert_confirm_actions.js') }}" defer></script>
@endsection
