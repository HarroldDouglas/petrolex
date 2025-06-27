<?php

namespace Database\Seeders\Development;

use App\Models\ProductCategoryDistributionCenter;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessorySeeder extends Seeder
{
    private ProductFactory $productFactory;

    public function run(): void
    {
        $this->command->info('Creating accessories for development...');

        $distCenterStocks = $this->getDistributionCenterStocks();

        if ($distCenterStocks->isEmpty()) {
            $this->command->error('No distribution centers with accessory stock found.');

            return;
        }

        $this->productFactory = new ProductFactory;
        $totalCreated = $this->createAccessories($distCenterStocks);

        $this->command->info("Development accessories created successfully! Total created: {$totalCreated}");
    }

    private function getDistributionCenterStocks()
    {
        return ProductCategoryDistributionCenter::with([
            'productCategory',
            'distributionCenter',
        ])
            ->whereHas('productCategory', function ($query) {
                $query->accessories();
            })
            ->where('stock', '>', 0)
            ->get();
    }

    private function createAccessories($distCenterStocks): int
    {
        $totalCreated = 0;
        $productsToCreate = [];

        foreach ($distCenterStocks as $stock) {
            $productCategory = $stock->productCategory;
            $accessoryType = $productCategory->productTypeInstance;
            $center = $stock->distributionCenter;

            if (! $accessoryType) {
                $this->command->warn("Skipping stock ID {$stock->id} - no accessory type found");
                continue;
            }

            $stockAmount = (int) $stock->stock;
            $this->command->info("Creating {$stockAmount} {$accessoryType->name} accessories for {$center->name}");

            for ($i = 0; $i < $stockAmount; $i++) {
                $productsToCreate[] = [
                    'productCategoryId' => $productCategory->id,
                    'accessoryTypeId' => $accessoryType->id,
                    'distributionCenterId' => $center->id,
                    'accessoryAttributes' => [
                        'is_sold' => false,
                    ],
                ];
            }

            if (count($productsToCreate) >= 100) {
                $totalCreated += $this->createProductsBatch($productsToCreate);
                $productsToCreate = [];
            }
        }

        if (! empty($productsToCreate)) {
            $totalCreated += $this->createProductsBatch($productsToCreate);
        }

        return $totalCreated;
    }

    private function createProductsBatch(array $products): int
    {
        $created = 0;

        DB::transaction(function () use ($products, &$created) {
            foreach ($products as $productData) {
                try {
                    $this->productFactory->accessory(
                        productCategoryId: $productData['productCategoryId'],
                        accessoryTypeId: $productData['accessoryTypeId'],
                        distributionCenterId: $productData['distributionCenterId'],
                        accessoryAttributes: $productData['accessoryAttributes']
                    )->create();

                    $created++;
                } catch (\Exception $e) {
                    $this->command->warn('Failed to create accessory: '.$e->getMessage());
                }
            }
        });

        return $created;
    }
}
