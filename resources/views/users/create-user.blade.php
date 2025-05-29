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
                                <i class="ph-duotone  ph-stack f-s-16"></i> Utilisateurs
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Nouveau</a>
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
                                <div class="profile-image" style="background-image: url(/build/assets/28-DUtk996K.jpg);">
                                </div>
                                <div class="profile-pic">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit">
                                            <input type="file" id="imageUpload" accept=".png')}}, .jpg')}}, .jpeg">
                                            <label for="imageUpload"><i class="ti ti-photo-heart"></i></label>
                                        </div>
                                        <div class="avatar-preview">
                                            <div id="imgPreview">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="person-details">
                                <h5 class="f-w-600">Photo de profil
                                    <img src="{{ asset('../assets/images/profile-app/01.png') }}" class="w-20 h-20"
                                        alt="instagram-check-mark">
                                </h5>
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
                                        <div class="col-md-6 mb-3">
                                            <label for="role" class="form-label">Fonction</label>
                                            <select class="form-select" id="role" name="role">
                                                @foreach($allowedRoles ?? [] as $role)
                                                    <option value="{{ $role->value }}">{{ $role->label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 distribution-centers-container" style="display: none;">
                                            <label for="distribution_centers" class="form-label">Centre de distribution</label>
                                            <select class="form-select" id="distribution_centers" name="distribution_centers[]" multiple>
                                                <option value="Point A">Point A</option>
                                                <option value="Point B">Point B</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div
                                                class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                                <button type="button" class="btn btn-light-danger">Annuler</button>
                                                <button type="submit" class="btn btn-success">Enregistrer</button>
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
        <!--customizer-->
        <div id="customizer"></div>

        <!-- select2 -->
        <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>

        <!-- filepond -->
        <script src="{{ asset('assets/vendor/filepond/file-encode.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/filepond/validate-size.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/filepond/validate-type.js') }}"></script>
        <script src="{{ asset('assets/vendor/filepond/exif-orientation.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/filepond/image-preview.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/filepond/filepond.min.js') }}"></script>

        <!-- Trumbowyg js -->
        <script src="{{ asset('assets/vendor/trumbowyg/trumbowyg.min.js') }}"></script>

        <!-- add product -->
        <script src="{{ asset('assets/js/add_product.js') }}"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize Select2 for distribution centers
                $('#distribution_centers').select2({
                    placeholder: 'Sélectionnez un ou plusieurs centres de distribution'
                });
                
                // Show/hide distribution centers based on role selection
                $('#role').on('change', function() {
                    var selectedRole = $(this).val();
                    
                    // Check if selected role is CENTER_MANAGER
                    if (selectedRole === '{{ \App\Enums\UserRole::CENTER_MANAGER()->value }}') {
                        $('.distribution-centers-container').show();
                    } else {
                        $('.distribution-centers-container').hide();
                    }
                });
                
                // Trigger change on page load to handle initial state
                $('#role').trigger('change');
            });
        </script>
    @endsection
