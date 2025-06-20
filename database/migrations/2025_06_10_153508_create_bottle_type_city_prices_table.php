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
        //TODO: rename this by product_city_prices, bottle_type_id will become product_category_id
        Schema::create('bottle_type_city_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bottle_type_id')->constrained('bottle_types')->onDelete('cascade');
            $table->string('city');
            $table->decimal('content_price', 10, 2);
            $table->decimal('content_with_bottle_price', 10, 2);
            $table->timestamps();

            $table->index('city');
            $table->index(['bottle_type_id', 'city']);

            $table->unique(
                ['bottle_type_id', 'city'],
                'bottle_type_city_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_type_city_prices');
    }
};
