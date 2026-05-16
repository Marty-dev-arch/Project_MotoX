<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Purpose: Ensures required part pricing columns exist.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            if (! Schema::hasColumn('parts', 'notes')) {
                $table->text('notes')->nullable()->after('category');
            }

            if (! Schema::hasColumn('parts', 'unit_price_basis')) {
                $table->string('unit_price_basis', 20)->default('per_box');
            }

            if (! Schema::hasColumn('parts', 'unit_price_per_box')) {
                $table->decimal('unit_price_per_box', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('parts', 'unit_price_per_piece')) {
                $table->decimal('unit_price_per_piece', 12, 2)->default(0);
            }
        });

        DB::table('parts')->orderBy('id')->chunkById(100, function ($parts): void {
            foreach ($parts as $part) {
                $piecesPerBox = max(1.0, (float) ($part->pieces_per_box ?? 1));
                $unitPrice = (float) ($part->unit_price ?? 0);
                $basis = (string) ($part->unit_price_basis ?? 'per_box');

                $perBox = $basis === 'per_piece' ? $unitPrice * $piecesPerBox : $unitPrice;
                $perPiece = $basis === 'per_piece' ? $unitPrice : ($unitPrice / $piecesPerBox);

                DB::table('parts')
                    ->where('id', $part->id)
                    ->update([
                        'unit_price_basis' => in_array($basis, ['per_box', 'per_piece'], true) ? $basis : 'per_box',
                        'unit_price_per_box' => round($perBox, 2),
                        'unit_price_per_piece' => round($perPiece, 2),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $columns = [];

            foreach (['unit_price_per_piece', 'unit_price_per_box'] as $column) {
                if (Schema::hasColumn('parts', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
