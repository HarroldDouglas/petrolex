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
            <div class="col-8 p-0">
                <h4 class="main-title">Ajouter un centre de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Centre de distribution</a>
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
                                            <label for="country" class="form-label">Pays</label>
                                            <select class="form-select select2" id="country" name="country">
                                                <option value="Cameroun">Cameroun</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label">Ville</label>
                                            <select class="form-select select2" id="city" name="city">
                                                <option value="Douala">Douala</option>
                                                <option value="Yaoundé">Yaoundé</option>
                                                <option value="Adamaoua">Adamaoua</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="neighborhood" class="form-label">Quartier</label>
                                            <select class="form-select select2" id="neighborhood" name="neighborhood">
                                                <option value="Bonanjo">Bonanjo</option>
                                                <option value="Akwa">Akwa</option>
                                                <option value="Bastos">Bastos</option>
                                                <option value="Centre">Centre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" placeholder="Adresse" id="address" name="address">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" placeholder="690102030"
                                                id="phone" name="phone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control" placeholder="email@example.com"
                                                id="email" name="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" class="form-control" placeholder="Latitude"
                                                id="latitude" name="latitude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="longitude" class="form-label">Longitude</label>
                                            <input type="text" class="form-control" placeholder="Longitude"
                                                id="longitude" name="longitude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                                            <input type="text" class="form-control" placeholder="storage_capacity"
                                                id="storage_capacity" name="storage_capacity">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="statut" class="form-label">Statut</label>
                                            <select class="form-select" id="statut" name="is_active">
                                                <option value="1">Actif</option>
                                                <option value="0">Inactif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description du centre de distribution"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <div
                                                class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                                <button type="button" class="btn btn-light-danger">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
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
