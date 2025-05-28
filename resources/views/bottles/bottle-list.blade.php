@extends('layout.master')
@section('title', 'Liste des bouteilles')
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
                <h4 class="main-title"> Liste des bouteilles</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Bouteilles
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste des bouteilles</a>
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

        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">En stock</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-fast-arrow-down-square f-s-45 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">285</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">En cours de<br>livraison</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-fast-arrow-right-square f-s-45 text-warning"></i>
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
                                    <p class="f-s-16 mb-0">Vendus</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-shopping-bag-arrow-up f-s-45 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">520</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Perdus</p>
                                    <div class="h-40 w-40 d-flex-center">
                                        <i class="iconoir-file-not-found f-s-45 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">15</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Code-barre</th>
                                        <th>Type de bouteille</th>
                                        <th>Date d'enregistrement</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bottles as $bottle)
                                        <tr>
                                            <td>{{ $bottle['barcode'] }}</td>
                                            <td>{{ $bottle['type'] }}</td>
                                            <td>{{ date('d/m/Y H:i', strtotime($bottle['created_at'])) }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = 'text-bg-success';
                                                    if ($bottle['status'] === 'Vendu') {
                                                        $badgeClass = 'text-bg-info';
                                                    }
                                                    if ($bottle['status'] === 'Perdu') {
                                                        $badgeClass = 'text-bg-danger';
                                                    }
                                                    if ($bottle['status'] === 'En cours de livraison') {
                                                        $badgeClass = 'text-bg-warning';
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $bottle['status'] }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group dropdown-icon-none">
                                                    <button
                                                        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
                                                        type="button" id="dropdownMenuButton{{ $bottle['id'] }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $bottle['id'] }}">
                                                        <li>
                                                            <a class="dropdown-item view-history" href="#"
                                                                data-id="{{ $bottle['id'] }}" data-bs-toggle="modal"
                                                                data-bs-target="#historyModal">
                                                                <i class="iconoir-clock-rotate-right text-primary me-2"></i>
                                                                Historique
                                                            </a>
                                                        </li>
                                                        @if ($bottle['status'] !== 'Perdu')
                                                            <li>
                                                                <a class="dropdown-item mark-lost" href="#"
                                                                    data-id="{{ $bottle['id'] }}">
                                                                    <i
                                                                        class="iconoir-chat-bubble-question text-danger me-2"></i>
                                                                    Déclarer perdu
                                                                </a>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <a class="dropdown-item mark-found" href="#"
                                                                    data-id="{{ $bottle['id'] }}">
                                                                    <i class="iconoir-circle-spark text-success me-2"></i>
                                                                    Marquer retrouvée
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
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

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historique de la bouteille</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Code-barre:</strong> <span id="bottle-barcode"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong> <span id="bottle-type"></span></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date et heure</th>
                                    <th>Description</th>
                                    <th>État précédent</th>
                                    <th>État actuel</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                <!-- History records will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier un utilisateur</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form class="app-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" placeholder="Nom" id="last_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" placeholder="Prénom" id="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" placeholder="email@example.com"
                                    id="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" placeholder="690102030" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="point_vente" class="form-label">Centre de distribution</label>
                                <select class="form-select" id="point_vente">
                                    <option value="Point A">Point A</option>
                                    <option value="Point B">Point B</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary">Modifier</button>
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
        <script>
            $(document).ready(function() {
                // View history button click handler
                $('.view-history').click(function() {
                    const bottleId = $(this).data('id');

                    // Find bottle information
                    const bottleData = @json($bottles).find(b => b.id == bottleId);

                    // Fill modal with bottle data
                    $('#bottle-barcode').text(bottleData.barcode);
                    $('#bottle-type').text(bottleData.type);

                    // Example history data - would typically come from an API
                    const history = [{
                            date: '2023-06-12 08:30:00',
                            description: 'Premier enregistrement',
                            previous_status: null,
                            current_status: 'En stock'
                        },
                        {
                            date: '2023-06-13 14:00:00',
                            description: 'En cours de livraison',
                            previous_status: 'En stock',
                            current_status: 'En cours de livraison'
                        },
                        {
                            date: '2023-06-13 15:00:00',
                            description: 'Livré chez le client',
                            previous_status: 'En cours de livraison',
                            current_status: 'Livré'
                        },
                        {
                            date: '2023-06-14 17:00:00',
                            description: 'Retourné au fournisseur',
                            previous_status: 'Livré',
                            current_status: 'En stock'
                        }
                    ];

                    // Clear existing history records
                    $('#history-table-body').empty();

                    // Add history records
                    history.forEach(function(record) {
                        const date = new Date(record.date).toLocaleDateString('fr-FR', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        const prevStatus = record.previous_status || '----';

                        $('#history-table-body').append(`
                        <tr>
                            <td>${date}</td>
                            <td>${record.description}</td>
                            <td>${prevStatus}</td>
                            <td>${record.current_status}</td>
                        </tr>
                    `);
                    });
                });

                // Mark as lost button click handler
                $('.mark-lost').click(function() {
                    if (confirm('Êtes-vous sûr de vouloir déclarer cette bouteille comme perdue?')) {
                        alert('La bouteille a été marquée comme perdue.');
                    }
                });

                // Mark as found button click handler
                $('.mark-found').click(function() {
                    if (confirm('Êtes-vous sûr de vouloir marquer cette bouteille comme retrouvée?')) {
                        alert('La bouteille a été marquée comme retrouvée.');
                    }
                });
            });
        </script>
    @endsection
