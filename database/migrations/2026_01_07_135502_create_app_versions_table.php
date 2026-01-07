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
            $table->string('app_type'); // customer_app, delivery_app
            $table->string('platform'); // android, ios
            $table->integer('version_code');
            $table->string('version_name');
            $table->boolean('update_required')->default(false);
            $table->text('release_notes')->nullable();
            $table->timestamps();

            // Unique constraint: one version per app_type + platform
            $table->unique(['app_type', 'platform']);
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
