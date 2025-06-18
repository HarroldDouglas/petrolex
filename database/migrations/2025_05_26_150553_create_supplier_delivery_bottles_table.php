<?php

use App\Enums\SupplierDeliveryBottleMovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//TODO: rename this to supplier_delivery_bottle_tracking
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_delivery_bottles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_delivery_product_type_id')
                 ->constrained('supplier_delivery_product_types', 'id', 'prod_type_fk')
                 ->onDelete('cascade');
            $table->foreignId('bottle_id')->constrained()->onDelete('restrict');
            $table->string('movement_type')->default(SupplierDeliveryBottleMovementType::INCOMING()->value);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(
                ['supplier_delivery_product_type_id', 'bottle_id'], 
                'sdpt_bottle_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_delivery_bottles');
    }
};
