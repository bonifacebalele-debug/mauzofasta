<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('tax_enabled')->default(false);
            $table->string('tax_number')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
