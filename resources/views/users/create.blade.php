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

        <!-- Add User start -->
        
             @livewire('user.create-user-form')
            <!-- Add User end -->
        
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
