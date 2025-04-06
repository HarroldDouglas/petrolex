<?php

use App\Enums\DeliveryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('deliverer_id')->nullable()->constrained('users');
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->foreignId('delivery_route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->enum('status', DeliveryStatus::values())->default(DeliveryStatus::PLANNED()->value);
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('proof_of_delivery')->nullable();
            $table->decimal('distance', 10, 2)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
