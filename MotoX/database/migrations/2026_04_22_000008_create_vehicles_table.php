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
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_name', 140);
            $table->string('plate_number', 40)->nullable();
            $table->string('vin', 80)->nullable();
            $table->string('engine_number', 80)->nullable();
            $table->integer('year_model')->nullable();
            $table->string('color', 60)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['shop_id', 'customer_id']);
            $table->index(['shop_id', 'is_active']);
            $table->unique(['shop_id', 'plate_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

