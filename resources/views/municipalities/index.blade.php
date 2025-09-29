@extends('layout.master')

@section('title', 'Liste des Municipalités')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Liste des Municipalités</li>
@endsection

@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Liste des Municipalités</h5>
                    </div>
                    <div class="card-body">
                        @livewire('municipality.municipality-list')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection