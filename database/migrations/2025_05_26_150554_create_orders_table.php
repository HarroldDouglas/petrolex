<?php

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
            $table->foreignId('delivery_person_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('distribution_center_id')->constrained()->onDelete('restrict');
            
            $table->string('order_number', 255)->unique();
            $table->enum('delivery_type', ['normal', 'fast'])->default('normal');
            $table->enum('status', ['confirmed', 'ready', 'in_transit', 'delivered', 'cancelled'])->default('confirmed');
            $table->enum('payment_status', ['not_paid', 'paid', 'refunded'])->default('paid');
            $table->enum('payment_method', ['card', 'orange_money', 'mobile_money']);
            
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes for common queries
            $table->index(['customer_id', 'status']);
            $table->index(['delivery_person_id', 'status']);
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
