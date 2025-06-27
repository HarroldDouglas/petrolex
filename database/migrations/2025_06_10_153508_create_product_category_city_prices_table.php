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
        Schema::create('product_category_city_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->string('city');
            $table->decimal('content_price', 10, 2);
            $table->decimal('content_with_bottle_price', 10, 2);
            $table->timestamps();

            $table->index('city');
            $table->index(['product_category_id', 'city']);

            $table->unique(
                ['product_category_id', 'city'],
                'product_category_city_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_city_prices');
    }
};
