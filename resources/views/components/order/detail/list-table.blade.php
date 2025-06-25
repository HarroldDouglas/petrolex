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
                            <th scope="col">Prix unitaire</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-start">
                                            <h6 class="mb-0">
                                                {{ $item->displayName }}
                                            </h6>
                                            <p class="f-w-500 m-0 text-muted f-s-13">Type:
                                                <span class="text-secondary">
                                                    {{ $item->orderItem->productCategory->product_type }}
                                                </span>
                                            </p>
                                            @if ($item->isBottle() && $item->orderItem->bottle_type)
                                                <p class="f-w-500 m-0 text-muted f-s-13">Option: <span
                                                        class="text-secondary">{{ $item->orderItem->bottle_type->label }}</span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="f-w-600">{{ $item->groupedQuantity }}</td>
                                <td class="text-success f-w-500">{{ $item->getUnitPrice()}}
                                </td>
                                <td class="text-success f-w-500">
                                    {{ $item->groupedTotalPrice}}
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
                                <strong>{{ number_format($order->delivery_fee, 0, ',', ' ') }}</strong>
                            </th>
                        </tr>
                        <tr>
                            <th scope="col" colspan="1" class="text-start">Total</th>
                            <th scope="col" colspan="3" class="text-end f-w-500">
                                <strong>{{ number_format($order->total_amount, 0, ',', ' ') }}</strong>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
