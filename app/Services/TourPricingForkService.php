<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TourOption;
use App\Models\TourOptionForeign;
use App\Models\TourPrice;
use App\Models\TourPriceForeign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tách bộ Tùy chọn & Giá theo ngôn ngữ dịch (admin translation).
 *
 * - Bản gốc: tour_option / tour_option_foreign có language_id = null.
 * - Bản dịch: sau lần fork đầu tiên, mỗi locale có bộ option+price + translation rows riêng.
 * - fork_source_id trỏ về option gốc để map ID khi UI còn đang hiển thị bản mượn từ master.
 */
class TourPricingForkService
{
    public function parseTranslationLanguageId(Request $request): ?int
    {
        $locale = $request->input('_translation_locale');
        if ($locale === null || $locale === '') {
            return null;
        }
        if ($locale === config('language.default_code', 'vi')) {
            return null;
        }
        $lang = Language::byCode((string) $locale);

        return $lang?->id;
    }

    public function hasDomesticFork(int $tourInfoId, int $languageId): bool
    {
        return TourOption::query()
            ->where('tour_info_id', $tourInfoId)
            ->where('language_id', $languageId)
            ->exists();
    }

    public function hasForeignFork(int $tourInfoForeignId, int $languageId): bool
    {
        return TourOptionForeign::query()
            ->where('tour_info_foreign_id', $tourInfoForeignId)
            ->where('language_id', $languageId)
            ->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\TourOption>
     */
    public function domesticOptionsForDisplay(int $tourInfoId, int $languageId)
    {
        if ($this->hasDomesticFork($tourInfoId, $languageId)) {
            return TourOption::query()
                ->where('tour_info_id', $tourInfoId)
                ->where('language_id', $languageId)
                ->with('prices')
                ->orderBy('id')
                ->get();
        }

        return TourOption::query()
            ->where('tour_info_id', $tourInfoId)
            ->whereNull('language_id')
            ->with('prices')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\TourOptionForeign>
     */
    public function foreignOptionsForDisplay(int $tourInfoForeignId, int $languageId)
    {
        if ($this->hasForeignFork($tourInfoForeignId, $languageId)) {
            return TourOptionForeign::query()
                ->where('tour_info_foreign_id', $tourInfoForeignId)
                ->where('language_id', $languageId)
                ->with('prices')
                ->orderBy('id')
                ->get();
        }

        return TourOptionForeign::query()
            ->where('tour_info_foreign_id', $tourInfoForeignId)
            ->whereNull('language_id')
            ->with('prices')
            ->orderBy('id')
            ->get();
    }

    public function ensureDomesticFork(int $tourInfoId, int $languageId): void
    {
        if ($this->hasDomesticFork($tourInfoId, $languageId)) {
            return;
        }
        $masters = TourOption::query()
            ->where('tour_info_id', $tourInfoId)
            ->whereNull('language_id')
            ->with('prices')
            ->orderBy('id')
            ->get();
        if ($masters->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($masters, $tourInfoId, $languageId) {
            foreach ($masters as $old) {
                $new = new TourOption();
                $new->tour_info_id = $tourInfoId;
                $new->name = $old->getRawOriginal('name') ?? $old->name;
                $new->language_id = $languageId;
                $new->fork_source_id = $old->id;
                $new->save();

                if ($this->translationTableExists('tour_option_translations')) {
                    $rows = DB::table('tour_option_translations')->where('tour_option_id', $old->id)->get();
                    foreach ($rows as $tr) {
                        DB::table('tour_option_translations')->insert([
                            'tour_option_id' => $new->id,
                            'language_id'    => $tr->language_id,
                            'name'           => $tr->name,
                            'status'         => $tr->status ?? 'published',
                            'translated_by'  => $tr->translated_by ?? 'fork',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }

                foreach ($old->prices as $p) {
                    TourPrice::insertItem([
                        'tour_option_id' => $new->id,
                        'apply_age'      => $p->apply_age,
                        'price'          => $p->price,
                        'profit'         => $p->profit,
                        'date_start'     => $p->date_start,
                        'date_end'       => $p->date_end,
                    ]);
                }
            }
        });
    }

    public function ensureForeignFork(int $tourInfoForeignId, int $languageId): void
    {
        if ($this->hasForeignFork($tourInfoForeignId, $languageId)) {
            return;
        }
        $masters = TourOptionForeign::query()
            ->where('tour_info_foreign_id', $tourInfoForeignId)
            ->whereNull('language_id')
            ->with('prices')
            ->orderBy('id')
            ->get();
        if ($masters->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($masters, $tourInfoForeignId, $languageId) {
            foreach ($masters as $old) {
                $new = new TourOptionForeign();
                $new->tour_info_foreign_id = $tourInfoForeignId;
                $new->option = $old->getRawOriginal('option') ?? $old->option;
                $new->language_id = $languageId;
                $new->fork_source_id = $old->id;
                $new->save();

                if ($this->translationTableExists('tour_option_foreign_translations')) {
                    $rows = DB::table('tour_option_foreign_translations')->where('tour_option_foreign_id', $old->id)->get();
                    foreach ($rows as $tr) {
                        DB::table('tour_option_foreign_translations')->insert([
                            'tour_option_foreign_id' => $new->id,
                            'language_id'            => $tr->language_id,
                            'option'                 => $tr->option,
                            'status'                 => $tr->status ?? 'published',
                            'translated_by'          => $tr->translated_by ?? 'fork',
                            'created_at'             => now(),
                            'updated_at'             => now(),
                        ]);
                    }
                }

                foreach ($old->prices as $p) {
                    TourPriceForeign::insertItem([
                        'tour_option_foreign_id' => $new->id,
                        'apply_age'              => $p->apply_age,
                        'price'                  => $p->price,
                        'profit'                 => $p->profit,
                        'date_start'             => $p->date_start,
                        'date_end'               => $p->date_end,
                    ]);
                }
            }
        });
    }

    /**
     * Chuẩn bị fork (nếu cần) trước khi thêm option mới khi đã có bộ master.
     */
    public function prepareDomesticForkBeforeCreate(int $tourInfoId, ?int $languageId): void
    {
        if (!$languageId || $tourInfoId <= 0) {
            return;
        }
        if ($this->hasDomesticFork($tourInfoId, $languageId)) {
            return;
        }
        if (TourOption::query()->where('tour_info_id', $tourInfoId)->whereNull('language_id')->exists()) {
            $this->ensureDomesticFork($tourInfoId, $languageId);
        }
    }

    public function prepareForeignForkBeforeCreate(int $tourInfoForeignId, ?int $languageId): void
    {
        if (!$languageId || $tourInfoForeignId <= 0) {
            return;
        }
        if ($this->hasForeignFork($tourInfoForeignId, $languageId)) {
            return;
        }
        if (TourOptionForeign::query()->where('tour_info_foreign_id', $tourInfoForeignId)->whereNull('language_id')->exists()) {
            $this->ensureForeignFork($tourInfoForeignId, $languageId);
        }
    }

    public function resolveDomesticOptionId(Request $request, int $requestedId): int
    {
        $langId = $this->parseTranslationLanguageId($request);
        if (!$langId || $requestedId <= 0) {
            return $requestedId;
        }
        $opt = TourOption::query()->find($requestedId);
        if (!$opt) {
            return $requestedId;
        }
        if ((int) $opt->language_id === $langId) {
            return $requestedId;
        }
        if ($opt->language_id !== null) {
            return $requestedId;
        }
        $this->ensureDomesticFork((int) $opt->tour_info_id, $langId);
        $mapped = TourOption::query()
            ->where('fork_source_id', $requestedId)
            ->where('language_id', $langId)
            ->value('id');

        return $mapped ? (int) $mapped : $requestedId;
    }

    public function resolveForeignOptionId(Request $request, int $requestedId): int
    {
        $langId = $this->parseTranslationLanguageId($request);
        if (!$langId || $requestedId <= 0) {
            return $requestedId;
        }
        $opt = TourOptionForeign::query()->find($requestedId);
        if (!$opt) {
            return $requestedId;
        }
        if ((int) $opt->language_id === $langId) {
            return $requestedId;
        }
        if ($opt->language_id !== null) {
            return $requestedId;
        }
        $this->ensureForeignFork((int) $opt->tour_info_foreign_id, $langId);
        $mapped = TourOptionForeign::query()
            ->where('fork_source_id', $requestedId)
            ->where('language_id', $langId)
            ->value('id');

        return $mapped ? (int) $mapped : $requestedId;
    }

    public static function deleteDomesticOptionCascade(int $optionId): void
    {
        if (Schema::hasTable('tour_option_translations')) {
            DB::table('tour_option_translations')->where('tour_option_id', $optionId)->delete();
        }
        TourPrice::query()->where('tour_option_id', $optionId)->delete();
        TourOption::query()->where('id', $optionId)->delete();
    }

    public static function deleteForeignOptionCascade(int $optionId): void
    {
        if (Schema::hasTable('tour_option_foreign_translations')) {
            DB::table('tour_option_foreign_translations')->where('tour_option_foreign_id', $optionId)->delete();
        }
        TourPriceForeign::query()->where('tour_option_foreign_id', $optionId)->delete();
        TourOptionForeign::query()->where('id', $optionId)->delete();
    }

    private function translationTableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
