<?php

namespace App\Console\Commands;

use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
use Illuminate\Console\Command;

class VerifyProductCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:verify-categories {--fix : Créer les catégories manquantes automatiquement}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie la cohérence entre les types de produits et leurs catégories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   VÉRIFICATION DES CATÉGORIES DE PRODUITS');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Statistiques
        $this->displayStatistics();
        $this->newLine();

        // Vérification bouteilles
        $bottleMissing = $this->verifyBottleCategories();
        $this->newLine();

        // Vérification accessoires
        $accessoryMissing = $this->verifyAccessoryCategories();
        $this->newLine();

        // Résumé
        $this->displaySummary($bottleMissing, $accessoryMissing);

        return Command::SUCCESS;
    }

    private function displayStatistics(): void
    {
        $totalCategories = ProductCategory::count();
        $bottleCategories = ProductCategory::bottles()->count();
        $accessoryCategories = ProductCategory::accessories()->count();
        $activeCategories = ProductCategory::count();

        $bottleTypes = BottleType::count();
        $accessoryTypes = AccessoryType::count();

        $this->info('📊 STATISTIQUES GÉNÉRALES:');
        $this->table(
            ['Élément', 'Nombre'],
            [
                ['Total catégories', $totalCategories],
                ['Catégories bouteilles', $bottleCategories],
                ['Catégories accessoires', $accessoryCategories],
                ['Types de bouteilles', $bottleTypes],
                ['Types d\'accessoires', $accessoryTypes],
            ]
        );
    }

    private function verifyBottleCategories(): int
    {
        $this->info('🍾 VÉRIFICATION DES BOUTEILLES:');

        $bottleTypes = BottleType::all();
        $missingCount = 0;

        $rows = [];
        foreach ($bottleTypes as $bottleType) {
            $category = ProductCategory::bottles()
                ->where('product_type_id', $bottleType->id)
                ->first();

            $status = $category ? '✅' : '❌ MANQUANT';
            $categoryId = $category ? $category->id : '-';

            if (!$category) {
                $missingCount++;

                if ($this->option('fix')) {
                    $newCategory = ProductCategory::create([
                        'product_type' => \App\Enums\ProductType::BOTTLE(),
                        'product_type_id' => $bottleType->id,
                    ]);
                    $status = '✅ CRÉÉ';
                    $categoryId = $newCategory->id;
                }
            }

            $rows[] = [
                $bottleType->id,
                $bottleType->name,
                $categoryId,
                $status,
            ];
        }

        $this->table(
            ['Type ID', 'Nom', 'Catégorie ID', 'Statut'],
            $rows
        );

        return $missingCount;
    }

    private function verifyAccessoryCategories(): int
    {
        $this->info('🔧 VÉRIFICATION DES ACCESSOIRES:');

        $accessoryTypes = AccessoryType::all();
        $missingCount = 0;

        $rows = [];
        foreach ($accessoryTypes as $accessoryType) {
            $category = ProductCategory::accessories()
                ->where('product_type_id', $accessoryType->id)
                ->first();

            $status = $category ? '✅' : '❌ MANQUANT';
            $categoryId = $category ? $category->id : '-';

            if (!$category) {
                $missingCount++;

                if ($this->option('fix')) {
                    $newCategory = ProductCategory::create([
                        'product_type' => \App\Enums\ProductType::ACCESSORY(),
                        'product_type_id' => $accessoryType->id,
                    ]);
                    $status = '✅ CRÉÉ';
                    $categoryId = $newCategory->id;
                }
            }

            $rows[] = [
                $accessoryType->id,
                $accessoryType->name,
                $categoryId,
                $status,
            ];
        }

        $this->table(
            ['Type ID', 'Nom', 'Catégorie ID', 'Statut'],
            $rows
        );

        return $missingCount;
    }

    private function displaySummary(int $bottleMissing, int $accessoryMissing): void
    {
        $totalMissing = $bottleMissing + $accessoryMissing;

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   RÉSUMÉ');
        $this->info('═══════════════════════════════════════════════════════');

        if ($totalMissing === 0) {
            $this->info('✅ Toutes les catégories sont cohérentes !');
        } else {
            $this->warn("⚠️  {$totalMissing} catégorie(s) manquante(s) détectée(s):");
            if ($bottleMissing > 0) {
                $this->warn("   - {$bottleMissing} catégorie(s) de bouteilles");
            }
            if ($accessoryMissing > 0) {
                $this->warn("   - {$accessoryMissing} catégorie(s) d'accessoires");
            }

            if (!$this->option('fix')) {
                $this->newLine();
                $this->info('💡 Utilisez --fix pour créer automatiquement les catégories manquantes:');
                $this->comment('   php artisan products:verify-categories --fix');
            }
        }
    }
}
