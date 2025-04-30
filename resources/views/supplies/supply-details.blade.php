@extends('layout.master')
@section('title', 'Détails de l\'approvisionnement')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Détails de l'approvisionnement</h4>
            <ul class="app-line-breadcrumbs mb-3">
                <li>
                    <a href="#" class="f-s-14 f-w-500">
                        <i class="ph-duotone ph-stack f-s-16"></i> Apps
                    </a>
                </li>
                <li><a href="#" class="f-s-14 f-w-500">Approvisionnements</a></li>
                <li class="active"><a href="#" class="f-s-14 f-w-500">Détails</a></li>
            </ul>
        </div>
    </div>

    <!-- Supply Detail Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-white text-black p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-2">Référence : <strong>APR-1001</strong></h5>
                        <p><strong>Point de distribution :</strong> Point Bafoussam 3</p>
                        <p><strong>Date :</strong> 13 Avril, 2025 11:45</p>
                        <p><strong>Statut :</strong> <span class="badge text-outline-danger">Annulé</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fournisseur :</strong> OPQ Gaz</p>
                        <p><strong>Total produits :</strong> 2</p>
                        <p><strong>Quantité totale :</strong> 300</p>
                    </div>
                </div>

                <!-- Product List -->
                <h5 class="mb-3">Produits approvisionnés</h5>
                <div class="table-responsive app-scroll">
                    <table class="table table-bordered w-100 display ticket-app-table" id="ticketdatatable">
                        <thead class="bg-primary">
                            <tr>
                                <th>Nom du produit</th>
                                <th>Quantité</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Bouteille de 9kg</td>
                                <td>150</td>
                                <td>8,000 FCFA</td>
                                <td>1,200,000 FCFA</td>
                            </tr>
                            <tr>
                                <td>Bouteille de 12kg</td>
                                <td>150</td>
                                <td>10,000 FCFA</td>
                                <td>1,500,000 FCFA</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Montant Total</td>
                                <td>2,700,000 FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

@endsection
