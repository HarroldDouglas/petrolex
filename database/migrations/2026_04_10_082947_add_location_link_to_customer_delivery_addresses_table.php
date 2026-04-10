<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->string('location_link')->nullable()->after('longitude');
            $table->string('address')->nullable()->change();
            $table->unsignedBigInteger('neighborhood_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_delivery_addresses', function (Blueprint $table) {
            $table->dropColumn('location_link');
            $table->string('address')->nullable(false)->change();
            $table->unsignedBigInteger('neighborhood_id')->nullable(false)->change();
        });
    }
};
