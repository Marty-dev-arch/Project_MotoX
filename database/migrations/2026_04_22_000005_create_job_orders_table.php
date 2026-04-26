<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 30);
            $table->string('vehicle', 140);
            $table->string('concern', 255);
            $table->string('status', 20)->default('pending');
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->date('scheduled_for')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'order_number']);
            $table->index(['shop_id', 'status']);
            $table->index(['shop_id', 'scheduled_for']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};

