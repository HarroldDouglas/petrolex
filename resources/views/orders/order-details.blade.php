@extends('layout.master')
@section('title', 'Détails de la Commande')
@section('css')
    <!-- leaflet css -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1 d-flex justify-content-between align-items-center">
            <div class="col">
                <h4 class="main-title">Détails de la Commande</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Commandes
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Détails de la Commande</a>
                    </li>
                </ul>
            </div>
            @livewire('order-details-actions', ['order' => $order])
        </div>
        <!-- Breadcrumb end -->

        @livewire('order.order-detail-view', ['orderId' => $order->id])
    </div>
    <x-sweet-alert-notification-listener />

@endsection

@section('script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
