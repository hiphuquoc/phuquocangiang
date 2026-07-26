<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 0 hardening — bảng seo: thêm index cho slug & slug_full để routing nhanh.
 *
 * RoutingController + Url::checkUrlExists query bằng slug_full -> bắt buộc có
 * index. Url::buildBreadcrumb query whereIn('slug', ...) -> cần index slug.
 *
 * Phase 1 sẽ thêm UNIQUE(language_id, slug_full) khi tách bảng seo_translations.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('seo')) return;

        try { DB::statement('CREATE INDEX seo_slug_idx ON seo (slug(191))'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX seo_slug_full_idx ON seo (slug_full(191))'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX seo_type_idx ON seo (type)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX seo_parent_idx ON seo (parent)'); } catch (\Throwable $e) {}
    }

    public function down()
    {
        if (!Schema::hasTable('seo')) return;
        try { DB::statement('DROP INDEX seo_slug_idx ON seo'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX seo_slug_full_idx ON seo'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX seo_type_idx ON seo'); } catch (\Throwable $e) {}
        try { DB::statement('DROP INDEX seo_parent_idx ON seo'); } catch (\Throwable $e) {}
    }
};
