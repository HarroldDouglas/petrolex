@extends('layout.master')

@section('title', 'Créer une Municipalité')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('municipalities.index') }}">Liste des Municipalités</a></li>
    <li class="breadcrumb-item active">Créer une Municipalité</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Créer une Municipalité</h5>
                    </div>
                    <div class="card-body">
                        @livewire('municipality.municipality-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection