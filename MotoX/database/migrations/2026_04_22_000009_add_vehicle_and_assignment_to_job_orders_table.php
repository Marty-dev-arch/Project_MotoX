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
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->after('customer_id')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['shop_id', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->dropIndex(['shop_id', 'assigned_to']);
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('vehicle_id');
        });
    }
};

