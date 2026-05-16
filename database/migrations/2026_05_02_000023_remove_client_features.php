<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Purpose: Removes unused client feature fields.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('client_order_items');
        Schema::dropIfExists('client_orders');
        Schema::dropIfExists('messages');

        DB::table('users')
            ->where('role', 'client')
            ->delete();
    }

    public function down(): void
    {
        Schema::create('client_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number', 40);
            $table->string('status', 20)->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'order_number']);
            $table->index(['shop_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });

        Schema::create('client_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_order_id')->constrained('client_orders')->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('part_name');
            $table->string('unit_label', 30);
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['shop_id', 'sent_at']);
            $table->index(['sender_id', 'recipient_id', 'sent_at']);
        });
    }
};
