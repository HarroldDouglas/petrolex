<?php

// database/seeders/Development/SupplySeeder.php

namespace Database\Seeders\Development;

use App\Enums\SupplierDeliveryStatus;
use App\Models\BottleType;
use App\Models\Supply;
use App\Models\SupplyItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating supplies for development...');

        $this->createPendingSupplies();
        $this->createPartiallyDeliveredSupplies();
        $this->createCompletedSupplies();
        $this->createCancelledSupplies();

        $this->command->info('Development supplies created successfully!');
    }

    /**
     * Create supplies with pending status (approvisionnements en attente)
     */
    private function createPendingSupplies(): void
    {
        $this->command->info('Creating pending supplies...');

        // Créer 5 approvisionnements en attente
        $pendingSupplies = Supply::factory()
            ->count(5)
            ->create([
                'status' => SupplierDeliveryStatus::PENDING(),
                'delivery_date' => null, // Pas de date de livraison car en attente
                'comments' => function () {
                    $possibleComments = [
                        'Commande en attente de confirmation du fournisseur.',
                        'Approvisionnement planifié, en attente de livraison.',
                        null, // Parfois pas de commentaires
                    ];

                    return $possibleComments[array_rand($possibleComments)];
                },
            ]);

        $this->addSupplyItems($pendingSupplies, false);

        $this->command->info('5 pending supplies created.');
    }

    /**
     * Create supplies with partially delivered status
     */
    private function createPartiallyDeliveredSupplies(): void
    {
        $this->command->info('Creating partially delivered supplies...');

        // Créer 3 approvisionnements partiellement livrés
        $partiallyDeliveredSupplies = Supply::factory()
            ->count(3)
            ->create([
                'status' => SupplierDeliveryStatus::PARTIALLY_DELIVERED(),
                'delivery_date' => now()->subDays(rand(1, 7)), // Livraison récente
                'comments' => function () {
                    $possibleComments = [
                        'Livraison partielle effectuée. Reste à livrer programmé pour la semaine prochaine.',
                        'Stock partiellement reçu. Fournisseur contacté pour le reste.',
                        'Première partie de la commande reçue, seconde partie en transit.',
                    ];

                    return $possibleComments[array_rand($possibleComments)];
                },
            ]);

        $this->addSupplyItems($partiallyDeliveredSupplies, true);

        $this->command->info('3 partially delivered supplies created.');
    }

    /**
     * Create supplies with completed status
     */
    private function createCompletedSupplies(): void
    {
        $this->command->info('Creating completed supplies...');

        // Créer 7 approvisionnements complétés
        $completedSupplies = Supply::factory()
            ->count(7)
            ->create([
                'status' => SupplierDeliveryStatus::COMPLETED(),
                'delivery_date' => now()->subDays(rand(10, 60)), // Livraison plus ancienne
                'comments' => function () {
                    $possibleComments = [
                        'Livraison complète reçue et vérifiée.',
                        'Stock mis à jour après réception complète.',
                        'Approvisionnement terminé et vérifié par le magasinier.',
                        'Livraison conforme à la commande.',
                    ];

                    return $possibleComments[array_rand($possibleComments)];
                },
            ]);

        $this->addSupplyItems($completedSupplies, true, true);

        $this->command->info('7 completed supplies created.');
    }

    /**
     * Create supplies with cancelled status
     */
    private function createCancelledSupplies(): void
    {
        $this->command->info('Creating cancelled supplies...');

        // Créer 2 approvisionnements annulés
        $cancelledSupplies = Supply::factory()
            ->count(2)
            ->create([
                'status' => SupplierDeliveryStatus::CANCELLED(),
                'delivery_date' => null, // Pas de livraison car annulée
                'comments' => function () {
                    $possibleComments = [
                        'Commande annulée suite à un retard excessif du fournisseur.',
                        'Approvisionnement annulé pour changement de fournisseur.',
                        'Annulé : produits non disponibles chez le fournisseur.',
                    ];

                    return $possibleComments[array_rand($possibleComments)];
                },
            ]);

        $this->addSupplyItems($cancelledSupplies, false);

        $this->command->info('2 cancelled supplies created.');
    }

    /**
     * Add supply items to supplies
     *
     * @param  mixed  $supplies  Collection of Supply models
     * @param  bool  $hasDelivered  Whether some items have been delivered
     * @param  bool  $fullyDelivered  Whether all items are fully delivered
     */
    private function addSupplyItems($supplies, bool $hasDelivered = false, bool $fullyDelivered = false): void
    {
        $bottleTypes = BottleType::all();

        if ($bottleTypes->isEmpty()) {
            $this->command->error('No bottle types found. Run BottleTypeSeeder first.');

            return;
        }

        foreach ($supplies as $supply) {
            // Nombre de types de bouteilles pour cet approvisionnement (1-3)
            $numBottleTypes = rand(1, 3);
            $selectedBottleTypes = $bottleTypes->random($numBottleTypes);

            foreach ($selectedBottleTypes as $bottleType) {
                // Entre 10 et 50 bouteilles de chaque type commandées
                $quantity = rand(10, 50);

                // Calculer la quantité livrée en fonction du statut
                $deliveredQuantity = 0;

                if ($supply->status === SupplierDeliveryStatus::COMPLETED()) {
                    // Si complété, tout est livré
                    $deliveredQuantity = $quantity;
                } elseif ($supply->status === SupplierDeliveryStatus::PARTIALLY_DELIVERED()) {
                    // Si partiellement livré, entre 1 et (quantité-1) sont livrées
                    $deliveredQuantity = rand(1, $quantity - 1);
                }
                // Pour PENDING et CANCELLED, deliveredQuantity reste à 0

                // Créer l'élément d'approvisionnement
                SupplyItem::create([
                    'supply_id' => $supply->id,
                    'bottle_type_id' => $bottleType->id,
                    'quantity' => $quantity,
                    'delivered_quantity' => $deliveredQuantity,
                    'price_unit' => $bottleType->price,
                    'comments' => $this->generateItemComment($supply->status, $deliveredQuantity, $quantity),
                ]);

                // Pour les approvisionnements complétés ou partiels, mettre à jour le stock des bouteilles
                if ($deliveredQuantity > 0) {
                    $this->createBottleRecords($supply, $bottleType, $deliveredQuantity);
                }
            }

            // Mettre à jour le total de l'approvisionnement
            $this->updateSupplyTotals($supply);
        }
    }

    /**
     * Generate a comment for a supply item based on status
     */
    private function generateItemComment($status, $delivered, $total): ?string
    {
        if ($status === SupplierDeliveryStatus::PENDING()) {
            return null;
        } elseif ($status === SupplierDeliveryStatus::CANCELLED()) {
            $cancellationReasons = [
                'Produit indisponible chez le fournisseur',
                'Remplacé par un autre type de bouteille',
                'Prix trop élevé, commande annulée',
                'Qualité insuffisante',
            ];

            return $cancellationReasons[array_rand($cancellationReasons)];
        } elseif ($status === SupplierDeliveryStatus::PARTIALLY_DELIVERED()) {
            return "Reçu $delivered sur $total commandés. Reste à livrer: ".($total - $delivered);
        } elseif ($status === SupplierDeliveryStatus::COMPLETED()) {
            $completionComments = [
                'Commande reçue complète',
                'Livraison vérifiée et conforme',
                'Stock mis à jour',
                null,
            ];

            return $completionComments[array_rand($completionComments)];
        }

        return null;
    }

    /**
     * Create bottle records for delivered items
     */
    private function createBottleRecords($supply, $bottleType, $quantity): void
    {
        // Cette méthode simulerait la création d'enregistrements de bouteilles
        // Pour un approvisionnement réel, on créerait de vraies bouteilles dans la table bottles
        $this->command->info("Simulating creation of $quantity bottles of type {$bottleType->name} for center ID {$supply->distribution_center_id}");

        // Dans un vrai système, on ferait quelque chose comme:
        // Bottle::factory()->count($quantity)->create([
        //     'bottle_type_id' => $bottleType->id,
        //     'distribution_center_id' => $supply->distribution_center_id,
        //     'status' => BottleStatus::IN_STOCK(),
        // ]);
    }

    /**
     * Update supply totals based on its items
     */
    private function updateSupplyTotals($supply): void
    {
        // Calculer le total des articles
        $items = DB::table('supply_items')
            ->where('supply_id', $supply->id)
            ->select(DB::raw('SUM(quantity * price_unit) as total_ordered, SUM(delivered_quantity * price_unit) as total_delivered'))
            ->first();

        $supply->update([
            'total_ordered' => $items->total_ordered ?? 0,
            'total_delivered' => $items->total_delivered ?? 0,
        ]);
    }
}
