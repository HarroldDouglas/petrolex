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
                <button class="btn btn-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMap" aria-expanded="false" aria-controls="collapseMap">
                    Voir en temps réel
                </button>
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
                                    <h5 class="text-nowrap">Détails de la Commande ({{ $order['id'] }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-calendar f-s-18 me-2 text-secondary"></i>Date</h6>
                                        <div class="text-end">
                                            <p>{{ \Carbon\Carbon::createFromFormat('d/m/Y', $order['order_date'])->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-credit-card f-s-18 me-2"></i>Paiement</h6>
                                        <div class="text-end">
                                            <p>En ligne</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-truck-delivery f-s-18 me-2"></i>Livraison</h6>
                                        <div class="text-end">
                                            <p>Livraison Rapide</p>
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
                                            <p>{{ $order['customer_name'] }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-mail f-s-18 text-secondary me-2"></i>Email</h6>
                                        <div class="text-end">
                                            <p>{{ str_replace(' ', '', strtolower($order['customer_name'])) }}@gmail.com</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-device-mobile f-s-18 text-secondary me-2"></i>Contact</h6>
                                        <div class="text-end">
                                            <p>+1 111 134 111</p>
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
                                Commande : {{ $order['id'] }}
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
                                        @foreach ($order['items'] as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="text-start">
                                                            <h6 class="mb-0"> {{ $item['name'] }}</h6>
                                                            <p class="f-w-500 m-0 text-muted f-s-13">Couleur:
                                                                <span class="text-secondary">Blanc</span>
                                                            </p>
                                                            <p class="f-w-500 m-0 text-muted f-s-13">Taille: <span
                                                                    class="text-secondary">Petit</span></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="f-w-600">{{ $item['quantity'] }}</td>
                                                <td class="text-success f-w-500">{{ number_format($item['price'], 2) }}</td>
                                                <td class="text-success f-w-500">
                                                    {{ number_format($item['price'] * $item['quantity'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="1" class="text-start">Sous-total</td>
                                        <td colspan="3" class="text-end f-w-500">
                                            <strong>{{ number_format($order['total_amount'], 2) }}</strong>
                                        </td>
                                    </tr>
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
                                            <span class="badge text-bg-primary ms-2">Il y a 20 min</span>
                                        </div>
                                        <p class="mt-2 text-dark">Une commande a été passée.</p>
                                        <p class="text-secondary">Mer, 15 Déc 2024 - 05:34PM</p>
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

{{--@stack('scripts')--}}
@endsection
