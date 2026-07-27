<?php

namespace Database\Seeders\Production;

use App\Models\DistributionCenter;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DistributionCenterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating default distribution center (Logbessou)...');

        /*
         * Default distribution center = Logbessou (the live production center).
         * Data mirrors the current prod row so `migrate:fresh --seed` yields a
         * functional setup: the mobile app resolves the city (Douala) and its base
         * prices. Stock is intentionally left at 0 — provisioning is the client's job.
         */
        $dc = DistributionCenter::updateOrCreate(
            ['name' => 'Logbessou'],
            [
                'address' => 'Pk-14',
                'neighborhood_id' => 26, // Logbessou, Douala V, Douala (city_id=2)
                'phone' => '679523777',
                'email' => 'lazare.tchamabe@petrolex.net',
                'latitude' => 4.0441020,
                'longitude' => 9.6819680,
                'is_active' => true,
                'storage_capacity' => 1000,
                'description' => 'Centre de distribution Logbessou - Douala',
            ]
        );

        $this->command->info("Distribution center ID={$dc->id} created.");

        // Link all product categories with stock=0 (real stock managed via admin panel)
        $cols = Schema::getColumnListing('product_category_distribution_center');
        $hasStockFilled = in_array('stock_filled', $cols);

        foreach (ProductCategory::all() as $cat) {
            $data = ['stock' => 0];
            if ($hasStockFilled) {
                $data['stock_filled'] = 0;
                $data['stock_empty'] = 0;
            }
            DB::table('product_category_distribution_center')->updateOrInsert(
                ['product_category_id' => $cat->id, 'distribution_center_id' => $dc->id],
                $data
            );
        }

        $this->command->info('All product categories linked (stock=0).');
    }
}
