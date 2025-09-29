@extends('layout.master')

@section('title', 'Créer une Municipalité')

@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
    <style>
        .select2-container--default .select2-selection--multiple {
            border-color: #d1d3e2;
            min-height: 38px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
    </style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
    <li class="breadcrumb-item"><a href="{{ route('municipalities.index') }}">Liste des Municipalités</a></li>
    <li class="breadcrumb-item active">Créer une Municipalité</li>
@endsection

@section('main-content')
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

@section('script')
    <!-- select2 -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection