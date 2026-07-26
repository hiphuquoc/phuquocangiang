<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SeoContentTranslation — body content per locale.
 *
 * Thay thế cơ chế blade file legacy (`storage/.../contents/<type>/<slug>.blade.php`).
 * Giờ content (HTML / blade) được lưu trong DB, key (seo_id, language_id).
 *
 * Đọc qua RoutingController::renderContentBlade() — đã được Phase 2 cập nhật để
 * ưu tiên DB → fallback file legacy.
 */
class SeoContentTranslation extends Model {
    use HasFactory;

    protected $table    = 'seo_content_translations';
    protected $fillable = ['seo_id', 'language_id', 'content', 'status', 'translated_by'];

    public function seo(): BelongsTo {
        return $this->belongsTo(Seo::class, 'seo_id', 'id');
    }

    public function language(): BelongsTo {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    /** Convenient lookup */
    public static function forLocale(int $seoId, ?string $locale = null): ?self {
        $locale ??= app()->getLocale();
        $langId = optional(Language::byCode($locale))->id;
        if (!$langId) return null;
        return self::where('seo_id', $seoId)->where('language_id', $langId)->first();
    }

    /** Get content with fallback chain: locale → default → null */
    public static function getContent(int $seoId, ?string $locale = null): ?string {
        $row = self::forLocale($seoId, $locale);
        if ($row && !empty($row->content)) return $row->content;

        $defaultCode = config('language.default_code', 'vi');
        if ($locale !== $defaultCode) {
            $rowDefault = self::forLocale($seoId, $defaultCode);
            if ($rowDefault && !empty($rowDefault->content)) return $rowDefault->content;
        }
        return null;
    }
}
