@extends('layout.master')
@section('title', 'Nouveau type de bouteille')
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title"> Nouveau type de bouteille</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Bouteilles
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Nouveau type de bouteille</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row app-form g-3">
                            <div class="col-md-12">
                                <label for="type_name" class="form-label">Nom du type de bouteille à ajouter</label>
                                <input type="text" class="form-control" id="type_name" placeholder="Ex: Bouteille 6kg"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="bottle_capacity_price" class="form-label">Prix de la recharge</label>
                                <input type="number" class="form-control" id="bottle_capacity_price" placeholder="Ex: 8500"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="bottle_price" class="form-label">Prix de la consigne + recharge</label>
                                <input type="number" class="form-control" id="bottle_price" placeholder="Ex: 25000"
                                    required>
                            </div>
                            <div class="col-md-12">
                                <label for="product_images" class="form-label">Images du produit</label>
                                <input type="file" class="form-control" id="product_images"
                                    placeholder="Ex: Bouteille 6kg" required>
                            </div>
                            <div class="col-md-8">
                                <label for="description" class="form-label">Description du produit</label>
                                <textarea class="form-control" id="description" rows="3" placeholder="Entrez la description ici" required></textarea>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-device-floppy"></i> créer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
