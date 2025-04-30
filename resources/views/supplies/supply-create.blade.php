@extends('layout.master')
@section('title', 'Créer un approvisionnement')

@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection

@section('main-content')
<div class="container-fluid">
    <!-- Breadcrumb start -->
    <div class="row m-1">
        <div class="col-12 ">
            <h4 class="main-title">Ajouter un approvisionnement</h4>
            <ul class="app-line-breadcrumbs mb-3">
                <li><a href="#" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i> Apps</a></li>
                <li><a href="#" class="f-s-14 f-w-500">Approvisionnements</a></li>
                <li class="active"><a href="#" class="f-s-14 f-w-500">Créer</a></li>
            </ul>
        </div>
    </div>
    <!-- Breadcrumb end -->

    <!-- Supply form start -->
    <div class="row">
        <div class="col-lg-12 col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <div class="app-product-section">
                        <div class="main-title">
                            <h6>Détails de l'approvisionnement</h6>
                        </div>
                        <form class="app-form">
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    <label for="ref" class="form-label">Référence</label>
                                    <input type="text" class="form-control" id="ref" placeholder="Ex: APR-0001">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="datetime-local" class="form-control" id="date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fournisseur" class="form-label">Fournisseur</label>
                                    <select class="form-select" id="fournisseur">
                                        <option value="">Sélectionner un fournisseur</option>
                                        <option>OPQ Gaz</option>
                                        <option>XYZ Energy</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="point" class="form-label">Point de distribution</label>
                                    <select class="form-select" id="point">
                                        <option value="">Sélectionner un point</option>
                                        <option>Douala Makepe</option>
                                        <option>Bafoussam 3</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Product lines section -->
                            <div class="main-title mt-1 mb-1">
                                <h6>Produits</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle table-bordered">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix unitaire</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-select">
                                                    <option>Bouteille 6kg</option>
                                                    <option>Bouteille 12kg</option>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" placeholder="Qté"></td>
                                            <td><input type="text" class="form-control" placeholder="Prix unitaire"></td>
                                            <td><input type="text" class="form-control" placeholder="Total" disabled></td>
                                            <td><button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-outline-primary btn-sm"><i class="ti ti-plus"></i> Ajouter un produit</button>
                            </div>

                            <!-- Action buttons -->
                            <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                <button type="button" class="btn btn-light-secondary b-r-22">Annuler</button>
                                <button type="submit" class="btn btn-primary b-r-22">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Supply form end -->
</div>
@endsection

@section('script')
    <!-- select2 -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection
