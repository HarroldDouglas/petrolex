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
        Schema::create('municipality_neighborhood_pivot', function (Blueprint $table) {
            $table->foreignId('municipality_id')->constrained()->onDelete('cascade');
            $table->foreignId('neighborhood_id')->constrained()->onDelete('cascade');
            $table->primary(['municipality_id', 'neighborhood_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipality_neighborhood_pivot');
    }
};
