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

    <!-- livewire multiple select -->
    <link rel="stylesheet" href="{{ asset('assets/css/multiple-select.css') }}">
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
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('users.list') }}" class="btn btn-primary">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste
                    </a>
                </div>
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
@endsection
