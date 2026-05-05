<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $table->decimal('minimum_stock', 14, 3)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table): void {
            $table->unsignedInteger('minimum_stock')->default(0)->change();
        });
    }
};
