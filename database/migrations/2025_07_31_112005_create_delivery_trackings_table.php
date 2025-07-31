<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->enum('status', ['pending', 'started', 'in_progress', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('driver_lat', 10, 8)->nullable();
            $table->decimal('driver_lng', 11, 8)->nullable();
            $table->decimal('destination_lat', 10, 8);
            $table->decimal('destination_lng', 11, 8);
            $table->string('destination_address');
            $table->integer('estimated_duration')->nullable(); // minutes
            $table->decimal('distance_remaining', 8, 2)->nullable(); // km
            $table->json('route_geometry')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_trackings');
    }
};
