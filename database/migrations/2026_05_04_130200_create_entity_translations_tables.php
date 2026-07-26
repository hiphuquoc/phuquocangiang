<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 multilingual — sinh batch <entity>_translations cho mỗi seo.type
 * có khai báo `translatable` trong config/tablemysql.php.
 *
 * Cấu trúc bảng dịch:
 *   id, <entity>_id, language_id, <translatable_columns...>, timestamps
 *
 * Quy ước tên bảng: tour_info_translations, ship_info_translations,
 * tour_location_translations, ...
 *
 * Ý đồ:
 *  - Bảng entity gốc (tour_info, ship_info...) GIỮ NGUYÊN cột name/description/...
 *    để tương thích ngược trong giai đoạn dual-read. Khi mọi controller chuyển
 *    qua đọc translations xong, mới cân nhắc DROP các cột đó (Phase 2).
 *  - Backfill dữ liệu hiện tại sang language_id = vi (default).
 */
return new class extends Migration
{
    public function up()
    {
        $defaultLangId = $this->ensureDefaultLanguage();

        foreach (config('tablemysql', []) as $type => $cfg) {
            $translatable = $cfg['translatable'] ?? [];
            if (empty($translatable)) continue;

            $entityTable = $this->entityTableFromType($type);
            if (!Schema::hasTable($entityTable)) continue;
            $entityFk    = $this->entityFkFromType($type);
            $transTable  = $entityTable . '_translations';

            // 1. Create translation table if not exists
            if (!Schema::hasTable($transTable)) {
                Schema::create($transTable, function (Blueprint $table) use ($translatable, $entityFk) {
                    $table->id();
                    $table->unsignedBigInteger($entityFk);
                    $table->unsignedBigInteger('language_id');
                    foreach ($translatable as $col) {
                        $table->text($col)->nullable();
                    }
                    $table->string('status', 20)->default('draft');
                    $table->string('translated_by', 20)->default('manual');
                    $table->timestamps();
                });

                try { DB::statement("CREATE INDEX {$transTable}_lang_idx ON {$transTable} (language_id)"); } catch (\Throwable $e) {}
                try { DB::statement("CREATE INDEX {$transTable}_entity_idx ON {$transTable} ({$entityFk})"); } catch (\Throwable $e) {}
                try { DB::statement("CREATE UNIQUE INDEX {$transTable}_unique_entity_lang ON {$transTable} ({$entityFk}, language_id)"); } catch (\Throwable $e) {}
            }

            // 2. Backfill data hiện tại — idempotent.
            //    Chỉ chèn entity chưa có translation cho default locale, dùng
            //    insertOrIgnore để rerun an toàn (không đụng UNIQUE entity_id+lang_id).
            $columnsToCopy = array_values(array_filter($translatable, fn($c) => Schema::hasColumn($entityTable, $c)));
            if (empty($columnsToCopy)) continue;

            $existedFks = DB::table($transTable)
                ->where('language_id', $defaultLangId)
                ->pluck($entityFk)->all();

            $select  = ['id as ' . $entityFk, DB::raw("{$defaultLangId} as language_id"), DB::raw("'published' as status")];
            foreach ($columnsToCopy as $c) $select[] = $c;
            $select[] = DB::raw("NOW() as created_at");
            $select[] = DB::raw("NOW() as updated_at");

            $query = DB::table($entityTable)->select($select);
            if (!empty($existedFks)) $query->whereNotIn('id', $existedFks);
            $rows = $query->get();
            if ($rows->isEmpty()) continue;

            foreach (array_chunk($rows->all(), 500) as $chunk) {
                $payload = array_map(fn($r) => (array) $r, $chunk);
                DB::table($transTable)->insertOrIgnore($payload);
            }
        }

        // Backfill seo_translations cho mọi row seo hiện tại (idempotent).
        // Lưu ý: bảng seo lịch sử có thể chứa các slug_full TRÙNG (ví dụ
        // 'tour-du-lich-da-nang' tồn tại cả ở type=tour_location và
        // type=tour_departure). seo_translations có UNIQUE(language_id, slug_full)
        // nên ta dùng insertOrIgnore và in cảnh báo các seo_id bị bỏ qua để
        // admin có thể merge / đổi slug sau qua CMS.
        if (Schema::hasTable('seo') && Schema::hasTable('seo_translations')) {
            $columns  = ['title', 'description', 'seo_title', 'seo_description', 'slug', 'slug_full', 'link_canonical'];
            $existing = array_values(array_filter($columns, fn($c) => Schema::hasColumn('seo', $c)));

            $existedSeoIds = DB::table('seo_translations')
                ->where('language_id', $defaultLangId)
                ->pluck('seo_id')->all();

            $select = ['id as seo_id', DB::raw("{$defaultLangId} as language_id"), DB::raw("'published' as status")];
            foreach ($existing as $c) $select[] = $c;
            $select[] = DB::raw('NOW() as created_at');
            $select[] = DB::raw('NOW() as updated_at');

            $query = DB::table('seo')->select($select);
            if (!empty($existedSeoIds)) $query->whereNotIn('id', $existedSeoIds);
            $rows = $query->get();

            if ($rows->isNotEmpty()) {
                $beforeCount = DB::table('seo_translations')->where('language_id', $defaultLangId)->count();
                foreach (array_chunk($rows->all(), 500) as $chunk) {
                    $payload = array_map(fn($r) => (array) $r, $chunk);
                    DB::table('seo_translations')->insertOrIgnore($payload);
                }
                $afterCount = DB::table('seo_translations')->where('language_id', $defaultLangId)->count();
                $skipped    = $rows->count() - ($afterCount - $beforeCount);
                if ($skipped > 0) $this->reportDuplicateSlugFulls($defaultLangId);
            }
        }
    }

    /**
     * In ra danh sách seo_id bị bỏ qua do trùng slug_full ở default locale.
     * Không throw — chỉ cảnh báo để admin xử lý qua CMS.
     */
    private function reportDuplicateSlugFulls(int $defaultLangId): void
    {
        $orphans = DB::table('seo as s')
            ->leftJoin('seo_translations as st', function ($j) use ($defaultLangId) {
                $j->on('st.seo_id', '=', 's.id')->where('st.language_id', '=', $defaultLangId);
            })
            ->whereNull('st.id')
            ->whereNotNull('s.slug_full')
            ->where('s.slug_full', '<>', '')
            ->select('s.id', 's.type', 's.slug_full', 's.title')
            ->orderBy('s.slug_full')
            ->get();

        if ($orphans->isEmpty()) return;

        $msg  = PHP_EOL . "  [warn] Backfill seo_translations: bo qua " . $orphans->count()
              . " seo_id do trung (language_id={$defaultLangId}, slug_full)." . PHP_EOL;
        $msg .= "         Cac URL nay hien chua co ban dich default. Hay doi slug hoac xoa row trung trong CMS." . PHP_EOL;
        foreach ($orphans as $o) {
            $msg .= sprintf("         - seo_id=%-5d type=%-20s slug_full=%-50s title=%s" . PHP_EOL,
                $o->id, $o->type, $o->slug_full, mb_substr((string) $o->title, 0, 60));
        }
        fwrite(STDERR, $msg);
    }

    public function down() {
        // Không drop tự động trong down() để tránh mất dữ liệu phiên dịch.
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
                'flag'        => '/images/flags/vi.png',
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

    /**
     * Map seo.type -> tên bảng entity tương ứng.
     * Ví dụ: 'tour_info' -> 'tour_info', 'tour_location' -> 'tour_location'.
     */
    private function entityTableFromType(string $type): string
    {
        // type đã là tên bảng entity gốc trong hitour
        return $type;
    }

    /**
     * Map seo.type -> tên cột FK trong bảng dịch.
     */
    private function entityFkFromType(string $type): string
    {
        return $type . '_id';
    }
};
