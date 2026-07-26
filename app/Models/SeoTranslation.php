<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SeoTranslation — phiên bản ngôn ngữ của 1 dòng `seo`.
 *
 * Mỗi entity (tour, ship, ...) link tới 1 row `seo` qua `seo_id`. Mỗi
 * `seo_id` có nhiều row `seo_translations` (1 row / language).
 *
 * Routing đa ngôn ngữ:
 *   SeoTranslation::query()
 *       ->where('language_id', $localeId)
 *       ->where('slug_full', $url)
 *       ->where('status', 'published')
 *       ->first()
 *
 * Auto management:
 *  - Khi `slug` thay đổi -> tự build lại `slug_full`, đệ quy update children
 *    (cùng locale) và tạo redirect 301.
 *  - Khi xoá: cascade vẫn để controller tự xử (an toàn cho dữ liệu).
 */
class SeoTranslation extends Model {
    use HasFactory;

    protected $table    = 'seo_translations';
    protected $fillable = [
        'seo_id',
        'language_id',
        'title',
        'description',
        'seo_title',
        'seo_description',
        'slug',
        'slug_full',
        'link_canonical',
        'status',
        'translated_by',
    ];

    public function seo(): BelongsTo {
        return $this->belongsTo(Seo::class, 'seo_id', 'id');
    }

    public function language(): BelongsTo {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    /**
     * Lấy tất cả bản dịch của cùng entity (chung seo_id) — phục vụ hreflang.
     */
    public function alternates(): HasMany {
        return $this->hasMany(SeoTranslation::class, 'seo_id', 'seo_id');
    }

    /* =================== Helpers =================== */

    /**
     * Build full URL theo (slug, parent_seo_id, locale).
     * O(1) — chỉ query slug_full của parent translation cùng locale.
     */
    public static function buildFullUrl(?string $slug, ?int $parentSeoId, int $languageId): ?string {
        if (empty($slug)) return null;
        $slug = ltrim($slug, '/');
        if (empty($parentSeoId)) return $slug;

        $parent = self::select('slug_full')
                    ->where('seo_id', $parentSeoId)
                    ->where('language_id', $languageId)
                    ->first();
        if (!empty($parent->slug_full)) {
            return rtrim($parent->slug_full, '/') . '/' . $slug;
        }
        return $slug;
    }

    /**
     * Tạo / cập nhật bản dịch.
     * Tự cascade slug_full cho children & tạo 301 redirect.
     *
     * @param int   $seoId
     * @param int   $languageId
     * @param array $params  ['title'=>..., 'slug'=>..., ...]
     */
    public static function upsertTranslation(int $seoId, int $languageId, array $params): self {
        $existing = self::where('seo_id', $seoId)->where('language_id', $languageId)->first();

        $slugOld     = $existing->slug ?? null;
        $slugFullOld = $existing->slug_full ?? null;

        // Tính slug_full mới nếu caller chưa truyền sẵn
        if (!array_key_exists('slug_full', $params) && array_key_exists('slug', $params)) {
            $seo = Seo::find($seoId);
            $params['slug_full'] = self::buildFullUrl($params['slug'], $seo->parent ?? null, $languageId);
        }

        if ($existing) {
            $existing->fill($params);
            $existing->save();
            $row = $existing;
        } else {
            $row = self::create(array_merge([
                'seo_id'      => $seoId,
                'language_id' => $languageId,
                'status'      => 'draft',
            ], $params));
        }

        // Cascade nếu slug_full đổi
        if (!empty($slugFullOld) && $slugFullOld !== $row->slug_full) {
            self::cascadeSlugFullChildren($seoId, $languageId);
            self::createRedirect301($slugFullOld, $row->slug_full, $languageId);
        }

        return $row;
    }

    /**
     * Cập nhật slug_full cho mọi seo_translations là con (cùng locale).
     */
    public static function cascadeSlugFullChildren(int $parentSeoId, int $languageId): void {
        $childSeos = Seo::select('id', 'parent', 'slug')->where('parent', $parentSeoId)->get();
        foreach ($childSeos as $childSeo) {
            $childTrans = self::where('seo_id', $childSeo->id)
                              ->where('language_id', $languageId)
                              ->first();
            if (!$childTrans) continue;

            $newSlugFull = self::buildFullUrl($childTrans->slug, $childSeo->parent, $languageId);
            if ($newSlugFull !== $childTrans->slug_full) {
                $oldFull = $childTrans->slug_full;
                $childTrans->update(['slug_full' => $newSlugFull]);
                self::createRedirect301($oldFull, $newSlugFull, $languageId);
                self::cascadeSlugFullChildren($childSeo->id, $languageId);
            }
        }
    }

    /**
     * Tạo bản 301 redirect trong redirect_info, lưu URL ĐẦY ĐỦ có prefix locale.
     */
    public static function createRedirect301(?string $slugFullOld, ?string $slugFullNew, int $languageId): void {
        if (empty($slugFullOld) || empty($slugFullNew) || $slugFullOld === $slugFullNew) return;
        try {
            if (!\Schema::hasTable('redirect_info')) return;

            $lang = Language::find($languageId);
            $prefix = ($lang && !$lang->is_default) ? '/' . $lang->code : '';

            $urlOld = $prefix . '/' . ltrim($slugFullOld, '/');
            $urlNew = $prefix . '/' . ltrim($slugFullNew, '/');

            $exists = DB::table('redirect_info')->where('url_old', $urlOld)->exists();
            if (!$exists) {
                DB::table('redirect_info')->insert(['url_old' => $urlOld, 'url_new' => $urlNew]);
            }
            DB::table('redirect_info')->where('url_new', $urlOld)->update(['url_new' => $urlNew]);
        } catch (\Throwable $e) {
            Log::warning('SeoTranslation::createRedirect301 failed: ' . $e->getMessage());
        }
    }
}
