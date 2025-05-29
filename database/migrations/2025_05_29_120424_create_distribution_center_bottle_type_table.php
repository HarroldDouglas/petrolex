<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('bottle_type_distribution_center', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_center_id')->constrained()->onDelete('cascade');
            $table->foreignId('bottle_type_id')->constrained()->onDelete('cascade');
            $table->integer('stock_empty')->default(0);
            $table->integer('stock_filled')->default(0);
            $table->timestamps();

            $table->unique(
                ['distribution_center_id', 'bottle_type_id'],
                'dc_bottle_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_center_bottle_type');
    }
};
