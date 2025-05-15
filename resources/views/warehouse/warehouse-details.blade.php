@extends('layout.master')
@section('title', 'Détails du point de distribution')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb with actions buttons -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Détails du point de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li><a href="{{ route('dashboard') }}" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i>
                            Tableau de bord</a></li>
                    <li><a href="{{ route('warehouses.list') }}" class="f-s-14 f-w-500">Points de distribution</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Point A</a></li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.edit', 1) }}" class="btn btn-success">
                        <i class="ti ti-edit me-1"></i>Modifier
                    </a>
                    <!-- Static status button for mockup -->
                    <a href="#" class="btn btn-danger">
                        <i class="ti ti-ban me-1"></i>Désactiver
                    </a>
                    <!-- The activate button would appear instead if status was inactive, but this is just a mockup -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Informations du point de distribution</h5>
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
