<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) return;

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('username');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }
            if (!Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 15, 2)->default(0)->after('avatar');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin'])->default('user')->after('wallet_balance');
            }
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
        });

        // Backfill username for existing users.
        $users = DB::table('users')->select('id', 'name', 'email', 'username')->get();
        foreach ($users as $u) {
            if (!empty($u->username)) continue;
            $base = strtolower(trim((string)($u->name ?: $u->email)));
            $base = preg_replace('/[^a-z0-9]+/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base);
            if (empty($base)) $base = 'user' . $u->id;
            $candidate = $base;
            $i = 1;
            while (DB::table('users')->where('username', $candidate)->where('id', '!=', $u->id)->exists()) {
                $candidate = $base . $i++;
            }
            DB::table('users')->where('id', $u->id)->update(['username' => $candidate]);
        }

        // Ensure unique username index.
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        } catch (\Throwable $e) {
            // ignore if index already exists
        }
    }

    public function down(): void
    {
        // Keep fields for backward compatibility.
    }
};
