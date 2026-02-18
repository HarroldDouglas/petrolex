<div wire:poll.15s>
    @if ($order)
        <!-- Order Details start -->
        <div class="row order-details">
            @if ($order->canScanBottles())
                <div class="collapse mt-3" id="collapseScanBottles">
                    <div class="card card-body border border-primary">
                        <h5 class="card-title mb-3">Lier des bouteilles pour la livraison</h5>
                        @livewire('order.order-scan-bottles', ['order' => $order], key('scan-'.$order->id.'-'.$order->status->value))
                    </div>
                </div>
            @endif

            <div class="col-xxl-8 mt-3">
                <div class="row">
                    <!-- Order Details Bloc start -->
                    <x-order.detail.order-details-bloc :order="$order" />
                    <!-- Order Details Bloc end -->

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
