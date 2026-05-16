<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Purpose: Adds inventory unit fields to parts.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $table->string('stock_mode', 20)->default('piece')->after('category');
            $table->string('unit_label', 20)->default('pcs')->after('stock_mode');
            $table->decimal('pieces_per_box', 12, 3)->nullable()->after('unit_label');
            $table->boolean('allow_fractional_quantity')->default(false)->after('pieces_per_box');
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->decimal('quantity', 14, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->integer('quantity')->change();
        });

        Schema::table('parts', function (Blueprint $table): void {
            $table->dropColumn([
                'stock_mode',
                'unit_label',
                'pieces_per_box',
                'allow_fractional_quantity',
            ]);
        });
    }
};

