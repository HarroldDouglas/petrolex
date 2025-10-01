@extends('layout.master')
@section('title', 'Modifier le rôle')
@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-6 p-0">
                <h4 class="main-title">Modifier le rôle</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Rôle
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Modifier</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 p-0">
                <div class="d-flex justify-content-end text-primary-dark">
                    <a type="button" class="btn btn-primary btn-md mb-2" href="{{ route('roles.list') }}">
                        <i class="ti ti-arrow-left me-2"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Main content start -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Modifier le rôle: {{ $role->name }}</h5>
                    </div>
                    <div class="card-body">
                        @livewire('role.edit-role-form', ['role' => $role])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- select2 js -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection