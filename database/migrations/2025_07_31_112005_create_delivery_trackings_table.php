<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('delivery_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status', 30)->default('pending');
            $table->decimal('driver_lat', 10, 8)->nullable();
            $table->decimal('driver_lng', 11, 8)->nullable();
            $table->decimal('current_speed', 8, 2)->nullable();
            $table->decimal('progress_percentage', 5, 2)->nullable(); // Nouveau champ pour la progression (0.00 à 100.00)
            $table->integer('estimated_duration')->nullable(); // In minutes
            $table->decimal('distance_remaining', 8, 2)->nullable(); // In kilometers
            $table->decimal('total_distance', 8, 2)->nullable();
            $table->json('route_geometry')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_trackings');
    }
};