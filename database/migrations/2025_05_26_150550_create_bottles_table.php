<?php

use App\Enums\BottleStatus;
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
        Schema::create('bottles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('distribution_center_id')->constrained()->onDelete('restrict');
            $table->foreignId('marked_lost_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('barcode', 255)->unique();
            $table->boolean('is_filled')->default(true);
            $table->enum('status', BottleStatus::values())->default('in_stock');
            $table->timestamps();
            $table->timestamp('marked_lost_at')->nullable();
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['distribution_center_id', 'status']);
            $table->index('status');
            $table->index('marked_lost_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottles');
    }
};
