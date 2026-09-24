<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One receipt per confirmed payment (spec §33) — a partial payment gets
     * its own receipt for that transaction, not just the final full payment.
     */
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('receipt_number');
            $table->unsignedBigInteger('amount');
            $table->string('method');
            $table->string('transaction_reference')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->unique(['business_id', 'receipt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
