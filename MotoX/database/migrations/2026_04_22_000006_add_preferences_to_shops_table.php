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
            $table->decimal('default_labor_rate', 10, 2)->default(0)->after('contact_number');
            $table->string('currency_code', 3)->default('PHP')->after('default_labor_rate');
            $table->boolean('auto_assign_job_orders')->default(true)->after('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn([
                'default_labor_rate',
                'currency_code',
                'auto_assign_job_orders',
            ]);
        });
    }
};

