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
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="Logo Petrolex" class="logo">
            </div>
            <div class="company-info">
                <h3>PETROLEX SARL</h3>
                <p>123 Avenue du Pétrole<br>
                Douala, Cameroun<br>
                Tél: +237 233 123 456<br>
                Email: contact@petrolex.cm</p>
            </div>
        </div>

        <h1 class="invoice-title">FACTURE</h1>

        <div class="invoice-details">
            <div class="client-info">
                <h4>Facturé à:</h4>
                <p>
                    <strong>{{ $order->customer->name ?? 'Client' }}</strong><br>
                    {{ $order->customer->address ?? 'Adresse non disponible' }}<br>
                    {{ $order->customer->phone ?? 'Téléphone non disponible' }}<br>
                    {{ $order->customer->email ?? 'Email non disponible' }}
                </p>
            </div>
            <div class="invoice-info">
                <h4>Détails de la facture:</h4>
                <p>
                    <strong>N° Facture:</strong> {{ $order->reference }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
                    <strong>Statut:</strong> {{ $order->status->name ?? 'En cours' }}
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
                        <td>{{ number_format($groupedItem->getUnitPrice(), 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($groupedItem->groupedTotalPrice, 0, ',', ' ') }} FCFA</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <div class="total-label">Sous-total:</div>
                <div class="total-value">{{ number_format($order->subtotal ?? 0, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="total-row">
                <div class="total-label">Frais de livraison:</div>
                <div class="total-value">{{ number_format($order->delivery_fee ?? 0, 0, ',', ' ') }} FCFA</div>
            </div>
            @if(($order->discount ?? 0) > 0)
            <div class="total-row">
                <div class="total-label">Réduction:</div>
                <div class="total-value">{{ number_format($order->discount ?? 0, 0, ',', ' ') }} FCFA</div>
            </div>
            @endif
            <div class="total-row grand-total">
                <div class="total-label">TOTAL:</div>
                <div class="total-value">{{ number_format($order->total_amount ?? 0, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <div class="payment-info">
            <h4>Informations de paiement</h4>
            <p><strong>Méthode de paiement:</strong> {{ $order->payment_method ?? 'Espèces' }}</p>
            <p><strong>Statut du paiement:</strong> {{ $order->payment_status ?? 'Payé' }}</p>
        </div>

        <div class="footer">
            <p><strong>Merci pour votre confiance! Pour toute question concernant cette facture, veuillez nous contacter.</strong></p>
            <p><strong>PETROLEX SARL | RC: RC/DLA/2020/B/1234 | NIU: M012345678901</strong></p>
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
