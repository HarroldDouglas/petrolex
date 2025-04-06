<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('deliverers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_registration')->nullable();
            $table->decimal('max_load_capacity', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('is_available')->default(true);
            $table->string('license_number')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->text('delivery_notes_template')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliverers');
    }
};
