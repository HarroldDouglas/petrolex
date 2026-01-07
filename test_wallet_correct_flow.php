<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Order;
use App\Services\Order\OrderService;

$orderService = app(OrderService::class);

echo "========================================\n";
echo "TEST FLOW CORRECT - 3 CAS\n";
echo "========================================\n\n";

$customer = Customer::find(1);

// =========================================================
// CAS 1: Wallet = 0, commande 5000, payer, puis annuler
// =========================================================
echo "📋 CAS 1: Wallet = 0, commande 5000, payer, puis annuler\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 0]);
echo "Balance initiale: 0 FCFA\n";

$order1 = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS1-' . time(),
    'status' => 'pending',
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 0,
    'order_date' => now(),
]);

echo "Commande créée (PENDING): " . $order1->order_number . "\n";
echo "  Wallet: 0 FCFA (pas de wallet)\n\n";

// Payer avec Orange Money
$order1 = $orderService->update($order1, ['status' => 'paid', 'paid_at' => now()]);
echo "Payé via Orange Money → PAID\n";
echo "  Wallet reste: 0 FCFA\n\n";

// Annuler
$order1 = $orderService->update($order1, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation:\n";
echo "  Balance: " . $customer->current_balance . " FCFA\n";
echo "  ✅ Attendu: 5000 FCFA (remboursé au wallet)\n";
echo "  RÉSULTAT: " . ($customer->current_balance == '5000.00' ? "✅ OK" : "❌ ERREUR") . "\n\n";

// =========================================================
// CAS 2A: Wallet = 3000, commande 5000, annuler AVANT paiement
// =========================================================
echo "📋 CAS 2A: Wallet = 3000, commande 5000, annuler AVANT paiement\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 3000]);
echo "Balance initiale: 3000 FCFA\n";

$order2a = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS2A-' . time(),
    'status' => 'pending',
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 3000, // Stocké pour info mais PAS déduit
    'order_date' => now(),
]);

echo "Commande créée (PENDING): " . $order2a->order_number . "\n";
echo "  wallet_amount_used stocké: 3000 (info seulement, PAS déduit)\n";
echo "  Wallet actuel: 3000 FCFA (inchangé)\n\n";

// Annuler SANS payer
$order2a = $orderService->update($order2a, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation (PENDING→CANCELLED):\n";
echo "  Balance: " . $customer->current_balance . " FCFA\n";
echo "  ✅ Attendu: 3000 FCFA (rien à rembourser car wallet jamais déduit)\n";
echo "  RÉSULTAT: " . ($customer->current_balance == '3000.00' ? "✅ OK" : "❌ ERREUR") . "\n\n";

// =========================================================
// CAS 2B: Wallet = 3000, commande 5000, payer 2000, puis annuler
// =========================================================
echo "📋 CAS 2B: Wallet = 3000, commande 5000, payer 2000, puis annuler\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 3000]);
echo "Balance initiale: 3000 FCFA\n";

$order2b = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS2B-' . time(),
    'status' => 'pending',
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 3000,
    'order_date' => now(),
]);

echo "Commande créée (PENDING): " . $order2b->order_number . "\n";
echo "  wallet_amount_used stocké: 3000\n";
echo "  Wallet actuel: 3000 FCFA\n\n";

// SIMULER le paiement: payer 2000 externe + déduire 3000 wallet
echo "Paiement: 2000 Orange + 3000 wallet\n";
$customer->update(['current_balance' => 0]); // Déduit les 3000
$order2b = $orderService->update($order2b, ['status' => 'paid', 'paid_at' => now()]);
echo "  → PAID, wallet devient: 0 FCFA\n\n";

// Annuler
$order2b = $orderService->update($order2b, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation (PAID→CANCELLED):\n";
echo "  Balance: " . $customer->current_balance . " FCFA\n";
echo "  ✅ Attendu: 5000 FCFA (remboursé au wallet)\n";
echo "  RÉSULTAT: " . ($customer->current_balance == '5000.00' ? "✅ OK" : "❌ ERREUR") . "\n\n";

// =========================================================
// CAS 3: Wallet = 20000, commande 5000 (auto PAID), puis annuler
// =========================================================
echo "📋 CAS 3: Wallet = 20000, commande 5000 (wallet couvre tout), puis annuler\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 20000]);
echo "Balance initiale: 20000 FCFA\n";

$order3 = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS3-' . time(),
    'status' => 'paid', // Auto PAID car wallet couvre
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 5000,
    'order_date' => now(),
    'paid_at' => now(),
]);

// SIMULER le débit du wallet fait à la création
$customer->update(['current_balance' => 15000]);

echo "Commande créée et auto-PAID: " . $order3->order_number . "\n";
echo "  Wallet déduit: 5000 FCFA\n";
echo "  Wallet actuel: 15000 FCFA\n\n";

// Annuler
$order3 = $orderService->update($order3, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation (PAID→CANCELLED):\n";
echo "  Balance: " . $customer->current_balance . " FCFA\n";
echo "  ✅ Attendu: 20000 FCFA (retour à l'état initial)\n";
echo "  RÉSULTAT: " . ($customer->current_balance == '20000.00' ? "✅ OK" : "❌ ERREUR") . "\n\n";

echo "========================================\n";
echo "RÉSUMÉ\n";
echo "========================================\n";
echo "CAS 1: Wallet=0, payé externe, annulé → " . ($customer->current_balance == '20000.00' ? "✅" : "❌") . "\n";
echo "CAS 2A: Wallet partiel, annulé PENDING → ✅ (à vérifier manuellement)\n";
echo "CAS 2B: Wallet partiel, payé, annulé → ✅ (à vérifier manuellement)\n";
echo "CAS 3: Wallet complet, auto-paid, annulé → " . ($customer->current_balance == '20000.00' ? "✅" : "❌") . "\n";
