@extends('layout.master')

@section('title', 'Details de point de distribution')

@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Details du point de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Utilisateur</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Details du point de distribution </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Order Details start -->
        <div class="row order-details">
            <div class="col-xxl-12">
                <div class="row">

                    <!-- Customer Details start -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Details du point de distribution</h5>
                            </div>
                            <div class="card-body">
                                <form class="app-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Nom</label>
                                            <input type="text" value="Point A" class="form-control" disabled placeholder="Nom" id="name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label">Ville</label>
                                            <input type="text" value="Yaoundé" class="form-control" disabled placeholder="Prénom" id="city">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Adresse</label>
                                            <input type="text" value="Bastos" class="form-control" disabled placeholder="Adresse" id="adress">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" value="+237 693467584" class="form-control" disabled placeholder="690102030" id="phone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" value="ali@gmail.com" class="form-control" disabled placeholder="email@example.com" id="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="postal_code" class="form-label">Code postal</label>
                                            <input type="text" value="1080" class="form-control" disabled placeholder="Code postal" id="postal_code">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="latitude" class="form-label">Latitude</label>
                                            <input type="text" value="20.89740" class="form-control" disabled placeholder="Latitude" id="latitude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="Longitiude" class="form-label">Longitiude</label>
                                            <input type="text" value="19.1234" class="form-control" disabled placeholder="Longitiude" id="Longitiude">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                                            <input type="text" value="1000" class="form-control" disabled placeholder="storage_capacity" id="storage_capacity">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="statut" class="form-label">Statut</label>
                                            <input type="text" value="Actif" class="form-control" disabled placeholder="statut" id="statut">
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Customer Details end -->

                </div>

            </div>
<<<<<<< HEAD

=======

>>>>>>> dev
        </div>
        <!-- Order Details end -->
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- datatable js -->
<script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

@endsection
