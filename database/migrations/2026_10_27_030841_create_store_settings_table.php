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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('DONS MEAT');
            $table->string('logo_path')->nullable();
            $table->text('business_description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('opening_hours')->nullable();
            $table->decimal('standard_beef_price_per_kg', 12, 2)->default(0);
            $table->unsignedInteger('payment_expiry_minutes')->default(60);
            $table->string('timezone', 50)->default('Asia/Makassar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
