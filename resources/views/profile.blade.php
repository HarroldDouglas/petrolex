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
                <h4 class="main-title">Ajouter un utilisateur</h4>
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
                        <a href="#" class="f-s-14 f-w-500">Ajouter</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add Product start -->
        <div class="row">
            <div class="col-lg-3">
                <div class="card order-details-card">
                    <div class="card-body">
                        <div class="profile-container">
                            <div class="image-details">
                                <div class="profile-image">
                                </div>
                                <div class="profile-pic">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit">
                                            <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg">
                                            <label for="imageUpload"><i class="ti ti-photo-heart"></i></label>
                                        </div>
                                        <div class="avatar-preview">
                                            <div id="imgPreview" style="background-image: url({{asset('assets/images/avtar/woman.jpg')}});">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="person-details">
                                <h5 class="f-w-600">Photo de profil</h5>
                                <p>Ajouter une photo de profil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 col-xxl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="app-product-section">
                            <div>
                                <form class="app-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Nom</label>
                                            <input type="text" class="form-control" placeholder="Nom" id="last_name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">Prénom</label>
                                            <input type="text" class="form-control" placeholder="Prénom" id="first_name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control" placeholder="email@example.com"
                                                id="email">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Téléphone</label>
                                            <input type="text" class="form-control" placeholder="690102030"
                                                id="phone">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="profileImageUpload" class="form-label">Photo de profil</label>
                                            <input type="file" class="form-control" id="profileImageUpload" accept="image/*">
                                            <small class="form-text text-muted">Format recommandé: JPG, PNG. Taille max: 2MB</small>
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

@section('script')
    <script src="{{ asset('assets/vendor/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/filepond/image-preview.min.js') }}"></script>

    <script>
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imgPreview').style.backgroundImage = `url(${e.target.result})`;
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('profileImageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('imageUpload').files = dataTransfer.files;
                const event = new Event('change');
                document.getElementById('imageUpload').dispatchEvent(event);
            }
        });

        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('profileImageUpload').files = dataTransfer.files;
            }
        });
    </script>
@endsection
