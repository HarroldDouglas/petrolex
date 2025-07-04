@extends('layout.master')
@section('title', 'Modifier le produit')
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title">Modifier le produit</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('accessories.index') }}" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Produits
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Modifier le produit</a>
                    </li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('accessories.index') }}" class="btn btn-primary">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @livewire('accessory.edit-accessory-type', ['accessoryType' => $accessoryType])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
