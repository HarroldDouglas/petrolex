<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds real bottle specifications as provided by the client.
     * Previous data was placeholder/invented values.
     */
    public function up(): void
    {
        // Step 1: Add new columns for real specifications
        Schema::table('bottle_types', function (Blueprint $table) {
            $table->string('content_type')->nullable()->after('description_en'); // BUTANE, PROPANE, etc.
            $table->string('test_pressure')->nullable()->after('weight'); // e.g., "30 BAR"
        });

        // Step 2: Update the 9kg bottle with REAL specifications from client
        // These are the official specifications, not invented values
        DB::table('bottle_types')
            ->where('id', 1)
            ->update([
                'name' => 'Bouteille de 9Kg',
                'name_en' => '9Kg Gas Bottle',
                'description' => 'Bouteille de gaz butane de 9kg',
                'description_en' => '9kg butane gas bottle',
                'content_type' => 'BUTANE',
                'capacity' => '18.5',          // Volume: 18.5 L (was incorrectly 9)
                'height' => '461',             // Hauteur: 461 mm (was incorrectly 45 cm)
                'weight' => '9',               // Masse du contenant: 9 kg (was incorrectly 16)
                'test_pressure' => '30 BAR',   // Test Pressure: 30 BAR (was missing)
                'content_price' => 4680.00,    // Prix de la recharge: 4680 FCFA
                'full_price' => 21780.00,      // Prix consigne + recharge: 21780 FCFA
                'specifications' => json_encode([
                    ['name' => 'Contenant', 'value' => 'BUTANE'],
                    ['name' => 'Masse du contenant', 'value' => '9 kg'],
                    ['name' => 'Volume', 'value' => '18.5 L'],
                    ['name' => 'Test Pressure', 'value' => '30 BAR'],
                    ['name' => 'Hauteur', 'value' => '461 mm'],
                ]),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old (invented) values
        DB::table('bottle_types')
            ->where('id', 1)
            ->update([
                'name' => 'Bouteille de 9Kg',
                'name_en' => '9Kg Gas Bottle',
                'description' => 'Bouteille moyenne de 9kg pour usage régulier',
                'description_en' => 'Medium 9kg bottle for regular use',
                'capacity' => '9',
                'height' => '45.00',
                'weight' => '16.00',
                'content_price' => 6000.00,
                'full_price' => 6500.00,
                'specifications' => json_encode([
                    ['name' => 'Capacité', 'value' => '9 L'],
                    ['name' => 'Hauteur', 'value' => '45.00 cm'],
                    ['name' => 'Poids', 'value' => '16.00 kg'],
                    ['name' => 'Rayon', 'value' => '17.50 cm'],
                ]),
            ]);

        Schema::table('bottle_types', function (Blueprint $table) {
            $table->dropColumn(['content_type', 'test_pressure']);
        });
    }
};
