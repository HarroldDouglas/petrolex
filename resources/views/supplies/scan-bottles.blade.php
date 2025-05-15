@extends('layout.master')
@section('title', 'Scanner les bouteilles')

@section('css')
    @livewireStyles
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb with return button -->
        <div class="row m-1">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="app-line-breadcrumbs">
                        <li><a href="#" class="f-s-14 f-w-500"><i class="ph-duotone ph-stack f-s-16"></i> Apps</a></li>
                        <li><a href="{{ route('supplies.list') }}" class="f-s-14 f-w-500">Approvisionnements</a></li>
                        <li><a href="{{ route('supplies.details', ['supply_id' => $supply['id']]) }}"
                                class="f-s-14 f-w-500">Détails</a></li>
                        <li class="active"><a href="#" class="f-s-14 f-w-500">Scanner</a></li>
                    </ul>
                    <a href="{{ route('supplies.details', ['supply_id' => $supply['id']]) }}"
                        class="btn btn-success b-r-22">
                        <i class="ti ti-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        @livewire('scan-bottles', [
            'supplyId' => $supply['id'],
            'productId' => $supply['product']['id'],
            'bottleType' => $supply['product']['type'],
            'quantity' => $supply['product']['quantity'],
        ])
    </div>
@endsection

@section('script')
    @livewireScripts
    @stack('scripts')
@endsection
