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
        <div class="col-12 ">
            <h4 class="main-title">Ajouter un approvisionnement</h4>
            <ul class="app-line-breadcrumbs mb-3">
                <li><a href="#" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i> Apps</a></li>
                <li><a href="#" class="f-s-14 f-w-500">Approvisionnements</a></li>
                <li class="active"><a href="#" class="f-s-14 f-w-500">Créer</a></li>
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
                            <h6>Informations de l'approvisionnement</h6>
                        </div>
                        <form class="app-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="col-md-12 mb-4">
                                        <label for="ref" class="form-label">Titre</label>
                                        <input type="text" class="form-control" id="ref" placeholder="Ex: Approvisionnement abc">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="datetime-local" class="form-control" id="date">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="point" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" rows="6"
                                        placeholder="Ex: Description de l'approvisionnement"></textarea>
                                </div>

                            </div>

                            <!-- Action buttons -->
                            <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                <button type="reset" class="btn btn-light-danger b-r-22">Annuler</button>
                                <button type="submit" class="btn btn-primary b-r-22">Enregistrer</button>
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
