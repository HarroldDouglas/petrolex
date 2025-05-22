@extends('layout.master')
@section('title', 'Créer un approvisionnement')

@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Ajouter un approvisionnement</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li><a href="#" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i> Approvisionnements</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Nouveau</a></li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Supply form start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="app-product-section">
                            <div class="main-title">
                                <h6>Nouvel approvisionnement!</h6>
                            </div>
                            <form class="app-form" action="{{ route('supplies.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="titre" class="form-label">Titre</label>
                                        <input type="text" class="form-control" id="titre"
                                            placeholder="Entrez un titre">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="datetime-local" class="form-control" id="date"
                                            value="{{ date('Y-m-d\TH:i') }}">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" rows="3" placeholder="Entrez une description"></textarea>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                    <a href="{{ route('supplies.list') }}" class="btn btn-light-secondary">Annuler</a>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Supply form end -->
    </div>
@endsection

@section('script')
    <!-- select2 -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection
