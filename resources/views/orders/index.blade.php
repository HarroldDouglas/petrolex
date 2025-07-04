@extends('layout.master')
@section('title', auth()->user()->role == \App\Enums\UserRole::CENTER_MANAGER() ? 'Mes Commandes' : 'Commandes')
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
                <h4 class="main-title">
                    {{ auth()->user()->role == \App\Enums\UserRole::CENTER_MANAGER() ? 'Mes Commandes' : 'Commandes' }}</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i>
                                {{ auth()->user()->role == \App\Enums\UserRole::CENTER_MANAGER() ? 'Mes Commandes' : 'Commandes' }}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Breadcrumb end -->

            <!-- Ticket start -->
            <div class="row ticket-app p-0">

                <!-- ticket table-->
                <div class="col-12">
                    <div class="card card-border">
                        <div class="card-header">
                            <h4 class="card-title">
                                {{ auth()->user()->role == \App\Enums\UserRole::CENTER_MANAGER() ? 'Mes Commandes' : 'Les Commandes' }}
                            </h4>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive app-scroll app-datatable-default">
                                @livewire('order.order-data-table')
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

        <!-- api js -->
        <script src="{{ asset('assets/js/ticket.js') }}"></script>

    @endsection
