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
        Schema::create('delivery_person_distribution_center', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_person_id');
            $table->unsignedBigInteger('distribution_center_id');
            $table->timestamps();
            
            $table->foreign('delivery_person_id', 'dp_dc_delivery_person_fk')
                ->references('id')
                ->on('delivery_persons')
                ->onDelete('cascade');
                
            $table->foreign('distribution_center_id', 'dp_dc_center_fk')
                ->references('id')
                ->on('distribution_centers')
                ->onDelete('cascade');
            
            $table->unique(['delivery_person_id', 'distribution_center_id'], 'dp_dc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_person_distribution_center');
    }
};
