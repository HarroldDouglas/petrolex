@extends('layout.master')
@section('title', 'Types de bouteilles')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title">Types de bouteilles</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Bouteilles
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Types de bouteilles</a>
                    </li>
                </ul>
            </div>
            <!-- Nouveau button -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-success-dark">
                    <a type="button" href="{{ route('bottles.types.create') }}"
                        class="waves-effect btn btn-success btn-md mb-2">
                        <i class="ti ti-plus"></i> Nouveau
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>Nom du type</th>
                                        <th>Date d'enregistrement</th>
                                        <th>État</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bottleTypes as $type)
                                        <tr>
                                            <td>{{ $type['name'] }}</td>
                                            <td>{{ date('d/m/Y H:i', strtotime($type['created_at'])) }}</td>
                                            <td>
                                                @php
                                                    $badgeClass =
                                                        $type['status'] === 'Actif'
                                                            ? 'text-bg-success'
                                                            : 'text-bg-danger';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $type['status'] }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('bottles.types.edit', $type['id']) }}"
                                                    class="btn btn-info btn-sm edit-type" data-id="{{ $type['id'] }}"
                                                    data-bs-toggle="modal" data-bs-target="#editTypeModal"
                                                    data-name="{{ $type['name'] }}">
                                                    <i class="iconoir-edit"></i> Modifier
                                                </a>

                                                @if ($type['status'] === 'Actif')
                                                    <button type="button" class="btn btn-warning btn-sm change-status"
                                                        data-id="{{ $type['id'] }}" data-action="deactivate">
                                                        <i class="iconoir-cancel"></i> Désactiver
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-success btn-sm change-status"
                                                        data-id="{{ $type['id'] }}" data-action="activate">
                                                        <i class="iconoir-check-circle"></i> Activer
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-danger btn-sm delete-type"
                                                    data-id="{{ $type['id'] }}">
                                                    <i class="iconoir-trash"></i> Supprimer
                                                </button>
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

@endsection

@section('script')
    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/ticket.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Form submission handler
            $('#createTypeForm').submit(function(e) {
                e.preventDefault();
                const typeName = $('#type_name').val();

                // Ici vous pourriez faire un appel AJAX pour sauvegarder les données
                // Pour l'exemple, nous affichons juste un message
                alert(`Type de bouteille "${typeName}" créé avec succès.`);

                // Reset form and collapse
                $('#type_name').val('');
                $('#collapseForm').collapse('hide');
            });

            // Edit button click handler
            $('.edit-type').click(function() {
                const typeId = $(this).data('id');
                const typeName = $(this).data('name');

                $('#edit_type_id').val(typeId);
                $('#edit_type_name').val(typeName);
            });

            // Save edit button click handler
            $('#saveTypeEdit').click(function() {
                const typeId = $('#edit_type_id').val();
                const typeName = $('#edit_type_name').val();

                // Ici vous pourriez faire un appel AJAX pour sauvegarder les modifications
                // Pour l'exemple, nous affichons juste un message
                alert(`Type de bouteille #${typeId} modifié en "${typeName}".`);

                // Close modal
                $('#editTypeModal').modal('hide');
            });

            // Change status button click handler
            $('.change-status').click(function() {
                const typeId = $(this).data('id');
                const action = $(this).data('action');
                const statusText = action === 'activate' ? 'activé' : 'désactivé';

                if (confirm(
                        `Êtes-vous sûr de vouloir ${action === 'activate' ? 'activer' : 'désactiver'} ce type de bouteille?`
                        )) {
                    // Ici vous pourriez faire un appel AJAX pour changer le statut
                    // Pour l'exemple, nous affichons juste un message
                    alert(`Le type de bouteille a été ${statusText}.`);
                }
            });

            // Delete button click handler
            $('.delete-type').click(function() {
                const typeId = $(this).data('id');

                if (confirm(
                        'Êtes-vous sûr de vouloir supprimer ce type de bouteille? Cette action est irréversible.'
                        )) {
                    // Ici vous pourriez faire un appel AJAX pour supprimer le type
                    // Pour l'exemple, nous affichons juste un message
                    alert(`Le type de bouteille a été supprimé.`);
                }
            });
        });
    </script>
@endsection
