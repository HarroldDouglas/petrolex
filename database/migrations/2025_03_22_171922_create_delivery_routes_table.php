<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->constrained('users');
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->string('name')->nullable();
            $table->date('route_date');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->integer('estimated_duration')->nullable(); // en minutes
            $table->integer('actual_duration')->nullable(); // en minutes
            $table->decimal('estimated_distance', 10, 2)->nullable(); // en km
            $table->decimal('actual_distance', 10, 2)->nullable(); // en km
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_routes');
    }
};
