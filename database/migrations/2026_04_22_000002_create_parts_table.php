<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->string('category');
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'sku']);
            $table->index(['shop_id', 'category']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};

