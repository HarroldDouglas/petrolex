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
            <div class="col mt-3 mb-3 text-end">
                <!-- Customer Details start -->
                <x-order.detail.actions :order="$order" />
                <!-- Customer Details end -->

                <a class="btn btn-primary ms-2" href="{{ route('orders.list') }}">
                    <i class="ti ti-arrow-back"></i> Retourner à la liste
                </a>
            </div>
        </div>
        <!-- Breadcrumb end -->

        @if ($order)
            <!-- Order Details start -->
            <div class="row order-details">

                <div class="col-12 mb-3">
                    <div class="collapse" id="collapseMap">
                        <div class="card card-body">
                            @livewire('components.route-map')
                        </div>
                    </div>
                </div>

                @if ($order->canScanBottles())
                    <div class="collapse mt-3" id="collapseScanBottles">
                        <div class="card card-body border border-primary">
                            <h5 class="card-title mb-3">Scanner les bouteilles de la commande</h5>
                            @livewire('order.order-scan-bottles', ['order' => $order])
                        </div>
                    </div>
                @endif

                <div class="col-xxl-8 mt-3">
                    <div class="row">
                        <x-order.detail.order-details :order="$order" />

                        <!-- Customer Details start -->
                        <x-order.detail.customer-details :order="$order" />
                        <!-- Customer Details end -->
                    </div>

                    <!-- Order start -->
                    <x-order.detail.list-table :order="$order" :groupedItems="$groupedItems" />
                    <!-- Order end -->

                </div>
                <x-order.detail.status :order="$order" />
            </div>
            <!-- Order Details end -->
        @else
            <p>Commande non trouvée.</p>
        @endif
    </div>

@endsection

@section('script')

    <!-- leaflet js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
