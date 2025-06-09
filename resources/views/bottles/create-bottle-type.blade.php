@extends('layout.master')
@section('title', 'Nouveau type de bouteille')
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title"> Nouveau type de bouteille</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Bouteilles
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Nouveau type de bouteille</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @livewire('bottle.create-bottle-type-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
