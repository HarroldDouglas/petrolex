<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_logs', function (Blueprint $table) {
            $table->id();
            $table->string('app_type', 30);
            $table->string('platform', 10)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('device_model', 100)->nullable();
            $table->string('os_version', 50)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level', 10)->default('error');
            $table->text('message');
            $table->text('stack_trace')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['app_type', 'created_at']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_logs');
    }
};
