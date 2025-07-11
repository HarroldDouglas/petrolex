@extends('layout.master')
@section('title', 'Créer une commande')
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
                <h4 class="main-title">Ajouter une commande</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Commande
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
                    <a type="button" class="btn btn-primary btn-md mb-2" href="{{ route('orders.list') }}">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste des commandes
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add Order start -->
        <div class="row">
            <div class="col-lg-12 col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        @livewire('order.create-order-form')
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Order end -->
    </div>
@endsection