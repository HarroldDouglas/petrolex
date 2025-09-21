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
        // Check if confirmed_at column exists before renaming
        if (Schema::hasColumn('orders', 'confirmed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('confirmed_at', 'paid_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('paid_at', 'confirmed_at');
        });
    }
};