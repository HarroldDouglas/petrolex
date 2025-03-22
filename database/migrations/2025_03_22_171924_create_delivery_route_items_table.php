<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_route_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_route_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_id')->constrained()->onDelete('cascade');
            $table->integer('sequence_number');
            $table->decimal('distance_from_previous', 10, 2)->nullable(); // en km
            $table->integer('estimated_time')->nullable(); // en minutes
            $table->timestamps();

            $table->unique(['delivery_route_id', 'delivery_id']);
            $table->unique(['delivery_route_id', 'sequence_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_route_items');
    }
};
