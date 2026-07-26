<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 0 hardening — bảng redirect_info hoạt động đúng với middleware CheckRedirect.
 *
 * Thay vì foreach Redirect::all() đăng ký N route mỗi request, ta query 1 lần
 * theo url_old. Cần index trên url_old để query nhanh; đồng thời ép collation
 * utf8mb4_bin để so sánh chính xác chữ hoa/thường/dấu (URL phân biệt case).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('redirect_info')) return;

        // Đổi cột url_old/url_new sang utf8mb4_bin (so sánh chính xác)
        try {
            DB::statement("ALTER TABLE redirect_info MODIFY url_old TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
            DB::statement("ALTER TABLE redirect_info MODIFY url_new TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
        } catch (\Throwable $e) {
            // bỏ qua nếu DB không hỗ trợ
        }

        // Index trên prefix 191 cho TEXT (đủ cho URL điển hình)
        try { DB::statement('CREATE INDEX redirect_url_old_idx ON redirect_info (url_old(191))'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX redirect_url_new_idx ON redirect_info (url_new(191))'); } catch (\Throwable $e) {}
    }

    public function down()
    {
        if (!Schema::hasTable('redirect_info')) return;
        try { DB::statement('DROP INDEX redirect_url_old_idx ON redirect_info'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX redirect_url_new_idx ON redirect_info'); } catch (\Throwable $e) {}
    }
};
