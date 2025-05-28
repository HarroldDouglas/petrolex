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
                    <div class="card-body p-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Nom complet</th>
                                        <th>Centre de distribution</th>
                                        <th>Téléphone</th>
                                        <th>Fonction</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= 50; $i++)
                                        <tr>
                                            <td class="d-flex align-items-center gap-2">
                                                <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                                    <img src="{{ asset('../assets/images/avtar/' . (($i % 9) + 1) . '.png') }}"
                                                        alt="" class="img-fluid">
                                                </div>
                                                <span class="title-text mb-0">Utilisateur {{ $i }}</span>
                                            </td>
                                            <td>Point {{ chr(64 + (($i % 26) + 1)) }}</td>
                                            <td>+237 {{ str_pad($i, 9, '6') }}</td>
                                            <td>Responsable centre de distribution</td>
                                            <td>{{ date('d M,Y', strtotime("-$i days")) }}</td>
                                            <td><span
                                                    class="badge text-outline-{{ $i % 2 ? 'success' : 'danger' }}">{{ $i % 2 ? 'Actif' : 'Inactif' }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group dropdown-icon-none">
                                                    <button
                                                        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                        type="button" id="dropdownMenuButton{{ $i }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $i }}">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('users.details', $i) }}">
                                                                <i class="ti ti-eye text-primary me-2"></i> Détail
                                                            </a>
                                                        </li>
                                                        @can('users.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                                data-bs-target="#editUserModal"
                                                                data-user-id="{{ $i }}">
                                                                <i class="ti ti-edit text-success me-2"></i> Editer
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @if ($i % 2)
                                                            <li>
                                                                <a class="dropdown-item toggle-status-btn" href="#"
                                                                    data-user-id="{{ $i }}"
                                                                    data-status="active"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deactivateModal">
                                                                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                                                                </a>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <a class="dropdown-item toggle-status-btn" href="#"
                                                                    data-user-id="{{ $i }}"
                                                                    data-status="inactive"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#activateModal">
                                                                    <i class="ti ti-check text-success me-2"></i> Activer
                                                                </a>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteModal"
                                                                data-user-id="{{ $i }}"
                                                                data-user-name="Utilisateur {{ $i }}">
                                                                <i class="ti ti-trash text-danger me-2"></i> Supprimer
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
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
