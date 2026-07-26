<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 — seed/upsert toàn bộ 10 ngôn ngữ thị trường mục tiêu.
 *
 * Đảm bảo các code sau tồn tại và đúng metadata:
 *   vi (default), en, zh-cn, zh-tw, ja, ko, es, fr, de, ru
 *
 * Đồng thời migrate dữ liệu cũ:
 *   - Nếu DB đã có row code='zh' → đổi code thành 'zh-cn' (để FK seo_translations.language_id
 *     vẫn tham chiếu đúng row sau khi đổi metadata).
 *
 * Idempotent: chạy nhiều lần đều ổn (insertOrIgnore + update theo code).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('languages')) return;

        $this->ensureHreflangColumn();

        // 1) Migrate cũ: zh -> zh-cn (giữ nguyên id để không vỡ FK)
        $oldZh = DB::table('languages')->where('code', 'zh')->first();
        $newZhCn = DB::table('languages')->where('code', 'zh-cn')->first();
        if ($oldZh && !$newZhCn) {
            DB::table('languages')->where('id', $oldZh->id)->update([
                'code'        => 'zh-cn',
                'name'        => 'Tiếng Trung (Giản thể)',
                'name_native' => '简体中文',
                'updated_at'  => now(),
            ]);
        }

        // 2) Upsert toàn bộ list từ config
        foreach (config('language.list', []) as $cfg) {
            $code = $cfg['code'];
            $payload = [
                'name'        => $cfg['name'],
                'name_native' => $cfg['name_native'] ?? null,
                'flag'        => $cfg['flag'] ?? null,
                'og_locale'   => $cfg['og_locale'] ?? null,
                'dir'         => $cfg['dir'] ?? 'ltr',
                'is_active'   => !empty($cfg['is_active']) ? 1 : 0,
                'is_default'  => !empty($cfg['is_default']) ? 1 : 0,
                'sort'        => $cfg['sort'] ?? 0,
                'updated_at'  => now(),
            ];

            // Nếu cột hreflang tồn tại thì set
            if (Schema::hasColumn('languages', 'hreflang')) {
                $payload['hreflang'] = $cfg['hreflang'] ?? $code;
            }

            $exists = DB::table('languages')->where('code', $code)->first();
            if ($exists) {
                DB::table('languages')->where('id', $exists->id)->update($payload);
            } else {
                $payload['code']       = $code;
                $payload['created_at'] = now();
                DB::table('languages')->insert($payload);
            }
        }

        // 3) Đảm bảo CHỈ duy nhất 1 default
        $defaultCode = config('language.default_code', 'vi');
        DB::table('languages')->update(['is_default' => 0]);
        DB::table('languages')->where('code', $defaultCode)->update(['is_default' => 1, 'is_active' => 1]);

        // 4) Clear cache languages (Cache::remember keys)
        try {
            \Illuminate\Support\Facades\Cache::forget('languages:active');
            \Illuminate\Support\Facades\Cache::forget('languages:all');
            \Illuminate\Support\Facades\Cache::forget('languages:default');
        } catch (\Throwable $e) {}
    }

    /**
     * Thêm cột `hreflang` (nullable) nếu chưa có — phục vụ <link rel="alternate">.
     */
    private function ensureHreflangColumn(): void
    {
        if (!Schema::hasColumn('languages', 'hreflang')) {
            try {
                Schema::table('languages', function (Blueprint $table) {
                    $table->string('hreflang', 16)->nullable()->after('og_locale');
                });
            } catch (\Throwable $e) {
                // ignore — có thể migration đã chạy trước hoặc DB không hỗ trợ ALTER
            }
        }
    }

    public function down()
    {
        // không revert — sẽ làm mất ngôn ngữ active.
    }
};
