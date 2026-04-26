<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('moved_at')->useCurrent();
            $table->timestamps();

            $table->index(['part_id', 'moved_at']);
            $table->index(['type', 'moved_at']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

