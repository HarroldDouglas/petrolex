@extends('layout.master')
@section('title', 'Modifier le centre de distribution')

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
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

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title m-0">Informations du centre de distribution</h5>
                    </div>
                    <div class="card-body">
                        @livewire('distribution-center.edit-distribution-center', ['distributionCenter' => $distributionCenter])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
