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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('wallet_amount_used', 10, 2)->default(0)->after('total_amount');
            $table->unsignedBigInteger('wallet_transaction_id')->nullable()->after('wallet_amount_used');

            $table->foreign('wallet_transaction_id')
                ->references('id')
                ->on('wallet_transactions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['wallet_transaction_id']);
            $table->dropColumn(['wallet_amount_used', 'wallet_transaction_id']);
        });
    }
};
