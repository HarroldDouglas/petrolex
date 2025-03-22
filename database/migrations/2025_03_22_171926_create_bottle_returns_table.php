<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('bottle_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bottle_delivery_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained(); // client qui retourne
            $table->foreignId('received_by')->nullable()->constrained('users'); // employé qui reçoit
            $table->foreignId('bottle_id')->constrained();
            $table->enum('condition', ['empty', 'partially_used', 'damaged'])->default('empty');
            $table->text('notes')->nullable();
            $table->timestamp('return_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bottle_returns');
    }
};
