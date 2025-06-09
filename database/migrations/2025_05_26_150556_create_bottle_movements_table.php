<?php

use App\Enums\BottleMovementType;
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
        Schema::create('bottle_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bottle_id')->constrained()->onDelete('restrict');
            $table->foreignId('supplier_delivery_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('distribution_center_id')->nullable()->constrained()->onDelete('restrict');
            $table->unsignedBigInteger('delivery_person_id')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            
            $table->enum('type', BottleMovementType::values());
            
            $table->boolean('declared_by_customer')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['bottle_id', 'movement_date']);
            $table->index('type');
            $table->index('movement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_movements');
    }
};
