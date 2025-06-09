<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('initiated_by')->constrained('users')->comment('Admin qui a initié le remboursement');

            $table->enum('refund_method', PaymentMethod::values());
            $table->string('refund_identifier', 100)->comment('Numéro de téléphone, email, compte bancaire, etc.');
            $table->enum('status', PaymentStatus::values())->default(PaymentStatus::PENDING());

            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable()->comment('Notes internes');

            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('order_id');
            $table->index('status');
            $table->index('refund_method');
            $table->index('initiated_by');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
