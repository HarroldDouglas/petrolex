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
        Schema::create('order_bottle_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('bottle_id')->constrained()->onDelete('cascade');
            $table->foreignId('empty_bottle_id')->nullable()->constrained('bottles')->onDelete('set null');
            $table->timestamps();

            $table->unique(['order_item_id', 'bottle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_bottle_scans');
    }
};
