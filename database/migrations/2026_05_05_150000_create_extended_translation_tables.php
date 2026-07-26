<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2 multilingual — DEEP TRANSLATIONS
 * ----------------------------------------
 * Sinh translation tables cho TẤT CẢ bảng quan hệ chứa text dịch được:
 *
 *   - tour_content_translations              (special_content, special_list, include, ...)
 *   - tour_timetable_translations            (title, content, content_sort)
 *   - tour_option_translations               (name)
 *   - tour_content_foreign_translations
 *   - tour_timetable_foreign_translations
 *   - tour_option_foreign_translations
 *   - hotel_content_translations             (name, content)
 *   - combo_content_translations             (name, content)
 *   - combo_option_translations              (name)
 *   - service_option_translations            (name)
 *   - question_answer_info_translations      (question, answer)
 *
 *   - seo_content_translations               (content longtext) — body content per locale,
 *                                              thay thế cơ chế blade file legacy.
 *
 * Triết lý: 1 entity row dùng chung cho mọi locale (chứa price/date/FK).
 * Mọi field text được tách sang `_translations` table, key (entity_id, language_id).
 *
 * Backfill: copy dữ liệu hiện tại sang language default (vi).
 *
 * Idempotent: chạy lại bao nhiêu lần cũng được.
 */
return new class extends Migration
{
    /**
     * Map: master_table => [translatable_columns]
     */
    private array $tablesMap = [
        'tour_content' => [
            'fk' => 'tour_content_id',
            'cols' => ['special_content', 'special_list', 'include', 'not_include', 'policy_child', 'menu', 'hotel', 'policy_cancel', 'note'],
        ],
        'tour_timetable' => [
            'fk' => 'tour_timetable_id',
            'cols' => ['title', 'content', 'content_sort'],
        ],
        'tour_option' => [
            'fk' => 'tour_option_id',
            'cols' => ['name'],
        ],
        'tour_content_foreign' => [
            'fk' => 'tour_content_foreign_id',
            'cols' => ['special_content', 'special_list', 'include', 'not_include', 'policy_child', 'menu', 'hotel', 'policy_cancel', 'note'],
        ],
        'tour_timetable_foreign' => [
            'fk' => 'tour_timetable_foreign_id',
            'cols' => ['title', 'content', 'content_sort'],
        ],
        'tour_option_foreign' => [
            'fk' => 'tour_option_foreign_id',
            'cols' => ['option'],
        ],
        'hotel_content' => [
            'fk' => 'hotel_content_id',
            'cols' => ['name', 'content'],
        ],
        'combo_content' => [
            'fk' => 'combo_content_id',
            'cols' => ['name', 'content'],
        ],
        'combo_option' => [
            'fk' => 'combo_option_id',
            'cols' => ['name'],
        ],
        'service_option' => [
            'fk' => 'service_option_id',
            'cols' => ['name'],
        ],
        'question_answer_info' => [
            'fk' => 'question_answer_info_id',
            'cols' => ['question', 'answer'],
        ],
    ];

    public function up()
    {
        $defaultLangId = $this->ensureDefaultLanguage();

        // 1) Sinh translation tables cho mọi master relation table
        foreach ($this->tablesMap as $master => $info) {
            if (!Schema::hasTable($master)) continue;

            $trans = $master . '_translations';
            $fk    = $info['fk'];
            $cols  = $info['cols'];

            if (!Schema::hasTable($trans)) {
                Schema::create($trans, function (Blueprint $table) use ($cols, $fk) {
                    $table->id();
                    $table->unsignedBigInteger($fk);
                    $table->unsignedBigInteger('language_id');
                    foreach ($cols as $col) $table->longText($col)->nullable();
                    $table->string('status', 20)->default('draft');
                    $table->string('translated_by', 20)->default('manual');
                    $table->timestamps();
                });

                try { DB::statement("CREATE INDEX {$trans}_lang_idx ON {$trans} (language_id)"); } catch (\Throwable $e) {}
                try { DB::statement("CREATE INDEX {$trans}_entity_idx ON {$trans} ({$fk})"); } catch (\Throwable $e) {}
                try { DB::statement("CREATE UNIQUE INDEX {$trans}_unique_entity_lang ON {$trans} ({$fk}, language_id)"); } catch (\Throwable $e) {}
            }

            // Backfill default locale (idempotent — chỉ chèn entity chưa có translation)
            $existedFks = DB::table($trans)
                ->where('language_id', $defaultLangId)
                ->pluck($fk)->all();

            $colsToCopy = array_values(array_filter($cols, fn($c) => Schema::hasColumn($master, $c)));
            if (empty($colsToCopy)) continue;

            $select = ['id as ' . $fk, DB::raw("{$defaultLangId} as language_id"), DB::raw("'published' as status")];
            foreach ($colsToCopy as $c) $select[] = $c;
            $select[] = DB::raw('NOW() as created_at');
            $select[] = DB::raw('NOW() as updated_at');

            $query = DB::table($master)->select($select);
            if (!empty($existedFks)) $query->whereNotIn('id', $existedFks);
            $rows = $query->get();
            if ($rows->isEmpty()) continue;

            foreach (array_chunk($rows->all(), 500) as $chunk) {
                $payload = array_map(fn($r) => (array) $r, $chunk);
                DB::table($trans)->insertOrIgnore($payload);
            }
        }

        // 2) seo_content_translations — body content per locale (thay blade file)
        if (!Schema::hasTable('seo_content_translations')) {
            Schema::create('seo_content_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seo_id');
                $table->unsignedBigInteger('language_id');
                $table->longText('content')->nullable();
                $table->string('status', 20)->default('draft');
                $table->string('translated_by', 20)->default('manual');
                $table->timestamps();
            });

            try { DB::statement('CREATE INDEX seo_content_trans_lang_idx ON seo_content_translations (language_id)'); } catch (\Throwable $e) {}
            try { DB::statement('CREATE INDEX seo_content_trans_seo_idx  ON seo_content_translations (seo_id)'); } catch (\Throwable $e) {}
            try { DB::statement('CREATE UNIQUE INDEX seo_content_trans_unique ON seo_content_translations (seo_id, language_id)'); } catch (\Throwable $e) {}
        }

        // 3) Backfill seo_content_translations từ blade file legacy (nếu có)
        //    Đọc file storage/app/public/contents/<type>/<slug>.blade.php → đẩy vào DB.
        $this->backfillSeoContentFromBlade($defaultLangId);
    }

    /**
     * Đọc các file content blade legacy và đẩy vào seo_content_translations.
     * Chỉ chạy nếu seo_content_translations còn trống cho default locale.
     */
    private function backfillSeoContentFromBlade(int $defaultLangId): void
    {
        if (!Schema::hasTable('seo')) return;

        $alreadyHas = DB::table('seo_content_translations')
            ->where('language_id', $defaultLangId)
            ->limit(1)->exists();
        if ($alreadyHas) return;

        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        $tablemysql = config('tablemysql', []);

        $payloadAll = [];
        $seos = DB::table('seo')->select('id', 'type', 'slug')
            ->whereNotNull('type')
            ->whereNotNull('slug')
            ->where('slug', '<>', '')
            ->get();

        foreach ($seos as $seo) {
            $cfg = $tablemysql[$seo->type] ?? null;
            if (empty($cfg['content_dir'])) continue;
            $path = rtrim($cfg['content_dir'], '/') . '/' . $seo->slug . '.blade.php';

            try {
                if (!$disk->exists($path)) continue;
                $content = $disk->get($path);
                if ($content === null) continue;

                $payloadAll[] = [
                    'seo_id'        => $seo->id,
                    'language_id'   => $defaultLangId,
                    'content'       => $content,
                    'status'        => 'published',
                    'translated_by' => 'auto-backfill',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            } catch (\Throwable $e) {
                // skip — không block migration vì 1 file lỗi
            }
        }

        foreach (array_chunk($payloadAll, 200) as $chunk) {
            DB::table('seo_content_translations')->insertOrIgnore($chunk);
        }

        if (!empty($payloadAll)) {
            fwrite(STDERR, PHP_EOL . "  [info] Backfilled " . count($payloadAll) . " seo_content_translations rows from blade files." . PHP_EOL);
        }
    }

    private function ensureDefaultLanguage(): int
    {
        $code = config('language.default_code', 'vi');
        $row = DB::table('languages')->where('code', $code)->first();
        if (!$row) {
            DB::table('languages')->insert([
                'code'        => $code,
                'name'        => 'Tiếng Việt',
                'name_native' => 'Tiếng Việt',
                'flag'        => '/images/flags/vi.svg',
                'og_locale'   => 'vi_VN',
                'dir'         => 'ltr',
                'is_active'   => 1,
                'is_default'  => 1,
                'sort'        => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $row = DB::table('languages')->where('code', $code)->first();
        }
        return (int) $row->id;
    }

    public function down() {
        // Không drop tự động để tránh mất dữ liệu phiên dịch.
    }
};
