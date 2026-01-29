@extends('layout.master')
@section('title', 'Vérification des catégories')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Vérification des catégories de produits</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-package f-s-16"></i> Produits
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Vérification des catégories</a>
                    </li>
                </ul>
            </div>
            <!-- Refresh button -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a type="button" href="{{ route('products.verify-categories') }}"
                        class="waves-effect btn btn-primary btn-md mb-2">
                        <i class="ti ti-reload"></i> Actualiser
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Statistics Cards -->
        <div class="row mb-3">
            <div class="col-md-12">
                @if($total_missing > 0)
                    <div class="alert alert-warning" role="alert">
                        <i class="ti ti-alert-triangle"></i>
                        <strong>Attention !</strong> {{ $total_missing }} catégorie(s) manquante(s) détectée(s).
                        Utilisez la commande <code>php artisan products:verify-categories --fix</code> pour les créer automatiquement.
                    </div>
                @else
                    <div class="alert alert-success" role="alert">
                        <i class="ti ti-check"></i>
                        <strong>Parfait !</strong> Toutes les catégories sont cohérentes.
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-3">
            <div class="col-xl-3 col-md-6">
                <div class="card card-border">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Catégories</p>
                                <h4 class="mb-0">{{ $stats['total_categories'] }}</h4>
                            </div>
                            <div class="avatar-box bg-light-primary rounded-circle">
                                <i class="ti ti-category f-s-24 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-border">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Catégories Bouteilles</p>
                                <h4 class="mb-0">{{ $stats['bottle_categories'] }} / {{ $stats['bottle_types'] }}</h4>
                            </div>
                            <div class="avatar-box bg-light-success rounded-circle">
                                <i class="ph-duotone ph-stack f-s-24 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-border">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Catégories Accessoires</p>
                                <h4 class="mb-0">{{ $stats['accessory_categories'] }} / {{ $stats['accessory_types'] }}</h4>
                            </div>
                            <div class="avatar-box bg-light-info rounded-circle">
                                <i class="ti ti-tool f-s-24 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-border">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Catégories Manquantes</p>
                                <h4 class="mb-0 {{ $total_missing > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $total_missing }}
                                </h4>
                            </div>
                            <div class="avatar-box {{ $total_missing > 0 ? 'bg-light-danger' : 'bg-light-success' }} rounded-circle">
                                <i class="ti {{ $total_missing > 0 ? 'ti-alert-circle' : 'ti-circle-check' }} f-s-24 {{ $total_missing > 0 ? 'text-danger' : 'text-success' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottles Verification -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-header">
                        <h5>
                            <i class="ph-duotone ph-stack"></i> Vérification des Bouteilles
                            @if($bottle_missing > 0)
                                <span class="badge bg-danger ms-2">{{ $bottle_missing }} manquante(s)</span>
                            @else
                                <span class="badge bg-success ms-2">Toutes OK</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID Type</th>
                                        <th>Nom de la Bouteille</th>
                                        <th>ID Catégorie</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bottles as $bottle)
                                        <tr class="{{ !$bottle['has_category'] ? 'table-warning' : '' }}">
                                            <td>{{ $bottle['id'] }}</td>
                                            <td>{{ $bottle['name'] }}</td>
                                            <td>{{ $bottle['category_id'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($bottle['has_category'])
                                                    <span class="badge bg-success">
                                                        <i class="ti ti-check"></i> OK
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="ti ti-x"></i> MANQUANT
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accessories Verification -->
        <div class="row">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-header">
                        <h5>
                            <i class="ti ti-tool"></i> Vérification des Accessoires
                            @if($accessory_missing > 0)
                                <span class="badge bg-danger ms-2">{{ $accessory_missing }} manquante(s)</span>
                            @else
                                <span class="badge bg-success ms-2">Toutes OK</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID Type</th>
                                        <th>Nom de l'Accessoire</th>
                                        <th>ID Catégorie</th>
                                        <th class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessories as $accessory)
                                        <tr class="{{ !$accessory['has_category'] ? 'table-warning' : '' }}">
                                            <td>{{ $accessory['id'] }}</td>
                                            <td>{{ $accessory['name'] }}</td>
                                            <td>{{ $accessory['category_id'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($accessory['has_category'])
                                                    <span class="badge bg-success">
                                                        <i class="ti ti-check"></i> OK
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="ti ti-x"></i> MANQUANT
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
