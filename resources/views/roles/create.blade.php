@extends('layout.master')
@section('title', 'Créer un rôle')
@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-6 p-0">
                <h4 class="main-title">Ajouter un rôle</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Rôle
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Ajouter</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 p-0">
                <div class="d-flex justify-content-end text-primary-dark">
                    <a type="button" class="btn btn-primary btn-md mb-2" href="{{ route('roles.list') }}">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste des rôles
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add Role start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        {{-- TODO: Add role creation form component --}}
                        <div class="text-center p-4">
                            <p class="text-muted">Fonctionnalité en cours de développement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Role end -->
    </div>
@endsection

@section('script')
    <!-- select2 js -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection