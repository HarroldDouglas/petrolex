@extends('layout.master')
@section('title', 'Détails du Centre de Distribution')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Détails du Centre de Distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('dashboard') }}" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-house f-s-16"></i> Accueil
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('distribution-centers.list') }}" class="f-s-14 f-w-500">Centres de Distribution</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">{{ $distributionCenter->name }}</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0 text-end">
                <a href="{{ route('distribution-centers.edit', $distributionCenter->id) }}" class="btn btn-primary">
                    <i class="ph-duotone ph-pencil-simple me-1"></i> Modifier
                </a>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Informations générales</h5>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Nom :</div>
                            <div class="col-md-8">{{ $distributionCenter->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Pays :</div>
                            <div class="col-md-8">{{ $distributionCenter->country }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Ville :</div>
                            <div class="col-md-8">{{ $distributionCenter->city }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Quartier :</div>
                            <div class="col-md-8">{{ $distributionCenter->neighborhood }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Adresse :</div>
                            <div class="col-md-8">{{ $distributionCenter->address }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Téléphone :</div>
                            <div class="col-md-8">{{ $distributionCenter->phone }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Email :</div>
                            <div class="col-md-8">{{ $distributionCenter->email }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Capacité de stockage :</div>
                            <div class="col-md-8">{{ $distributionCenter->storage_capacity ?? 'Non spécifié' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-medium">Statut :</div>
                            <div class="col-md-8">
                                @if ($distributionCenter->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </div>
                        </div>
                        @if ($distributionCenter->description)
                            <div class="row mb-3">
                                <div class="col-md-4 fw-medium">Description :</div>
                                <div class="col-md-8">{{ $distributionCenter->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Stock de bouteilles</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                            <div>
                                <h6 class="mb-0">Consignes</h6>
                                <p class="text-muted small mb-0">Disponibles en stock</p>
                            </div>
                            <h3>{{ $distributionCenter->total_empty_bottles }}</h3>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Consignes + Recharges</h6>
                                <p class="text-muted small mb-0">Disponibles en stock</p>
                            </div>
                            <h3>{{ $distributionCenter->total_filled_bottles }}</h3>
                        </div>
                    </div>
                </div>

                @if ($distributionCenter->latitude && $distributionCenter->longitude)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Localisation</h5>
                            <div id="map" style="height: 200px"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Stock détaillé par type de bouteille</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type de bouteille</th>
                                        <th>Vides</th>
                                        <th>Pleines</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($distributionCenter->bottleTypeStocks as $bottleType)
                                        <tr>
                                            <td>{{ $bottleType->name }}</td>
                                            <td>{{ $bottleType->pivot->stock_empty }}</td>
                                            <td>{{ $bottleType->pivot->stock_filled }}</td>
                                            <td>{{ $bottleType->pivot->stock_empty + $bottleType->pivot->stock_filled }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">Aucun type de bouteille associé à ce
                                                centre de distribution</td>
                                        </tr>
                                    @endforelse
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
    @if ($distributionCenter->latitude && $distributionCenter->longitude)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Vérifier que l'API Google Maps est chargée correctement
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    console.error('Google Maps API n\'a pas été chargée correctement');
                    document.getElementById('map').innerHTML = '<div class="alert alert-warning">Impossible de charger la carte. Veuillez vérifier votre connexion internet.</div>';
                    return;
                }
                
                initMap();
            });

            function initMap() {
                const center = {
                    lat: {{ $distributionCenter->latitude }},
                    lng: {{ $distributionCenter->longitude }}
                };

                try {
                    const map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 15,
                        center: center,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });

                    new google.maps.Marker({
                        position: center,
                        map: map,
                        title: '{{ $distributionCenter->name }}',
                        animation: google.maps.Animation.DROP
                    });
                } catch (error) {
                    console.error('Erreur lors de l\'initialisation de la carte:', error);
                    document.getElementById('map').innerHTML = '<div class="alert alert-danger">Erreur lors du chargement de la carte: ' + error.message + '</div>';
                }
            }
        </script>
        
        <!-- Charger l'API Google Maps avec une gestion d'erreur -->
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.api_key') }}&callback=initMap&v=weekly" 
            async 
            defer
            onerror="document.getElementById('map').innerHTML = '<div class\'alert alert-danger\'>Impossible de charger l\'API Google Maps. Veuillez vérifier votre clé API.</div>'">
        </script>
    @endif
@endsection
