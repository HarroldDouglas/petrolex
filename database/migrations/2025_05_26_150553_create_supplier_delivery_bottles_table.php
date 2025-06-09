<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_delivery_bottles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_delivery_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_delivery_product_type_id')
                 ->constrained('supplier_delivery_product_types', 'id', 'prod_type_fk')
                 ->onDelete('cascade');
            $table->foreignId('bottle_id')->constrained()->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
            
            // Ensure each bottle can only be associated once with a delivery
            $table->unique(['supplier_delivery_id', 'bottle_id']);
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
