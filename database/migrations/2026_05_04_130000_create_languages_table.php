<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 multilingual — bảng `languages`.
 *
 * Mỗi row = 1 ngôn ngữ hệ thống hỗ trợ. Là FK cho seo_translations & các
 * <entity>_translations.
 *
 * Cột:
 *  - code           : 'vi', 'en', 'zh-CN', 'ja', 'ko' (unique)
 *  - name           : tên hiển thị tiếng Việt cho admin
 *  - name_native    : tên ở chính ngôn ngữ đó (English, 中文, ...)
 *  - flag           : URL ảnh cờ
 *  - og_locale      : 'vi_VN', 'en_US', 'zh_CN' (Open Graph)
 *  - dir            : 'ltr' / 'rtl'
 *  - is_active      : true => xuất hiện ở public (route, menu, sitemap)
 *  - is_default     : true => không có URL prefix; chỉ 1 row có giá trị này
 *  - sort           : thứ tự hiển thị
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('languages')) return;

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->string('name_native', 50)->nullable();
            $table->string('flag')->nullable();
            $table->string('og_locale', 20)->nullable();
            $table->string('dir', 3)->default('ltr');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Seed từ config/language.php
        $rows = [];
        foreach (config('language.list', []) as $cfg) {
            $rows[] = [
                'code'        => $cfg['code'],
                'name'        => $cfg['name'],
                'name_native' => $cfg['name_native'] ?? null,
                'flag'        => $cfg['flag'] ?? null,
                'og_locale'   => $cfg['og_locale'] ?? null,
                'dir'         => $cfg['dir'] ?? 'ltr',
                'is_active'   => !empty($cfg['is_active']) ? 1 : 0,
                'is_default'  => !empty($cfg['is_default']) ? 1 : 0,
                'sort'        => $cfg['sort'] ?? 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        if (!empty($rows)) DB::table('languages')->insert($rows);
    }

    public function down()
    {
        // Schema::dropIfExists('languages');
    }
};
