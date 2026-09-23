<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency')->default('TZS');
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('locale')->default('sw');
            $table->string('invoice_prefix')->default('INV');
            $table->string('receipt_prefix')->default('RCT');
            $table->string('order_prefix')->default('ORD');
            $table->unsignedInteger('next_invoice_number')->default(1);
            $table->unsignedInteger('next_receipt_number')->default(1);
            $table->unsignedInteger('next_order_number')->default(1);
            $table->unsignedInteger('low_stock_default_threshold')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
