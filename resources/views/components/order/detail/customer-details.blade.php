@php 
     use Illuminate\Support\Str;
@endphp
<div class="col-lg-6">
    <div class="card order-details-card">
        <div class="card-header">
            <h5>Détails du Client</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6 class="f-w-600 text-dark"><i class="ti ti-file-invoice text-secondary f-s-18 me-2"></i>Client</h6>
                <div class="text-end">
                    <p>
                        <a href="{{ route('users.customer.details', $order->customer->user->id) }}" class="btn-link fw-bold">
                            {{ $order->customer->user->first_name }} {{ $order->customer->user->last_name }}
                        </a>
                    </p>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <h6 class="f-w-600 text-dark"><i class="ti ti-map-pin f-s-18 text-secondary me-2"></i>Adresse de la commande</h6>
                <div class="text-end">
                    <p>
                        {{ $order->deliveryAddress?->fullAddress() ?? 'Non spécifiée' }}
                        @if($order->deliveryAddress?->hasLocationLink())
                            <br><a href="{{ $order->deliveryAddress->location_link }}" target="_blank" class="btn-link">
                                <i class="ti ti-external-link"></i> Voir sur la carte
                            </a>
                        @endif
                    </p>
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
