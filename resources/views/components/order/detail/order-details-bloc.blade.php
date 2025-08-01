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
                    <p>{{ $order->payment_method?->label }}</p> {{-- Ajout de l'opérateur null-safe --}}
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <h6 class="f-w-600 text-dark"><i
                        class="ti ti-truck-delivery f-s-18 me-2"></i>Livraison</h6>
                <div class="text-end">
                    <p>{{ $order->delivery_type->label }}</p>
                </div>
            </div>
            
            @if ($order->deliveryPerson)
                <div class="d-flex justify-content-between mt-3">
                    <h6 class="f-w-600 text-dark"><i
                            class="ti ti-user f-s-18 me-2"></i>Livreur</h6>
                    <div class="text-end">
                        <p>
                            <a href="{{ route('users.delivery.details', $order->deliveryPerson->user->id) }}" class="btn-link fw-bold">
                                {{ $order->deliveryPerson->user->full_name }}
                            </a>
                        </p>
                    </div>
                </div>
            @endif

            @unless(auth()->user()->hasRole(\App\Enums\UserRole::CENTER_MANAGER()->value))
                @if ($order->distributionCenter)
                    <div class="d-flex justify-content-between mt-3">
                        <h6 class="f-w-600 text-dark"><i
                                class="ti ti-building-warehouse f-s-18 me-2"></i>Centre de distribution</h6>
                        <div class="text-end">
                            <p>
                                <a href="{{ route('distribution-centers.details', $order->distributionCenter->id) }}" class="btn-link fw-bold">
                                    {{ $order->distributionCenter->name }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endif
            @endunless
        </div>
    </div>
</div>
