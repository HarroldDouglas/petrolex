@extends('layout.master')
@section('title', 'Liste des utilisateurs')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/livewire-tables.css') }}">
    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Liste des utilisateurs</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Utilisateurs
                            </span>
                        </a>
                    </li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Liste</a></li>
                </ul>
            </div>
            <div class="col-4 p-0">
                @can('users.create')
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('users.create') }}" class="btn btn-success">
                            <i class="iconoir-plus me-2"></i> Nouveau
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <div class="row ticket-app">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body pt-1 px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <livewire:users-table />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Use reusable components for confirmation modals -->
    <x-modals.delete-confirmation id="deleteModal" entity="l'utilisateur" />
    <x-modals.deactivate-confirmation id="deactivateModal" entity="utilisateur" />
    <x-modals.activate-confirmation id="activateModal" entity="utilisateur" />

@endsection

@section('script')
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

    <script src="{{ asset('assets/vendor/moment/moment.min.js') }}"></script>

    <!-- Include reusable modal scripts -->
    @include('components.modals.modal-scripts')
    
    <script>
        $(document).ready(function() {
            // Any page-specific script overrides or extensions can go here
            
            // For example, if you want to do something specific after deleting a user
            $('.confirm-delete-btn').click(function() {
                // Custom logic before reload
                // For example: show a success message
                // ...

                window.location.reload();
            });
        });
    </script>
@endsection
