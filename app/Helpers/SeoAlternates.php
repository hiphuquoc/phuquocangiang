<?php

namespace App\Helpers;

use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoTranslation;
use Illuminate\Support\Collection;

/**
 * SeoAlternates — sinh hreflang / canonical / x-default từ seo_translations.
 *
 * Cách dùng trong head.blade.php:
 *   $alternates = \App\Helpers\SeoAlternates::for($itemSeo);
 *   foreach ($alternates as $alt) {
 *      <link rel="alternate" hreflang="{{ $alt['code'] }}" href="{{ $alt['url'] }}" />
 *   }
 *
 * Mỗi entry trả về:
 *   ['code' => 'vi', 'url' => 'https://.../...', 'is_default' => true, 'language' => Language model]
 */
class SeoAlternates
{
    /**
     * Sinh danh sách alternates cho 1 entity (Seo hoặc model có ->seo).
     * @param mixed $entityOrSeo
     * @return Collection
     */
    public static function for($entityOrSeo): Collection {
        $seo = self::resolveSeo($entityOrSeo);
        if (!$seo) return collect();

        // Lấy translations published, kèm language
        $translations = SeoTranslation::with('language')
            ->where('seo_id', $seo->id)
            ->where('status', 'published')
            ->get();

        $appUrl = rtrim(env('APP_URL'), '/');
        $items  = collect();

        foreach ($translations as $t) {
            $lang = $t->language;
            if (!$lang || !$lang->is_active) continue;

            $prefix = $lang->is_default ? '' : '/' . $lang->code;
            $slug   = ltrim($t->slug_full ?? '', '/');
            $url    = $appUrl . $prefix . '/' . $slug;

            $items->push([
                'code'       => $lang->code,
                'og_locale'  => $lang->og_locale,
                'is_default' => (bool) $lang->is_default,
                'url'        => $url,
                'language'   => $lang,
                'translation'=> $t,
            ]);
        }
        return $items;
    }

    /**
     * URL theo locale cụ thể (canonical hoặc switcher) cho 1 entity.
     */
    public static function urlFor($entityOrSeo, ?string $locale = null): ?string {
        $seo = self::resolveSeo($entityOrSeo);
        if (!$seo) return null;
        $locale ??= app()->getLocale();
        $lang = Language::byCode($locale);
        if (!$lang) return null;

        $t = SeoTranslation::where('seo_id', $seo->id)
                            ->where('language_id', $lang->id)
                            ->first();
        if (!$t || empty($t->slug_full)) return null;

        $prefix = $lang->is_default ? '' : '/' . $lang->code;
        return rtrim(env('APP_URL'), '/') . $prefix . '/' . ltrim($t->slug_full, '/');
    }

    /**
     * x-default URL: ưu tiên locale default; nếu missing -> URL có alternate đầu tiên.
     */
    public static function xDefaultUrl($entityOrSeo): ?string {
        $alts = self::for($entityOrSeo);
        $default = $alts->firstWhere('is_default', true);
        if ($default) return $default['url'];
        return $alts->first()['url'] ?? null;
    }

    /**
     * Helper resolve: chấp nhận Seo, mọi model có ->seo, hoặc int (seo_id).
     */
    private static function resolveSeo($input): ?Seo {
        if ($input instanceof Seo) return $input;
        if (is_object($input) && method_exists($input, 'seo')) {
            return $input->seo;
        }
        if (is_object($input) && isset($input->seo)) {
            return $input->seo instanceof Seo ? $input->seo : Seo::find($input->seo->id ?? null);
        }
        if (is_numeric($input)) return Seo::find($input);
        return null;
    }
}
