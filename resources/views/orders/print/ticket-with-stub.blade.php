<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - {{ $order->reference }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
        }
        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .company-info {
            text-align: right;
        }
        .invoice-title {
            text-align: center;
            font-size: 24px;
            margin: 20px 0;
            font-weight: bold;
            color: #2c3e50;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .client-info, .invoice-info {
            width: 48%;
        }
        .invoice-info {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        thead th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .totals {
            margin-top: 30px;
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            margin: 5px 0;
        }
        .total-label {
            width: 150px;
            font-weight: bold;
            text-align: left;
        }
        .total-value {
            width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #2c3e50;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .payment-info {
            margin: 30px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container" id="printable">
        <div class="header">
            <div>
                @if(file_exists(public_path('assets/images/logos/logo.png')))
                    <img src="{{ asset('assets/images/logos/logo.png') }}" alt="Logo Petrolex" class="logo">
                @else
                    <h2>PETROLEX CAMEROUN SA</h2>
                @endif
            </div>
            <div class="company-info">
                <h3>PETROLEX CAMEROUN SA</h3>
                <p>Bonapriso, Rue D. Savio, face centre PROMED<br>
                Douala, Cameroun<br>
                Tél : 237 233402714<br>
                Email : petrolex@petrolex.net</p>
            </div>
        </div>

        <h1 class="invoice-title">FACTURE</h1>

        <div class="invoice-details">
            <div class="client-info">
                <h4>Facturé à:</h4>
                <p>
                    <strong>{{ $order->customer?->user->first_name }} {{ $order->customer?->user->last_name }}</strong><br>
                    @if($order->delivery_address_id && $order->deliveryAddress)
                        {{ $order->deliveryAddress->label }}<br>
                        @if($order->deliveryAddress->address)
                            {{ $order->deliveryAddress->address }}<br>
                        @endif
                        @if($order->deliveryAddress->neighborhood)
                            {{ $order->deliveryAddress->neighborhood->name ?? $order->deliveryAddress->neighborhood }},
                        @endif
                        <strong>Tél:</strong> {{ $order->deliveryAddress->phone ?? ($order->customer?->phone ?? 'Non disponible') }}
                        @if($order->deliveryAddress->hasLocationLink())
                            <br><strong>Localisation:</strong> {{ $order->deliveryAddress->location_link }}
                        @elseif($order->deliveryAddress->hasGpsCoordinates())
                            <br><strong>GPS:</strong> {{ $order->deliveryAddress->latitude }}, {{ $order->deliveryAddress->longitude }}
                        @endif
                        @if($order->customer?->email)
                        <br><strong>Email:</strong> {{ $order->customer->email }}
                        @endif
                    @else
                        {{ $order->customer?->address ?? 'Adresse non disponible' }}<br>
                        <strong>Tél:</strong> {{ $order->customer?->phone ?? 'Non disponible' }}
                        @if($order->customer?->email)
                        <br><strong>Email:</strong> {{ $order->customer->email }}
                        @endif
                    @endif
                </p>
                @if($order->comments)
                    <p><strong>Instructions:</strong> {{ $order->comments }}</p>
                @endif
            </div>
            <div class="invoice-info">
                <h4>Détails de la facture:</h4>
                <p>
                    <strong>N° Facture:</strong> {{ $order->order_number ?? $order->reference ?? $order->id }}<br>
                    <strong>Date:</strong> {{ $order->order_date ? $order->order_date->format('d/m/Y') : ($order->created_at ? $order->created_at->format('d/m/Y') : date('d/m/Y')) }}<br>
                    <strong>Statut:</strong> {{ $order->status?->label ?? $order->status?->name ?? 'En cours' }}<br>
                    <strong>Type de livraison:</strong> {{ $order->delivery_type?->label ?? 'Standard' }}
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedItems as $groupedItem)
                    <tr>
                        <td>{{ $groupedItem->displayName }}</td>
                        <td>{{ $groupedItem->groupedQuantity }}</td>
                        <td>{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($groupedItem->getUnitPrice()) }}</td>
                        <td>{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($groupedItem->groupedTotalPrice) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <div class="total-label">Sous-total:</div>
                <div class="total-value">{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->items_total_price ?? $order->subtotal ?? 0) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Frais de livraison:</div>
                <div class="total-value">{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->delivery_fee) }}</div>
            </div>
            @if(($order->discount ?? 0) > 0)
            <div class="total-row">
                <div class="total-label">Réduction:</div>
                <div class="total-value">{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->discount ?? 0) }}</div>
            </div>
            @endif
            <div class="total-row grand-total">
                <div class="total-label">TOTAL:</div>
                <div class="total-value">{{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount ?? 0) }}</div>
            </div>
        </div>

        <div class="payment-info">
            <h4>Informations de paiement</h4>
            <p><strong>Méthode de paiement:</strong> {{ $order->payment_method->label }}</p>
            <p><strong>Statut du paiement:</strong> {{ $order->payment_status->label }}</p>
        </div>

        <div class="footer">
            <p><strong>Merci pour votre confiance! Pour toute question concernant cette facture, veuillez nous contacter.</strong></p>
            <p><strong>PETROLEX CAMEROUN SA | RC: RC/DLA/2020/B/1234 | NIU: M012345678901</strong></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
