<?php

use App\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//This table represents a product category , it is the product that can be sold, for example: bottle(bouteille de 6kg) or accessory(tuyaux de 5m)
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('product_type', ProductType::values());
            $table->unsignedBigInteger('product_type_id'); // ID for bottle_type or for accessory_type
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_type', 'product_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
