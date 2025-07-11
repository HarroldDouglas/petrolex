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
                @livewire('order-details-actions', ['order' => $order])
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
                        <!-- Order Details start -->
                        <div class="col-lg-6">
                            <div class="card order-details-card">
                                <div class="card-header">
                                    <h5 class="text-nowrap">Détails de la Commande ({{ $order->id }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i
                                                class="ti ti-calendar f-s-18 me-2 text-secondary"></i>Date</h6>
                                        <div class="text-end">
                                            <p>{{ $order->order_date->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-credit-card f-s-18 me-2"></i>Paiement
                                        </h6>
                                        <div class="text-end">
                                            <p>{{ $order->payment_method->label }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i
                                                class="ti ti-truck-delivery f-s-18 me-2"></i>Livraison</h6>
                                        <div class="text-end">
                                            <p>{{ $order->delivery_type->label }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Order Details end -->

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
    <x-sweet-alert-notification-listener />

@endsection

@section('script')

    <!-- leaflet js -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('assets/js/custom/sweet_alert_confirm_actions.js') }}" defer></script>
    <!-- sweet-alert cancel reason script -->
    <script>
        /**
         * Affiche une première boîte de dialogue pour demander la raison de l'annulation,
         * puis enchaîne avec la confirmation de saisie.
         *
         * @param {Object} params - Les paramètres de configuration
         * @param {string} params.method - Nom de la méthode Livewire à appeler
         * @param {Array} params.parameters - Paramètres initiaux à passer (l'ID de la commande, etc.)
         * @param {string} params.componentId - ID du composant Livewire
         * @param {string} params.confirmWord - Mot à taper pour la confirmation finale (ex: "Annuler")
         */
        function promptForCancellationReason(params) {
            Swal.fire({
                title: 'Motif de l\'annulation',
                input: 'textarea',
                inputLabel: 'Veuillez spécifier la raison de l\'annulation de cette commande.',
                inputPlaceholder: 'Entrez la raison ici...',
                inputAttributes: {
                    'aria-label': 'Entrez la raison ici'
                },
                showCancelButton: true,
                confirmButtonText: 'Continuer',
                cancelButtonText: 'Fermer',
                confirmButtonColor: "#198754",
                cancelButtonColor: "#6c757d",
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Vous devez fournir une raison !'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const reason = result.value;
                    const finalParams = {
                        ...params,
                        parameters: [...params.parameters, reason], // Ajoute la raison aux paramètres
                        title: 'Êtes-vous absolument sûr ?',
                        text: 'Cette action est irréversible et annulera la commande.',
                        entityName: `Raison : ${reason}`,
                        icon: 'warning',
                        confirmWord: 'Annuler',
                        confirmButtonIcon: 'ti ti-receipt-refund',
                        confirmText: 'Oui, annuler la commande'
                    };
                    confirmActionWithInput(finalParams);
                }
            });
        }
    </script>
@endsection
