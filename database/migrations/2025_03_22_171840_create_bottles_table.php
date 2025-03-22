<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('bottles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->decimal('capacity', 8, 2); // in liters
            $table->decimal('weight', 8, 2)->nullable(); // in kg
            $table->decimal('height', 8, 2)->nullable(); // in cm
            $table->decimal('diameter', 8, 2)->nullable(); // in cm
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bottles');
    }
};
