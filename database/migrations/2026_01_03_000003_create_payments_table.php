<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A minimal payments record for Phase 4's Fast Sale (spec §27). The full
     * Payments module (configurable payment_methods, pending/failed/refunded
     * workflows, standalone payment listing/recording UI) is Phase 5 — this
     * table already has the shape that phase extends, not a throwaway.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->ulid('uuid')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('method', ['cash', 'mpesa', 'airtel_money', 'mixx_by_yas', 'halopesa', 'bank', 'other']);
            $table->string('reference')->nullable();
            $table->enum('status', ['confirmed', 'refunded'])->default('confirmed');
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
