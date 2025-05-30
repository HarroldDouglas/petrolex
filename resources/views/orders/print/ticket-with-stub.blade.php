<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Commande avec Souche - {{ $order->order_number ?? 'CMD-2024-001' }}</title>
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

        .page {
            width: 80mm;
            margin: 0 auto;
            background: white;
        }

        .ticket-section {
            padding: 10px;
            margin-bottom: 20px;
        }

        .separator {
            text-align: center;
            margin: 20px 0;
            border-bottom: 2px dashed #000;
            position: relative;
            font-size: 10px;
            font-weight: bold;
        }

        .separator::after {
            content: attr(data-text);
            background: white;
            padding: 0 10px;
            position: absolute;
            left: 50%;
            top: -8px;
            transform: translateX(-50%);
        }

        .stub-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 5px;
            border: 2px solid #000;
            background: #f0f0f0;
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

        .signature-section {
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }

        .signature-box {
            margin-top: 10px;
            border: 1px solid #000;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }

        .instructions {
            background: #f9f9f9;
            border: 1px solid #ccc;
            padding: 10px;
            margin: 15px 0;
            font-size: 10px;
        }

        .delivery-receipt {
            background: #fff;
        }

        .customer-copy {
            background: #f8f8f8;
        }

        @media print {
            body {
                margin: 0;
            }
            .page {
                width: 100%;
                margin: 0;
            }
            .separator {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        
        <!-- DÉCHARGE LIVREUR -->
        <div class="ticket-section delivery-receipt">
            <div class="stub-title">📋 DÉCHARGE LIVREUR</div>
            
            <div class="header">
                <div class="company-name">DELIVERY EXPRESS</div>
                <div class="company-info">Service de livraison rapide</div>
                <div class="company-info">Tél: +33 1 23 45 67 89</div>
            </div>

            <div class="order-number">
                COMMANDE N° {{ $order->order_number ?? 'CMD-2024-001' }}
            </div>

            <div class="order-info">
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span>{{ $order->order_date ? $order->order_date->format('d/m/Y H:i') : '15/01/2024 14:30' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Type:</span>
                    <span>{{ $order->delivery_type === 'fast' ? 'Express' : 'Standard' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Total:</span>
                    <span class="label">{{ number_format($order->total_amount ?? 39.00, 2) }}€</span>
                </div>
            </div>

            <div class="customer-section">
                <div class="section-title">CLIENT</div>
                <div>{{ $order->customer->name ?? 'Jean Dupont' }}</div>
                <div>{{ $order->customer->phone ?? '+33 6 12 34 56 78' }}</div>
            </div>

            <div class="delivery-section">
                <div class="section-title">ADRESSE DE LIVRAISON</div>
                <div>{{ $order->deliveryAddress->address ?? '123 Rue de la Paix' }}</div>
                <div>{{ ($order->deliveryAddress->postal_code ?? '75001') . ' ' . ($order->deliveryAddress->city ?? 'Paris') }}</div>
            </div>

            <div class="signature-section">
                <div class="section-title">SIGNATURES</div>
                <div style="margin-bottom: 10px;">
                    <div style="margin-bottom: 5px;"><strong>Livreur:</strong> {{ $order->deliveryPerson->name ?? '________________' }}</div>
                    <div class="signature-box">Signature du livreur</div>
                </div>
                <div>
                    <div style="margin-bottom: 5px;"><strong>Client:</strong> ________________</div>
                    <div class="signature-box">Signature du client</div>
                </div>
            </div>

            <div class="instructions">
                <strong>INSTRUCTIONS:</strong><br>
                ✓ Livraison effectuée le: ___/___/_____ à ___h___<br>
                ✓ Colis remis en main propre: ☐ Oui ☐ Non<br>
                ✓ Commentaires: _________________________
            </div>
        </div>

        <div class="separator" data-text="✂️ DÉTACHER ICI - SOUCHE CLIENT ✂️"></div>

        <!-- SOUCHE CLIENT -->
        <div class="ticket-section customer-copy">
            <div class="stub-title">📄 SOUCHE CLIENT</div>
            
            <div class="header">
                <div class="company-name">DELIVERY EXPRESS</div>
                <div class="company-info">Service de livraison rapide</div>
                <div class="company-info">Tél: +33 1 23 45 67 89</div>
                <div class="company-info">www.delivery-express.fr</div>
            </div>

            <div class="order-number">
                COMMANDE N° {{ $order->order_number ?? 'CMD-2024-001' }}
            </div>

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

            <div class="status">
                {{ $order->payment_status ?? 'PAYÉ' }}
            </div>

            <div class="customer-section">
                <div class="section-title">CLIENT</div>
                <div>{{ $order->customer->name ?? 'Jean Dupont' }}</div>
                <div>{{ $order->customer->phone ?? '+33 6 12 34 56 78' }}</div>
                <div>{{ $order->customer->email ?? 'jean.dupont@email.com' }}</div>
            </div>

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
            </div>

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

            @if($order->comments ?? 'Sonner à l\'interphone SVP')
                <div class="delivery-section">
                    <div class="section-title">COMMENTAIRES</div>
                    <div>{{ $order->comments ?? 'Sonner à l\'interphone SVP' }}</div>
                </div>
            @endif

            <div class="footer">
                <div>Merci pour votre commande !</div>
                <div>Conservez cette souche comme preuve d'achat</div>
                <div>{{ now()->format('d/m/Y H:i:s') }}</div>
            </div>
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
