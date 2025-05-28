@extends('layout.master')
@section('title', 'Modifier le centre de distribution')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row m-1">
            <div class="col-12 p-0">
                <h4 class="main-title">Modifier le centre de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li><a href="{{ route('dashboard') }}" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i>
                            Tableau de bord</a></li>
                    <li><a href="{{ route('warehouses.list') }}" class="f-s-14 f-w-500">Centres dedistribution</a></li>
                    <li><a href="{{ route('warehouses.details', $warehouse['id']) }}"
                            class="f-s-14 f-w-500">{{ $warehouse['name'] }}</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Modifier</a></li>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Informations du centre de distribution</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ $warehouse['name'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="{{ $warehouse['city'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                        value="{{ $warehouse['address'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Code postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                        value="{{ $warehouse['postal_code'] }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="{{ $warehouse['phone'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $warehouse['email'] }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="storage_capacity" class="form-label">Capacité de stockage</label>
                                    <input type="number" class="form-control" id="storage_capacity" name="storage_capacity"
                                        value="{{ $warehouse['storage_capacity'] }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Statut</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="ACTIF" {{ $warehouse['status'] === 'ACTIF' ? 'selected' : '' }}>
                                            Actif</option>
                                        <option value="INACTIF" {{ $warehouse['status'] === 'INACTIF' ? 'selected' : '' }}>
                                            Inactif</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude"
                                        value="{{ $warehouse['latitude'] }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude"
                                        value="{{ $warehouse['longitude'] }}">
                                </div>

                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('warehouses.details', $warehouse['id']) }}"
                                            class="btn btn-secondary">
                                            <i class="ti ti-x me-1"></i>Annuler
                                        </a>
                                        <a href="{{ route('warehouses.details', $warehouse['id']) }}"
                                            class="btn btn-success">
                                            <i class="ti ti-device-floppy me-1"></i>Enregistrer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
