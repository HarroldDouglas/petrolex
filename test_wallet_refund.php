<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Order;
use App\Services\Order\OrderService;

$orderService = app(OrderService::class);

echo "========================================\n";
echo "TEST DES 3 CAS D'ANNULATION\n";
echo "========================================\n\n";

// Préparer un customer
$customer = Customer::find(1);

// =========================================================
// CAS 1: Wallet = 0, commande payée, puis annulée
// =========================================================
echo "📋 CAS 1: Wallet = 0, commande PAID, puis annulée\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 0]);
echo 'Balance initiale: '.$customer->current_balance." FCFA\n";

$order1 = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS1-'.time(),
    'status' => 'paid', // Payé via Orange Money/MTN
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 0, // Aucun wallet utilisé
    'order_date' => now(),
    'paid_at' => now(),
]);

echo 'Commande créée: '.$order1->order_number."\n";
echo '  Status: '.$order1->status->value."\n";
echo '  Total: '.$order1->total_amount."\n";
echo '  Wallet used: '.$order1->wallet_amount_used."\n\n";

// Annuler
$orderService->update($order1, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation:\n";
echo '  Balance: '.$customer->current_balance." FCFA (attendu: 5000 car remboursé via wallet)\n";
echo '  ✅ RÉSULTAT: '.($customer->current_balance == '5000.00' ? 'OK' : 'ERREUR')."\n\n";

// =========================================================
// CAS 2: Wallet partiel (2000/5000), commande PENDING, puis annulée
// =========================================================
echo "📋 CAS 2: Wallet partiel (2000/5000), commande PENDING, puis annulée\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 2000]);
echo 'Balance initiale: '.$customer->current_balance." FCFA\n";

$order2 = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS2-'.time(),
    'status' => 'pending', // En attente de paiement externe
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 2000, // 2000 déduit du wallet
    'order_date' => now(),
]);

// SIMULER le débit du wallet qui aurait été fait par OrderService
$customer->update(['current_balance' => 2000 - 2000]); // 2000 - 2000 = 0

echo 'Commande créée: '.$order2->order_number."\n";
echo '  Status: '.$order2->status->value."\n";
echo '  Total: '.$order2->total_amount."\n";
echo '  Wallet used: '.$order2->wallet_amount_used."\n";
echo '  Balance après débit wallet: '.$customer->current_balance." FCFA\n\n";

// Annuler
$orderService->update($order2, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation:\n";
echo '  Balance: '.$customer->current_balance." FCFA (attendu: 2000 = 0 + 2000 remboursé)\n";
echo '  ✅ RÉSULTAT: '.($customer->current_balance == '2000.00' ? 'OK' : 'ERREUR')."\n\n";

// =========================================================
// CAS 3: Wallet couvre tout (7000/5000), commande PAID, puis annulée
// =========================================================
echo "📋 CAS 3: Wallet couvre tout (7000/5000), commande PAID, puis annulée\n";
echo "-------------------------------------------\n";

$customer->update(['current_balance' => 7000]);
echo 'Balance initiale: '.$customer->current_balance." FCFA\n";

$order3 = Order::create([
    'customer_id' => $customer->id,
    'distribution_center_id' => 1,
    'delivery_address_id' => 1,
    'order_number' => 'TEST-CAS3-'.time(),
    'status' => 'paid', // Payé entièrement par wallet
    'delivery_type' => 'normal',
    'subtotal' => 4500,
    'delivery_fee' => 500,
    'total_amount' => 5000,
    'wallet_amount_used' => 5000, // Tout payé par wallet
    'order_date' => now(),
    'paid_at' => now(),
]);

// SIMULER le débit du wallet qui aurait dû être fait lors de la création
$customer->update(['current_balance' => 7000 - 5000]); // 7000 - 5000 = 2000

echo 'Commande créée: '.$order3->order_number."\n";
echo '  Status: '.$order3->status->value."\n";
echo '  Total: '.$order3->total_amount."\n";
echo '  Wallet used: '.$order3->wallet_amount_used."\n";
echo '  Balance après débit wallet: '.$customer->current_balance." FCFA\n\n";

// Annuler
$orderService->update($order3, ['status' => 'cancelled']);
$customer->refresh();

echo "Après annulation:\n";
echo '  Balance: '.$customer->current_balance." FCFA (attendu: 7000 = 2000 + 5000 remboursé)\n";
echo '  ✅ RÉSULTAT: '.($customer->current_balance == '7000.00' ? 'OK' : 'ERREUR')."\n\n";

echo "========================================\n";
echo "FIN DES TESTS\n";
echo "========================================\n";
