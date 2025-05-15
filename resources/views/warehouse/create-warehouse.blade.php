@extends('layout.master')
@section('title', 'Create User')
@section('css')
    <!-- filepond css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/filepond/filepond.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/filepond/image-preview.min.css') }}">

    <!-- editor css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/trumbowyg/trumbowyg.min.css') }}">

    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Ajouter un point de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Point de distribution</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Ajouter</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add Product start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-10">
                <div class="card">
                    <div class="card-body">
                        <div class="app-product-section">
                            <div>
                                <form class="app-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Nom</label>
                                            <input type="text" class="form-control" placeholder="Nom" id="name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label">Ville</label>
                                            <input type="text" class="form-control" placeholder="Prénom" id="city">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" placeholder="Adresse" id="adress">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" placeholder="690102030"
                                                id="phone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control" placeholder="email@example.com"
                                                id="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="point_vente" class="form-label">Code postal</label>
                                            <input type="text" class="form-control" placeholder="Code postal"
                                                id="postal_code">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" class="form-control" placeholder="Latitude"
                                                id="latitude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="Longitiude" class="form-label">Longitiude</label>
                                            <input type="text" class="form-control" placeholder="Longitiude"
                                                id="Longitiude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                                            <input type="text" class="form-control" placeholder="storage_capacity"
                                                id="storage_capacity">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="statut" class="form-label">Statut</label>
                                            <select class="form-select" id="statut">
                                                <option value="actif">Actif</option>
                                                <option value="inactif">Inactif</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div
                                                class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                                <button type="button" class="btn btn-light-danger">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</bu>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
            <!-- Add Product end -->
        </div>
    @endsection
