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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('app_type');
            $table->string('platform');
            $table->integer('version_code');
            $table->string('version_name');
            $table->boolean('update_required')->default(false);
            $table->text('release_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['app_type', 'platform', 'is_active']);
            $table->unique(['app_type', 'platform', 'version_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
