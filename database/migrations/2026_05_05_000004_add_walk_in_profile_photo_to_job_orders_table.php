<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Purpose: Adds walk-in profile photo support to job orders.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->string('walk_in_profile_photo_path')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table): void {
            $table->dropColumn('walk_in_profile_photo_path');
        });
    }
};
