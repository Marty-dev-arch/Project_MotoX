<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Purpose: Backfills shop IDs for existing users.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE users SET shop_id = (SELECT shops.id FROM shops WHERE shops.user_id = users.id LIMIT 1) WHERE shop_id IS NULL');
    }

    public function down(): void
    {
    }
};

