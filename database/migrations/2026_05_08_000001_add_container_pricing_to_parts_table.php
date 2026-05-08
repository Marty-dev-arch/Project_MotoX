<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $table->string('unit_price_basis', 20)->default('per_box')->after('unit_price');
            $table->decimal('liters_per_bottle', 12, 3)->nullable()->after('pieces_per_box');
        });

        DB::table('parts')
            ->where('stock_mode', 'piece')
            ->update([
                'stock_mode' => 'box_piece',
                'unit_label' => 'box',
                'pieces_per_box' => 1,
                'unit_price_basis' => 'per_piece',
            ]);

        DB::table('parts')
            ->where('stock_mode', 'box_piece')
            ->whereNull('pieces_per_box')
            ->update(['pieces_per_box' => 1]);

        DB::table('parts')
            ->where('stock_mode', 'liquid')
            ->whereNull('liters_per_bottle')
            ->update(['liters_per_bottle' => 1]);

        DB::table('parts')
            ->where('stock_mode', 'liquid')
            ->update(['unit_price_basis' => 'per_liter']);
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $table->dropColumn(['unit_price_basis', 'liters_per_bottle']);
        });
    }
};
