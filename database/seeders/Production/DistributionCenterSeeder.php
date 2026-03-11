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
        $this->command->info('Creating default distribution center...');

        $dc = DistributionCenter::updateOrCreate(
            ['name' => 'Petrolex Cameroun - Siège Douala'],
            [
                'address'          => '399, Rue 1225 Dominique Savio, Bonanjo',
                'neighborhood_id'  => 17, // Bonanjo, Douala
                'phone'            => '+237699000000',
                'email'            => 'contact@isogaz.net',
                'latitude'         => 4.0280392,
                'longitude'        => 9.6960282,
                'is_active'        => true,
                'storage_capacity' => 5000,
                'description'      => 'Centre de distribution principal - Siège social Petrolex Cameroun',
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
