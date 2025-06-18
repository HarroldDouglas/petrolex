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
                    <li><a href="{{ route('supplies.list') }}" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i> Approvisionnements</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">Nouveau</a></li>
                </ul>
            </div>

            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('supplies.list') }}" class="btn btn-primary">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Supply form start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        @livewire('supply.create-supply-delivery')
                    </div>
                </div>
            </div>
        </div>
        <!-- Supply form end -->
    </div>
@endsection
