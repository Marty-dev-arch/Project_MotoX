<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Purpose: Adds username support to users.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 60)->nullable()->after('name');
        });

        $used = [];
        DB::table('users')
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$used): void {
                $source = Str::before((string) $user->email, '@') ?: (string) $user->name;
                $base = Str::of($source)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9_.-]+/', '-')
                    ->trim('-_.')
                    ->limit(48, '')
                    ->toString();

                $base = $base !== '' ? $base : 'user'.$user->id;
                $username = $base;
                $suffix = 2;

                while (isset($used[$username]) || DB::table('users')->where('username', $username)->exists()) {
                    $username = Str::limit($base, 52, '').'-'.$suffix;
                    $suffix++;
                }

                $used[$username] = true;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
