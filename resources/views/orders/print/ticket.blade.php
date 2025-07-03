<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Commande - {{ $order->order_number ?? 'CMD-2024-001' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        .ticket {
            width: 80mm;
            margin: 0 auto;
            background: white;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .order-info {
            margin-bottom: 15px;
        }

        .order-number {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .label {
            font-weight: bold;
        }

        .customer-section, .delivery-section {
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }

        .items-section {
            margin-bottom: 15px;
        }

        .items-header {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            width: 30px;
            text-align: center;
        }

        .item-price {
            width: 50px;
            text-align: right;
        }

        .total-section {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-bottom: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .status {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            border: 1px solid #000;
        }

        @media print {
            body {
                margin: 0;
            }
            .ticket {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- En-tête -->
        <div class="header">
            <img src="{{ asset('assets/images/logo/isogaz-no-bg.png') }}" alt="Petrolex Logo" style="width: 100px; margin-bottom: 5px;">
            <div class="company-info">Service de livraison rapide</div>
            <div class="company-info">Tél: +33 1 23 45 67 89</div>
            <div class="company-info">www.delivery-express.fr</div>
        </div>

        <!-- Numéro de commande -->
        <div class="order-number">
            COMMANDE N° {{ $order->order_number ?? 'CMD-2024-001' }}
        </div>

        <!-- Informations de commande -->
        <div class="order-info">
            <div class="info-row">
                <span class="label">Date:</span>
                <span>{{ $order->order_date ? $order->order_date->format('d/m/Y H:i') : '15/01/2024 14:30' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Type:</span>
                <span>{{ $order->delivery_type === 'fast' ? 'Livraison Express' : 'Livraison Standard' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Statut:</span>
                <span>{{ $order->status ?? 'Confirmée' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Paiement:</span>
                <span>{{ $order->payment_method ?? 'Carte Bancaire' }}</span>
            </div>
        </div>

        <!-- Statut -->
        <div class="status">
            {{ $order->payment_status ?? 'PAYÉ' }}
        </div>

        <!-- Client -->
        <div class="customer-section">
            <div class="section-title">CLIENT</div>
            <div>{{ $order->customer->name ?? 'Jean Dupont' }}</div>
            <div>{{ $order->customer->phone ?? '+33 6 12 34 56 78' }}</div>
            <div>{{ $order->customer->email ?? 'jean.dupont@email.com' }}</div>
        </div>

        <!-- Adresse de livraison -->
        <div class="delivery-section">
            <div class="section-title">LIVRAISON</div>
            <div>{{ $order->deliveryAddress->address ?? '123 Rue de la Paix' }}</div>
            <div>{{ ($order->deliveryAddress->postal_code ?? '75001') . ' ' . ($order->deliveryAddress->city ?? 'Paris') }}</div>
            @if($order->deliveryAddress->additional_info ?? 'Appartement 4B')
                <div>{{ $order->deliveryAddress->additional_info ?? 'Appartement 4B' }}</div>
            @endif
            @if($order->delivery_date)
                <div><strong>Livraison prévue:</strong> {{ $order->delivery_date->format('d/m/Y H:i') }}</div>
            @endif
        </div>

        <!-- Articles -->
        <div class="items-section">
            <div class="section-title">ARTICLES COMMANDÉS</div>
            <div class="items-header">
                <div class="item-row">
                    <span class="item-name">Article</span>
                    <span class="item-qty">Qté</span>
                    <span class="item-price">Prix</span>
                </div>
            </div>
            
            <!-- Articles statiques pour demo -->
            <div class="item-row">
                <span class="item-name">Pizza Margherita</span>
                <span class="item-qty">2</span>
                <span class="item-price">24,00€</span>
            </div>
            <div class="item-row">
                <span class="item-name">Coca Cola 33cl</span>
                <span class="item-qty">2</span>
                <span class="item-price">5,00€</span>
            </div>
            <div class="item-row">
                <span class="item-name">Tiramisu</span>
                <span class="item-qty">1</span>
                <span class="item-price">6,50€</span>
            </div>

            {{-- Uncomment when you have order items relationship
            @foreach($order->items as $item)
                <div class="item-row">
                    <span class="item-name">{{ $item->product->name }}</span>
                    <span class="item-qty">{{ $item->quantity }}</span>
                    <span class="item-price">{{ number_format($item->total_price, 2) }}€</span>
                </div>
            @endforeach
            --}}
        </div>

        <!-- Totaux -->
        <div class="total-section">
            <div class="total-row">
                <span>Sous-total:</span>
                <span>{{ number_format($order->subtotal ?? 35.50, 2) }}€</span>
            </div>
            <div class="total-row">
                <span>Frais de livraison:</span>
                <span>{{ number_format($order->delivery_fee ?? 3.50, 2) }}€</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>{{ number_format($order->total_amount ?? 39.00, 2) }}€</span>
            </div>
        </div>

        <!-- Centre de distribution -->
        <div class="delivery-section">
            <div class="section-title">CENTRE DE DISTRIBUTION</div>
            <div>{{ $order->distributionCenter->name ?? 'Centre Paris Nord' }}</div>
            <div>{{ $order->distributionCenter->phone ?? '+33 1 45 67 89 01' }}</div>
        </div>

        <!-- Commentaires -->
        @if($order->comments ?? 'Sonner à l\'interphone SVP')
            <div class="delivery-section">
                <div class="section-title">COMMENTAIRES</div>
                <div>{{ $order->comments ?? 'Sonner à l\'interphone SVP' }}</div>
            </div>
        @endif

        <!-- Pied de page -->
        <div class="footer">
            <div>Merci pour votre commande !</div>
            <div>Gardez ce ticket jusqu'à réception</div>
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
