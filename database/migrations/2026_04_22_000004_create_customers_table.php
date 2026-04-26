<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 140);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'name']);
            $table->unique(['shop_id', 'email']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

