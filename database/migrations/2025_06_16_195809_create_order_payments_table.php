<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', PaymentStatus::values())->default(PaymentStatus::PENDING());
            $table->enum('payment_method', PaymentMethod::values())->nullable();
            
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            
            $table->timestamp('payment_date')->nullable();
            $table->text('payment_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for common queries
            $table->index('payment_reference');
            $table->index(['order_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
