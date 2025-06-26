@extends('layout.master')
@section('title', 'Créer un centre de distribution')
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
            <div class="col-6 p-0">
                <h4 class="main-title">Ajouter un centre de distribution</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Centre de distribution
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
                    <a type="button" class="btn btn-primary btn-md mb-2" href="{{ route('distribution-centers.list') }}">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste des centres de distribution
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add Distribution Center start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-10">
                <div class="card">
                    <div class="card-body">
                        @livewire('distribution-center.create-distribution-center')
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Distribution Center end -->
    </div>
@endsection
