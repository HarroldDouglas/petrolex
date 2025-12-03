<?php

use App\Models\AccessoryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer les médias associés aux anciens accessory types
        $accessoryTypeIds = AccessoryType::pluck('id')->toArray();
        
        if (!empty($accessoryTypeIds)) {
            Media::where('model_type', AccessoryType::class)
                ->whereIn('model_id', $accessoryTypeIds)
                ->delete();
        }

        // Supprimer tous les anciens accessory types
        AccessoryType::query()->delete();

        // Le seeder AccessoryTypeSeeder va recréer les nouveaux accessoires
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration ne peut pas être annulée car les anciennes données sont perdues
    }
};
