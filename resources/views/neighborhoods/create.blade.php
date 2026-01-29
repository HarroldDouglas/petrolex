@extends('layout.master')

@section('title', 'Créer un Quartier')

@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
@endsection

@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Créer un Quartier</h5>
                    </div>
                    <div class="card-body">
                        @livewire('neighborhood.create-neighborhood-form')
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
