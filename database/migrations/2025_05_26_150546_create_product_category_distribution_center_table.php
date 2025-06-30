<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_category_distribution_center', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')
                ->constrained()
                ->onDelete('cascade')
                ->name('fk_pc_dc_product_category');
            $table->foreignId('distribution_center_id')
                ->constrained()
                ->onDelete('cascade')
                ->name('fk_pc_dc_distribution_center');
            $table->integer('stock')->default(0); // For accessory and others (not bottle)
            $table->integer('stock_empty')->default(0); // For empty bottles
            $table->integer('stock_filled')->default(0); // For filled bottles
            $table->timestamps();

            $table->unique(['product_category_id', 'distribution_center_id'], 'pc_dc_product_category_distribution_center');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_category_distribution_center');
    }
};
