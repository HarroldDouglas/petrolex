@extends('layout.master')
@section('title', 'Détails du centre de distribution')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb with actions buttons -->
        <div class="row m-1">
            <div class="col-6 p-0">
                <h4 class="main-title">Ajouter un centre de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Centre de distribution
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Ajouter</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 p-0">
                <div class="d-flex justify-content-end text-primary-dark">
                    <a type="button" class="btn btn-success btn-md mb-2" href="{{ route('distribution-centers.list') }}">
                        <i class="ti ti-arrow-left"></i> Retourner à la liste des centres de distribution
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Informations du centre de distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Nom</h6>
                                    <p class="mb-0">Point A</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Ville</h6>
                                    <p class="mb-0">Yaoundé</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Adresse</h6>
                                    <p class="mb-0">Bastos, derrière l'usine</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Code postal</h6>
                                    <p class="mb-0">BP 1234</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Latitude</h6>
                                    <p class="mb-0">3.866667</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Téléphone</h6>
                                    <p class="mb-0">+237 690 123 456</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Email</h6>
                                    <p class="mb-0">pointA@example.com</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Capacité de stockage</h6>
                                    <p class="mb-0">200 bouteilles</p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Statut</h6>
                                    <span class="badge text-light-info">ACTIF</span>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Longitude</h6>
                                    <p class="mb-0">11.516667</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title m-0">Statistiques</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-muted mb-1">Bouteilles en stock</h6>
                                <p class="mb-0 fs-3">150</p>
                            </div>
                            <div class="col-md-6 mb-4">
                                <h6 class="text-muted mb-1">Capacité utilisée</h6>
                                <p class="mb-0 fs-3">75%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
