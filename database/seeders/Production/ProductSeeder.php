<?php

// database/seeders/Production/ProductSeeder.php

namespace Database\Seeders\Production;

use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public static $accessoryProductMap = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating products...');

        $this->createBottleProducts();
        $this->createAccessoryProducts();

        $this->command->info('Products created successfully!');
    }

    /**
     * Create products for bottle types
     */
    private function createBottleProducts(): void
    {
        $bottleTypes = BottleType::all();

        if ($bottleTypes->count() === 0) {
            $this->command->warn('No bottle types found. Run BottleTypeSeeder first.');

            return;
        }

        foreach ($bottleTypes as $bottleType) {
            Product::firstOrCreate(
                [
                    'id' => $bottleType->id,
                    'product_type' => ProductType::BOTTLE(),
                ]
            );
        }

        $this->command->info("{$bottleTypes->count()} bottle products created.");
    }

    /**
     * Create products for accessory types
     */
    private function createAccessoryProducts(): void
    {
        $accessoryTypes = AccessoryType::all();

        if ($accessoryTypes->count() === 0) {
            $this->command->warn('No accessory types found. Run AccessoryTypeSeeder first.');

            return;
        }
        self::$accessoryProductMap = [];

        foreach ($accessoryTypes as $accessoryType) {
            $product = Product::create([
                'product_type' => ProductType::ACCESSORY(),
            ]);

            self::$accessoryProductMap[$accessoryType->id] = $product->id;
        }

        $this->command->info("{$accessoryTypes->count()} accessory products created.");
    }

    /**
     * Get the product ID associated with an accessory type
     *
     * @param  int  $accessoryTypeId
     * @return int|null
     */
    public static function getProductIdForAccessoryType($accessoryTypeId)
    {
        return self::$accessoryProductMap[$accessoryTypeId] ?? null;
    }
}
