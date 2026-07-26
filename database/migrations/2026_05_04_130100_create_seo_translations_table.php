<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 multilingual — bảng seo_translations.
 *
 * Mỗi entity (tour/ship/service/...) có 1 row trong seo và N row
 * seo_translations (mỗi ngôn ngữ 1 row).
 *
 * Cột:
 *  - seo_id           : FK seo.id (entity gốc)
 *  - language_id      : FK languages.id
 *  - title            : text — tiêu đề trang theo locale
 *  - description      : text — mô tả ngắn theo locale
 *  - seo_title        : meta title cho Google
 *  - seo_description  : meta description cho Google
 *  - slug             : segment cuối của URL (vd 'phu-quoc-tour')
 *  - slug_full        : URL đầy đủ (vd 'tour-trong-nuoc/phu-quoc-tour')
 *  - link_canonical   : canonical override (rỗng = self)
 *  - status           : 'published' | 'draft' (mặc định 'draft' cho locale mới)
 *  - translated_by    : 'manual' | 'auto' (đề xuất AI dùng cho dịch nháp)
 *
 * Constraint:
 *  - UNIQUE (seo_id, language_id) — tránh trùng dịch.
 *  - UNIQUE (language_id, slug_full(191)) — không 2 trang cùng URL trong 1 locale.
 *  - INDEX language_id, slug_full(191) cho routing nhanh.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('seo_translations')) return;

        Schema::create('seo_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seo_id');
            $table->unsignedBigInteger('language_id');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('slug')->nullable();
            $table->text('slug_full')->nullable();
            $table->text('link_canonical')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('translated_by', 20)->default('manual');
            $table->timestamps();

            $table->index('seo_id');
            $table->index('language_id');
        });

        // utf8mb4_bin cho slug/slug_full để so sánh case-sensitive
        try {
            DB::statement("ALTER TABLE seo_translations MODIFY slug TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
            DB::statement("ALTER TABLE seo_translations MODIFY slug_full TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin");
        } catch (\Throwable $e) {}

        try { DB::statement('CREATE INDEX seo_trans_slug_full_idx ON seo_translations (slug_full(191))'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE INDEX seo_trans_slug_idx ON seo_translations (slug(191))'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE UNIQUE INDEX seo_trans_unique_seo_lang ON seo_translations (seo_id, language_id)'); } catch (\Throwable $e) {}
        try { DB::statement('CREATE UNIQUE INDEX seo_trans_unique_lang_slug_full ON seo_translations (language_id, slug_full(191))'); } catch (\Throwable $e) {}
    }

    public function down()
    {
        // Schema::dropIfExists('seo_translations');
    }
};
