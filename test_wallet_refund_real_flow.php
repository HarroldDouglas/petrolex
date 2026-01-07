<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Services\Order\OrderService;
use App\DTOs\Order\CreateOrderWithoutPaymentDTO;
use App\DTOs\Order\OrderItemDTO;
use App\Enums\BottleOrderType;

$orderService = app(OrderService::class);

echo "========================================\n";
echo "TEST FLOW RÉEL - 3 CAS D'ANNULATION\n";
echo "========================================\n\n";

// Préparer un customer avec une adresse
$customer = Customer::find(1);
$deliveryAddress = CustomerDeliveryAddress::where('customer_id', $customer->id)->first();

if (!$deliveryAddress) {
    echo "❌ ERREUR: Pas d'adresse de livraison pour le customer 1\n";
    exit(1);
}

// =========================================================
// CAS 1: Wallet = 0, commande payée via Orange/MTN, puis annulée
// =========================================================
echo "📋 CAS 1: Wallet = 0, créer commande (sera PENDING), payer, puis annuler\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 0]);
$balanceInitiale1 = (float) $customer->current_balance;
echo "Balance initiale: $balanceInitiale1 FCFA\n";

// Créer commande via OrderService (flow réel)
$orderDTO1 = new CreateOrderWithoutPaymentDTO(
    customer_id: $customer->id,
    delivery_address_id: $deliveryAddress->id,
    distribution_center_id: 1,
    delivery_type: 'normal',
    items: [
        new OrderItemDTO(
            product_category_id: 1,
            quantity: 1,
            unit_price: 6000.00,
            total_price: 6000.00,
            option: BottleOrderType::RECHARGE()
        )
    ],
    delivery_fee: 500.00,
    total_amount: 6500.00,
    comments: null
);

$order1 = $orderService->createWithoutPayment($orderDTO1);
$customer->refresh();

echo "Commande créée: {$order1->order_number}\n";
echo "  Status: {$order1->status->value}\n";
echo "  Total: {$order1->total_amount}\n";
echo "  Wallet used: {$order1->wallet_amount_used}\n";
echo "  Balance après création: {$customer->current_balance} FCFA\n";

// Simuler paiement externe (Orange Money) - marquer comme PAID
$order1 = $orderService->update($order1, ['status' => 'paid', 'paid_at' => now()]);
echo "  Commande marquée PAID (paiement externe)\n\n";

// Annuler
$order1 = $orderService->update($order1, ['status' => 'cancelled']);
$customer->refresh();

$balanceFinale1 = (float) $customer->current_balance;
$attendu1 = 6500.00; // Doit rembourser le total_amount

echo "Après annulation:\n";
echo "  Balance: $balanceFinale1 FCFA (attendu: $attendu1 FCFA)\n";
echo "  ✅ RÉSULTAT: " . ($balanceFinale1 == $attendu1 ? "OK" : "ERREUR (différence: " . ($balanceFinale1 - $attendu1) . ")") . "\n\n";

// =========================================================
// CAS 2: Wallet partiel (3000/6500), commande PENDING, puis annulée
// =========================================================
echo "📋 CAS 2: Wallet partiel (3000/6500), commande PENDING, puis annulée\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 3000]);
$balanceInitiale2 = (float) $customer->current_balance;
echo "Balance initiale: $balanceInitiale2 FCFA\n";

$orderDTO2 = new CreateOrderWithoutPaymentDTO(
    customer_id: $customer->id,
    delivery_address_id: $deliveryAddress->id,
    distribution_center_id: 1,
    delivery_type: 'normal',
    items: [
        new OrderItemDTO(
            product_category_id: 1,
            quantity: 1,
            unit_price: 6000.00,
            total_price: 6000.00,
            option: BottleOrderType::RECHARGE()
        )
    ],
    delivery_fee: 500.00,
    total_amount: 6500.00,
    comments: null
);

$order2 = $orderService->createWithoutPayment($orderDTO2);
$customer->refresh();

echo "Commande créée: {$order2->order_number}\n";
echo "  Status: {$order2->status->value}\n";
echo "  Total: {$order2->total_amount}\n";
echo "  Wallet used: {$order2->wallet_amount_used}\n";
echo "  Balance après déduction wallet: {$customer->current_balance} FCFA\n\n";

// Annuler sans payer (reste PENDING)
$order2 = $orderService->update($order2, ['status' => 'cancelled']);
$customer->refresh();

$balanceFinale2 = (float) $customer->current_balance;
$attendu2 = 3000.00; // Balance initiale (3000 - 3000 utilisé + 3000 remboursé)

echo "Après annulation:\n";
echo "  Balance: $balanceFinale2 FCFA (attendu: $attendu2 FCFA)\n";
echo "  ✅ RÉSULTAT: " . ($balanceFinale2 == $attendu2 ? "OK" : "ERREUR (différence: " . ($balanceFinale2 - $attendu2) . ")") . "\n\n";

// =========================================================
// CAS 3: Wallet couvre tout (10000/6500), commande auto PAID, puis annulée
// =========================================================
echo "📋 CAS 3: Wallet couvre tout (10000/6500), commande auto PAID, puis annulée\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 10000]);
$balanceInitiale3 = (float) $customer->current_balance;
echo "Balance initiale: $balanceInitiale3 FCFA\n";

$orderDTO3 = new CreateOrderWithoutPaymentDTO(
    customer_id: $customer->id,
    delivery_address_id: $deliveryAddress->id,
    distribution_center_id: 1,
    delivery_type: 'normal',
    items: [
        new OrderItemDTO(
            product_category_id: 1,
            quantity: 1,
            unit_price: 6000.00,
            total_price: 6000.00,
            option: BottleOrderType::RECHARGE()
        )
    ],
    delivery_fee: 500.00,
    total_amount: 6500.00,
    comments: null
);

$order3 = $orderService->createWithoutPayment($orderDTO3);
$customer->refresh();

echo "Commande créée: {$order3->order_number}\n";
echo "  Status: {$order3->status->value} (doit être 'paid' car wallet couvre tout)\n";
echo "  Total: {$order3->total_amount}\n";
echo "  Wallet used: {$order3->wallet_amount_used}\n";
echo "  Balance après déduction wallet: {$customer->current_balance} FCFA\n\n";

// Annuler
$order3 = $orderService->update($order3, ['status' => 'cancelled']);
$customer->refresh();

$balanceFinale3 = (float) $customer->current_balance;
$attendu3 = 10000.00; // Balance initiale (10000 - 6500 utilisé + 6500 remboursé)

echo "Après annulation:\n";
echo "  Balance: $balanceFinale3 FCFA (attendu: $attendu3 FCFA)\n";
echo "  ✅ RÉSULTAT: " . ($balanceFinale3 == $attendu3 ? "OK" : "ERREUR (différence: " . ($balanceFinale3 - $attendu3) . ")") . "\n\n";

echo "========================================\n";
echo "RÉSUMÉ FINAL\n";
echo "========================================\n";
$cas1Ok = $balanceFinale1 == $attendu1;
$cas2Ok = $balanceFinale2 == $attendu2;
$cas3Ok = $balanceFinale3 == $attendu3;

echo "CAS 1 (wallet=0, PAID→cancelled): " . ($cas1Ok ? "✅ OK" : "❌ ERREUR") . "\n";
echo "CAS 2 (wallet partiel, PENDING→cancelled): " . ($cas2Ok ? "✅ OK" : "❌ ERREUR") . "\n";
echo "CAS 3 (wallet complet, auto PAID→cancelled): " . ($cas3Ok ? "✅ OK" : "❌ ERREUR") . "\n\n";

if ($cas1Ok && $cas2Ok && $cas3Ok) {
    echo "🎉 TOUS LES CAS PASSENT - PRÊT POUR DÉPLOIEMENT!\n";
} else {
    echo "⚠️ CERTAINS CAS ÉCHOUENT - NE PAS DÉPLOYER!\n";
}
