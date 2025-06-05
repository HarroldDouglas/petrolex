@extends('layout.master')
@section('title', 'Détails de la Commande')
@section('css')
<!-- leaflet css -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
                <div class="d-inline-block">
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMap" aria-expanded="false" aria-controls="collapseMap">
                                <i class="ti ti-map-pin me-2"></i>Voir en temps réel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="printOrder({{ $order->id }})">
                                <i class="ti ti-printer me-2"></i>Imprimer la facture
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.download.invoice', ['order' => $order->id]) }}">
                                <i class="ti ti-download me-2"></i>Télécharger la facture
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#refundModal{{ $order->id }}">
                                    <i class="ti ti-receipt-refund me-2"></i>Rembourser
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                @livewire('order.refund-form', ['order' => $order])

                <a class="btn btn-info ms-2" href="{{ route('dashboard') }}">Retour à la Liste des Commandes</a>
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
                <div class="col-xxl-8 mt-3">
                    <div class="row">
                        <!-- Order Details start -->
                        <div class="col-lg-6">
                            <div class="card order-details-card">
                                <div class="card-header">
                                    <h5 class="text-nowrap">Détails de la Commande ({{ $order->id }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-calendar f-s-18 me-2 text-secondary"></i>Date</h6>
                                        <div class="text-end">
                                            <p>{{ $order->order_date->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-credit-card f-s-18 me-2"></i>Paiement</h6>
                                        <div class="text-end">
                                            <p>{{ $order->payment_method->label }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-truck-delivery f-s-18 me-2"></i>Livraison</h6>
                                        <div class="text-end">
                                            <p>{{ $order->delivery_type->label }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Order Details end -->

                        <!-- Customer Details start -->
                            <x-order.detail.customer-details :order="$order"/>
                        <!-- Customer Details end -->
                    </div>

                    <!-- Order start -->
                    <x-order.detail.list-table :order="$order" :groupedItems="$groupedItems"/>
                    <!-- Order end -->

                </div>
                <x-order.detail.status :order="$order"/>
            </div>
            <!-- Order Details end -->
        @else
            <p>Commande non trouvée.</p>
        @endif
    </div>
@endsection

@section('script')

<!-- js-->
<script src="{{ asset('assets/js/orders_details.js') }}"></script>
<!-- leaflet js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function printOrder(orderId, withStub = true) {
        const url = withStub 
            ? `/orders/${orderId}/print/ticket-with-stub`
            : `/orders/${orderId}/print/ticket`;
        
        const printWindow = window.open(url, '_blank', 'width=800,height=600');
        
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    }

    function showRefundAlert() {
        alert('Fonctionnalité de remboursement en cours de développement!');
    }
</script>

{{--@stack('scripts')--}}
@endsection
