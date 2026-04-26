<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 30);
            $table->decimal('labor_amount', 12, 2)->default(0);
            $table->decimal('parts_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 60)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'invoice_number']);
            $table->index(['shop_id', 'status']);
            $table->index(['shop_id', 'issued_at']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

