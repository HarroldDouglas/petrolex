<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .header:after {
            content: "";
            display: table;
            clear: both;
        }
        .logo-container {
            float: left;
            width: 40%;
        }
        .company-info {
            float: right;
            width: 40%;
            text-align: right;
        }
        .invoice-title {
            text-align: center;
            font-size: 20px;
            margin: 20px 0;
            font-weight: bold;
        }
        .invoice-details {
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-details:after {
            content: "";
            display: table;
            clear: both;
        }
        .client-info {
            float: left;
            width: 45%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .totals {
            width: 100%;
            margin-top: 20px;
        }
        .total-right {
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
            clear: both;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            text-align: left;
        }
        .total-value {
            display: inline-block;
            width: 120px;
            text-align: right;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #333;
        }
        .payment-info {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer strong {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <h2>PETROLEX SARL</h2>
        </div>
        <div class="company-info">
            <p>
                123 Avenue du Pétrole<br>
                Douala, Cameroun<br>
                Tél: +237 233 123 456<br>
                Email: contact@petrolex.cm
            </p>
        </div>
    </div>

    <h1 class="invoice-title">FACTURE</h1>

    <div class="invoice-details">
        <div class="client-info">
            <h3>Facturé à:</h3>
            <p>
                <strong>{{ $order->customer->name ?? 'Client' }}</strong><br>
                {{ $order->customer->address ?? 'Adresse non disponible' }}<br>
                {{ $order->customer->phone ?? 'Téléphone non disponible' }}<br>
                {{ $order->customer->email ?? 'Email non disponible' }}
            </p>
        </div>
        <div class="invoice-info">
            <h3>Détails de la facture:</h3>
            <p>
                <strong>N° Facture:</strong> {{ $order->order_number }}<br>
                <strong>Date:</strong> {{ $order->created_at ? $order->created_at->format('d/m/Y') : date('d/m/Y') }}<br>
                <strong>Statut:</strong> {{ $order->status->label ?? 'En cours' }}
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="45%">Description</th>
                <th width="15%">Quantité</th>
                <th width="20%">Prix unitaire</th>
                <th width="20%">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedItems as $groupedItem)
                <tr>
                    <td>{{ $groupedItem->displayName }}</td>
                    <td>{{ $groupedItem->groupedQuantity }}</td>
                    <td>{{ number_format($groupedItem->getUnitPrice(), 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($groupedItem->groupedTotalPrice, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-right">
            <div class="total-row">
                <span class="total-label">Sous-total:</span>
                <span class="total-value">{{ number_format($order->items_total_price ?? $order->subtotal ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="total-row">
                <span class="total-label">Frais de livraison:</span>
                <span class="total-value">{{ number_format($order->delivery_fee ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            @if(($order->discount ?? 0) > 0)
            <div class="total-row">
                <span class="total-label">Réduction:</span>
                <span class="total-value">{{ number_format($order->discount ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">{{ number_format($order->total_price ?? $order->total_amount ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    <div class="payment-info">
        <h3>Informations de paiement</h3>
        <p><strong>Méthode de paiement:</strong> {{ $order->payment_method->label ?? 'Espèces' }}</p>
        <p><strong>Statut du paiement:</strong> {{ $order->payment_status->label ?? 'Payé' }}</p>
    </div>

    <div class="footer">
        <p><strong>Merci pour votre confiance! Pour toute question concernant cette facture, veuillez nous contacter.</strong></p>
        <p><strong>PETROLEX SARL | RC: RC/DLA/2020/B/1234 | NIU: M012345678901</strong></p>
    </div>
</body>
</html>
