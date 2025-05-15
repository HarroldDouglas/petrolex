@extends('layout.master')
@section('title', 'Éditer l\'approvisionnement')
@section('css')
    @livewireStyles
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row m-1">
            <div class="col-12">
                <h4 class="main-title">Éditer l'approvisionnement</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li>
                        <a href="#" class="f-s-14 f-w-500">
                            <i class="ph-duotone ph-stack f-s-16"></i> Apps
                        </a>
                    </li>
                    <li><a href="{{ route('supplies.list') }}" class="f-s-14 f-w-500">Approvisionnements</a></li>
                    <li><a href="{{ route('supplies.details', ['supply_id' => 1]) }}" class="f-s-14 f-w-500">Détails</a>
                    </li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Éditer</a></li>
                </ul>
            </div>
        </div>

        <!-- Supply Edit Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-white text-black p-4">
                    <div class="card-body">
                        <div class="app-product-section">
                            <form id="edit-supply-form" class="app-form"
                                action="{{ route('supplies.details', ['supply_id' => 1]) }}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="titre" class="form-label">Titre</label>
                                        <input type="text" class="form-control" id="titre" name="title"
                                            placeholder="Entrez un titre" value="Approvisionnement du 13 Avril">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="datetime-local" class="form-control" id="date" name="date"
                                            value="{{ date('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Entrez une description">Approvisionnement de bouteilles de gaz et accessoires pour le point Bafoussam 3</textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('supplies.list') }}" class="btn btn-secondary">
                                                <i class="ti ti-x me-1"></i>Annuler
                                            </a>
                                            <button type="submit" class="btn btn-success">
                                                <i class="ti ti-device-floppy me-1"></i>Enregistrer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Livewire Component -->
        @livewire('supply-products')
    </div>
@endsection

@section('script')
    @livewireScripts
@endsection
