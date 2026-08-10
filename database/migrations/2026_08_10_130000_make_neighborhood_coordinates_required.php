<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Null neighborhood coordinates crash the strongly-typed mobile apps
 * (2026-08 incident: 12 admin-created quartiers without lat/lng).
 * Enforce at the DB level so no code path can reintroduce them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable(false)->change();
            $table->decimal('longitude', 11, 8)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }
};
