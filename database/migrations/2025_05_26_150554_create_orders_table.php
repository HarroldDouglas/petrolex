<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('delivery_address_id')->constrained('customer_delivery_addresses')->onDelete('restrict');
            $table->unsignedBigInteger('delivery_person_id')->nullable();
            $table->text('delivery_person_update_reason')->nullable();
            $table->foreignId('distribution_center_id')->constrained()->onDelete('restrict');
            
            $table->string('order_number', 255)->unique();
            $table->enum('delivery_type', ['normal', 'fast'])->default('normal');
            $table->enum('status', OrderStatus::values())->default(OrderStatus::PAID());
            
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('delivery_date')->nullable();
            $table->text('comments')->nullable()->comment('Customer comments about the order');
            $table->text('center_comments')->nullable()->comment('Distribution center comments about the order');
            $table->decimal('rating', 2, 1)->nullable()->comment('Customer rating from 1 to 5');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('processing_at')->nullable(); 
            $table->timestamp('delivered_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            
            $table->unsignedBigInteger('cancelled_by')->nullable()->comment('User ID who cancelled the order');
            $table->text('cancelled_reason')->nullable()->comment('Reason for order cancellation');
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for common queries
            $table->index(['customer_id', 'status']);
            $table->index('delivery_person_id');
            $table->index(['distribution_center_id', 'status']);
            $table->index('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
