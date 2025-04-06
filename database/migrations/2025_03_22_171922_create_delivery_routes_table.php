<?php

use App\Enums\DeliveryStatus;
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
            $table->enum('status', DeliveryStatus::toValues())->default(DeliveryStatus::PLANNED()->value);
            $table->integer('estimated_duration')->nullable();
            $table->integer('actual_duration')->nullable();
            $table->decimal('estimated_distance', 10, 2)->nullable();
            $table->decimal('actual_distance', 10, 2)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('route_notes')->nullable();
            $table->boolean('is_optimized')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_routes');
    }
};
