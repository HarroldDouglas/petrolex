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
                        </ul>
                    </div>
                </div>
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
                        <div class="col-lg-6">
                            <div class="card order-details-card">
                                <div class="card-header">
                                    <h5>Détails du Client</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-file-invoice text-secondary f-s-18 me-2"></i>Client</h6>
                                        <div class="text-end">
                                            <p>{{ $order->customer->user->first_name }} {{ $order->customer->user->last_name }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-mail f-s-18 text-secondary me-2"></i>Email</h6>
                                        <div class="text-end">
                                            <p>{{ $order->customer->user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-device-mobile f-s-18 text-secondary me-2"></i>Contact</h6>
                                        <div class="text-end">
                                            <p>{{ $order->customer->user->phone ?? 'Aucun numéro' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Customer Details end -->
                    </div>

                    <!-- Order start -->
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                Commande : {{ $order->order_number }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="orders-details-datatable app-scroll table-responsive">
                                <table class="table table-bottom-border text-center align-middle mb-0" id="ticketdatatable">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="text-start">Détails des Articles</th>
                                        <th scope="col">Quantité</th>
                                        <th scope="col">Prix</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="text-start">
                                                            <h6 class="mb-0">
                                                                {{ $item->product->name() }}
                                                            </h6>
                                                            <p class="f-w-500 m-0 text-muted f-s-13">Type:
                                                                <span class="text-secondary">
                                                                    {{ $item->product->type() }}
                                                                </span>
                                                            </p>
                                                            @if($item->bottle_type)
                                                                <p class="f-w-500 m-0 text-muted f-s-13">Option: <span
                                                                        class="text-secondary">{{ $item->bottle_type->label}}</span></p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="f-w-600">{{ $item->quantity }}</td>
                                                <td class="text-success f-w-500">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                                <td class="text-success f-w-500">
                                                    {{ number_format($item->total_price, 0, ',', ' ') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <th scope="col" colspan="1" class="text-start">Sous-total</th>
                                            <th scope="col" colspan="3" class="text-end f-w-500">
                                                <strong>{{ number_format($order->subtotal, 0, ',', ' ') }}</strong>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="col" colspan="1" class="text-start">Frais de livraison</th>
                                            <th scope="col" colspan="3" class="text-end f-w-500">
                                                <strong>{{ $order->delivery_fee }}</strong>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="col" colspan="1" class="text-start">Total</th>
                                            <th scope="col" colspan="3" class="text-end f-w-500">
                                                <strong>{{ $order->total_amount }}</strong>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Order end -->

                </div>
                <!-- Order Status start -->
                <div class="col-xxl-4 mt-3">
                    <div class="card equal-card">
                        <div class="card-header">
                            <h5>Statut de la Commande</h5>
                        </div>
                        <div class="card-body">
                            <ul class="app-timeline-box">

                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-shopping-cart f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-primary b-1-primary">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-primary">Commande Passée</h6>
                                            <span class="badge text-bg-primary ms-2">{{ $order->order_date->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-2 text-dark">Une commande a été passée.</p>
                                        <p class="text-secondary">{{ $order->order_date->format('D, d M Y - H:i') }}</p>
                                    </div>
                                </li>
                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-secondary h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-checks f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-secondary b-1-secondary">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-secondary">En cours de livraison</h6>
                                            <span class="color-light">Il y a 50 min</span>
                                        </div>
                                        <p class="mt-2">
                                            Le livreur est déjà en route pour livrer votre commande.
                                        </p>
                                        <p class="text-secondary">Jeu, 20 Déc 2024 - 6:48AM</p>
                                        <span class="badge bg-primary-900">Design</span>
                                        <span class="badge bg-primary-900">HTML</span>
                                    </div>
                                </li>
                                <li class="timeline-section">
                                    <div class="timeline-icon">
                            <span class="text-light-success h-35 w-35 d-flex-center b-r-50">
                                <i class="ti ti-truck-delivery f-s-20"></i>
                            </span>
                                    </div>
                                    <div class="timeline-content bg-light-success b-1-success">
                                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                                            <h6 class="mt-2 text-success">Livrée</h6>
                                            <span class="badge text-bg-success ms-2">Il y a 1 heure</span>
                                        </div>
                                        <p class="mt-2 text-dark">
                                            Votre article a bien été livré sur votre confirmation.
                                        </p>
                                        <p class="text-secondary">Jeu, 20 Déc 2024 - 5:48AM</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    @if($order->canBeRated() && ($order->comments || $order->rating))
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>Évaluation du client</h5>
                            </div>
                            <div class="card-body">
                                @if($order->rating)
                                <div class="d-flex justify-content-between">
                                    <h6 class="f-w-600 text-dark"><i class="ti ti-star f-s-18 me-2 text-warning"></i>Note</h6>
                                    <div class="text-end">
                                        <p>{{ $order->rating }}/5</p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($order->comments)
                                <div class="mt-3">
                                    <h6 class="f-w-600 text-dark"><i class="ti ti-message f-s-18 me-2 text-info"></i>Commentaire client</h6>
                                    <p class="mt-2">{{ $order->comments }}</p>
                                </div>
                                @endif
                                
                                @if($order->center_comments)
                                <div class="mt-3">
                                    <h6 class="f-w-600 text-dark"><i class="ti ti-message-circle f-s-18 me-2 text-primary"></i>Commentaire centre</h6>
                                    <p class="mt-2">{{ $order->center_comments }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <!-- Order Status end -->
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
</script>

{{--@stack('scripts')--}}
@endsection
