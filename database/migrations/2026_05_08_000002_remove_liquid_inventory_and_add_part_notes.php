<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('parts')
            ->where('stock_mode', 'liquid')
            ->update([
                'stock_mode' => 'box_piece',
                'unit_label' => 'box',
                'pieces_per_box' => 1,
                'unit_price_basis' => 'per_box',
            ]);

        Schema::table('parts', function (Blueprint $table): void {
            if (! Schema::hasColumn('parts', 'notes')) {
                $table->text('notes')->nullable()->after('category');
            }

            if (Schema::hasColumn('parts', 'liters_per_bottle')) {
                $table->dropColumn('liters_per_bottle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            if (! Schema::hasColumn('parts', 'liters_per_bottle')) {
                $table->decimal('liters_per_bottle', 12, 3)->nullable()->after('pieces_per_box');
            }

            if (Schema::hasColumn('parts', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
