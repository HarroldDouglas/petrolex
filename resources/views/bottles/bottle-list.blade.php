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
                            @livewire('bottle.bottle-data-table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <x-modals.bottle-history id="historyModal" title="Historique de la bouteille" />
    

    @endsection

    @section('script')
        <!-- slick-file -->
        <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/js/ticket.js') }}"></script>
        <script src="{{ asset('assets/js/dashboard.js') }}"></script>
        <script>
            $(document).ready(function() {
            // Function to bind action handlers
            function bindActionHandlers() {
                // View history button click handler
                $(document).off('click', '.view-history').on('click', '.view-history', function(e) {
                    e.preventDefault();
                    const bottleId = $(this).data('id');
                    
                    // Show loading state
                    $('#bottle-barcode').text('Chargement...');
                    $('#bottle-type').text('Chargement...');
                    $('#history-table-body').html('<tr><td colspan="4" class="text-center">Chargement...</td></tr>');

                    // Fetch bottle history via AJAX
                    $.ajax({
                        url: "{{ route('bottles.history', ':id') }}".replace(':id', bottleId),
                        type: 'GET',
                        success: function(response) {
                            // Fill modal with bottle data
                            $('#bottle-barcode').text(response.bottle.barcode);
                            $('#bottle-type').text(response.bottle.type);

                            // Clear existing history records
                            $('#history-table-body').empty();

                            // Add history records
                            if (response.history && response.history.length > 0) {
                                response.history.forEach(function(record) {
                                    const date = new Date(record.moved_at).toLocaleDateString('fr-FR', {
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
                                            <td>${record.notes}</td>
                                            <td>${prevStatus}</td>
                                            <td>${response.bottle.status}</td>
                                        </tr>
                                    `);
                                });
                            } else {
                                $('#history-table-body').append('<tr><td colspan="4" class="text-center">Aucun historique disponible</td></tr>');
                            }
                        },
                        error: function() {
                            $('#history-table-body').html('<tr><td colspan="4" class="text-center text-danger">Erreur lors du chargement de l\'historique</td></tr>');
                        }
                    });
                });

                // Mark as lost button click handler
                $(document).off('click', '.mark-lost').on('click', '.mark-lost', function(e) {
                    e.preventDefault();
                    const bottleId = $(this).data('id');
                    
                    if (confirm('Êtes-vous sûr de vouloir déclarer cette bouteille comme perdue?')) {
                        $.ajax({
                            url: "{{ route('bottles.mark-lost', ':id') }}".replace(':id', bottleId),
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                alert('La bouteille a été marquée comme perdue.');
                                
                                // Use the correct method for Laravel Livewire Tables
                                refreshLivewireTable(bottleId);
                            },
                            error: function() {
                                alert('Erreur lors de la mise à jour du statut.');
                            }
                        });
                    }
                });

                // Mark as found button click handler
                $(document).off('click', '.mark-found').on('click', '.mark-found', function(e) {
                    e.preventDefault();
                    const bottleId = $(this).data('id');
                    
                    if (confirm('Êtes-vous sûr de vouloir marquer cette bouteille comme retrouvée?')) {
                        $.ajax({
                            url: "{{ route('bottles.mark-found', ':id') }}".replace(':id', bottleId),
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                alert('La bouteille a été marquée comme retrouvée.');
                                
                                // Use the correct method for Laravel Livewire Tables
                                refreshLivewireTable(bottleId);
                            },
                            error: function() {
                                alert('Erreur lors de la mise à jour du statut.');
                            }
                        });
                    }
                });
            }

            // Function to refresh Livewire table - specifically for Laravel Livewire Tables
            function refreshLivewireTable(bottleId = null) {
                
                if (typeof window.Livewire !== 'undefined') {
                    if (window.Livewire.dispatch) {
                        window.Livewire.dispatch('refreshDatatable');
                        window.Livewire.dispatch('bottleStatusUpdated', { bottleId: bottleId });
                    }
                }
               
            }

            // Initial binding
            bindActionHandlers();

            document.addEventListener('livewire:load', function () {
                console.log('Livewire loaded');
                bindActionHandlers();
            });

            document.addEventListener('livewire:navigated', function () {
                console.log('Livewire navigated');
                bindActionHandlers();
            });

            // Listen for custom refresh events
            window.addEventListener('refreshDatatable', function() {
                console.log('Custom refresh event received');
                bindActionHandlers();
            });

            document.addEventListener('livewire:update', function() {
                console.log('Livewire component updated');
                bindActionHandlers();
            });

            window.addEventListener('refresh-datatable', function() {
                console.log('Laravel Livewire Tables refresh event');
                bindActionHandlers();
            });
        });           
        </script>
    @endsection
