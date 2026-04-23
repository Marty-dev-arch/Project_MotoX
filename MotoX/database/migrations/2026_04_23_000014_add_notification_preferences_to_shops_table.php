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
        Schema::table('shops', function (Blueprint $table): void {
            $table->boolean('notify_low_stock_alerts')->default(true)->after('auto_assign_job_orders');
            $table->boolean('notify_job_order_updates')->default(true)->after('notify_low_stock_alerts');
            $table->boolean('notify_billing_updates')->default(true)->after('notify_job_order_updates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn([
                'notify_low_stock_alerts',
                'notify_job_order_updates',
                'notify_billing_updates',
            ]);
        });
    }
};
