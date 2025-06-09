<?php

use App\Enums\SupplierDeliveryStatus;
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
        Schema::create('supplier_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_center_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->string('delivery_number', 255)->unique();
            $table->string('supplier_name', 255);
            $table->text('description')->nullable();
            $table->date('delivery_date');
            $table->enum('status', SupplierDeliveryStatus::values())->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_deliveries');
    }
};
