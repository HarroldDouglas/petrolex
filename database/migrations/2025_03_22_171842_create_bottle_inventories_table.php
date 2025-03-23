<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bottle_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bottle_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->integer('quantity_available');
            $table->integer('quantity_reserved')->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->integer('max_stock_level')->nullable();
            $table->timestamps();

            $table->unique(['bottle_id', 'warehouse_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bottle_inventories');
    }
};
