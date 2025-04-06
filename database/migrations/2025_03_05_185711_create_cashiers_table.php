<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('cashiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('employee_id')->nullable()->unique();
            $table->decimal('cash_balance', 10, 2)->default(0);
            $table->decimal('daily_transaction_limit', 10, 2)->nullable();
            $table->timestamp('shift_start')->nullable();
            $table->timestamp('shift_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashiers');
    }
};
