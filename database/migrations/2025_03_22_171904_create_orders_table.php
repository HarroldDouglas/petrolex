<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('address_id')->nullable()->constrained('client_addresses');
            $table->enum('status', OrderStatus::toValues())->default(OrderStatus::PENDING()->value);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_status', PaymentStatus::toValues())->default(PaymentStatus::PENDING()->value);
            $table->text('notes')->nullable();
            $table->timestamp('requested_delivery_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('source')->default('web');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
