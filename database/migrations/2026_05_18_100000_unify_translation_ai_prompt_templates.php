<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Prompt template dịch dùng chung (scope=translation), không gắn locale/seo_type.
 * Tùy biến theo ngôn ngữ/loại trang qua token [target_language], [locale], [seo_type], [source].
 */
return new class extends Migration {
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('ai_prompt_templates')) {
            return;
        }

        DB::table('ai_prompt_templates')
            ->where('scope', 'translation')
            ->update([
                'locale' => null,
                'seo_type' => null,
            ]);
    }

    public function down(): void
    {
        // Không khôi phục locale/seo_type cũ.
    }
};
