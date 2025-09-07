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
        Schema::create('bottle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('name_en', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('capacity', 50);
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('radius', 8, 2)->nullable();
            $table->decimal('content_price', 10, 2);
            $table->decimal('full_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bottle_types');
    }
};
