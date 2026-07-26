<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoTranslation;
use App\Services\HtmlCacheService;
use Illuminate\Http\Request;

/**
 * SitemapController — Sinh sitemap đa ngôn ngữ.
 *
 * Quy ước URL:
 *  - /sitemap.xml                    -> index toàn site (default locale)
 *  - /sitemap/{type}.xml             -> URL của 1 type ở default locale
 *  - /{locale}/sitemap.xml           -> index riêng locale (vd /en/sitemap.xml)
 *  - /{locale}/sitemap/{type}.xml    -> URL của 1 type ở locale đó
 *
 * Mỗi <url> trong sitemap kèm <xhtml:link rel="alternate"> cho mọi locale
 * có translation published tương ứng — đúng chuẩn Google hreflang in sitemap.
 *
 * Cache: dùng HtmlCacheService với namespace = locale.
 */
class SitemapController extends Controller {

    /** sitemap.xml — index liệt kê các sitemap con theo type. */
    public static function main(Request $request) {
        $locale = $request->attributes->get('locale')
            ?? $request->route('locale')
            ?? config('language.default_code', 'vi');
        $lang   = Language::byCode($locale) ?? Language::default();
        if (!$lang) return response('', 404);

        // ============================================================
        // TEMP (i18n) — Tắt sitemap cho locale chưa dịch xong.
        // Chỉ phục vụ sitemap cho default locale (vi). Các locale khác
        // trả về 404 để không bị index bởi Google.
        // Khi dịch xong, xoá block này — xem docs/i18n-noindex-temporary.md
        // ============================================================
        if (!$lang->is_default) {
            return response('', 404);
        }

        $cache    = app(HtmlCacheService::class);
        $cacheKey = 'sitemaps/' . $lang->code . '/index';
        $prefix   = $lang->is_default ? '' : '/' . $lang->code;

        $xml = $cache->getOrRender($cacheKey, function () use ($prefix) {
            $types = [];
            foreach (config('tablemysql', []) as $type => $cfg) {
                if (!empty($cfg['sitemap']) && seo_type_enabled($type)) {
                    $types[] = $type;
                }
            }
            if (empty($types)) {
                $types = Seo::query()->whereNotNull('type')->distinct()->pluck('type')->all();
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                 . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($types as $type) {
                $url = rtrim(env('APP_URL'), '/') . $prefix . '/sitemap/' . $type . '.xml';
                $xml .= '<sitemap>'
                          . '<loc>' . htmlspecialchars($url, ENT_XML1) . '</loc>'
                          . '<lastmod>' . date('c') . '</lastmod>'
                      . '</sitemap>';
            }
            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($xml ?? '', 200, ['Content-Type' => 'application/xml']);
    }

    /** sitemap/{type}.xml — danh sách URL cho 1 type theo locale. */
    public static function child(Request $request, $type) {
        if (empty($type)) return ErrorController::error404();

        $cleanType = preg_replace('/[^a-z0-9_\-]/i', '', $type);
        if (!seo_type_enabled($cleanType)) {
            return ErrorController::error404();
        }

        $locale = $request->attributes->get('locale')
            ?? $request->route('locale')
            ?? config('language.default_code', 'vi');
        $lang   = Language::byCode($locale) ?? Language::default();
        if (!$lang) return response('', 404);

        // ============================================================
        // TEMP (i18n) — Tắt sitemap cho locale chưa dịch xong.
        // Chỉ phục vụ sitemap cho default locale (vi). Các locale khác
        // trả về 404 để không bị index bởi Google.
        // Khi dịch xong, xoá block này — xem docs/i18n-noindex-temporary.md
        // ============================================================
        if (!$lang->is_default) {
            return response('', 404);
        }

        $cache    = app(HtmlCacheService::class);
        $cacheKey = 'sitemaps/' . $lang->code . '/' . $cleanType;

        $xml = $cache->getOrRender($cacheKey, function () use ($cleanType, $lang) {
            // Lấy seo + translation cho locale yêu cầu (only published)
            $seoIds = Seo::where('type', $cleanType)->pluck('id');
            if ($seoIds->isEmpty()) return null;

            $translations = SeoTranslation::with('seo')
                ->whereIn('seo_id', $seoIds)
                ->where('language_id', $lang->id)
                ->where('status', 'published')
                ->whereNotNull('slug_full')
                ->get();

            if ($translations->isEmpty()) return null;

            // Pre-load mọi alternate cho hreflang annotation
            $allAlts = SeoTranslation::with('language')
                ->whereIn('seo_id', $seoIds)
                ->where('status', 'published')
                ->whereNotNull('slug_full')
                ->get()
                ->groupBy('seo_id');

            $appUrl = rtrim(env('APP_URL'), '/');

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                 . '<urlset xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
                 .         'xmlns:xhtml="http://www.w3.org/1999/xhtml" '
                 .         'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($translations as $t) {
                $seo = $t->seo;
                if (!$seo) continue;

                $prefix = $lang->is_default ? '' : '/' . $lang->code;
                $loc = $appUrl . $prefix . '/' . ltrim($t->slug_full, '/');
                $img = !empty($seo->image) ? $appUrl . $seo->image : '';
                $alt = self::xmlEscape($t->seo_title ?: $t->title);

                $xml .= '<url>'
                      . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                      . '<lastmod>' . date('c', strtotime(($t->updated_at ?? $seo->updated_at) ?: 'now')) . '</lastmod>'
                      . '<changefreq>weekly</changefreq>'
                      . '<priority>0.8</priority>';

                // Hreflang annotations cho mọi locale alternate
                $alternates = $allAlts->get($seo->id, collect());
                foreach ($alternates as $a) {
                    $altLang = $a->language;
                    if (!$altLang || !$altLang->is_active) continue;
                    $altPrefix = $altLang->is_default ? '' : '/' . $altLang->code;
                    $altLoc    = $appUrl . $altPrefix . '/' . ltrim($a->slug_full, '/');
                    $xml .= '<xhtml:link rel="alternate" hreflang="' . $altLang->code . '" href="' . htmlspecialchars($altLoc, ENT_XML1) . '" />';
                }
                // x-default
                $defaultAlt = $alternates->first(fn($a) => optional($a->language)->is_default);
                if ($defaultAlt) {
                    $defaultLang = $defaultAlt->language;
                    $defaultLoc  = $appUrl . '/' . ltrim($defaultAlt->slug_full, '/');
                    $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultLoc, ENT_XML1) . '" />';
                }

                if (!empty($img)) {
                    $xml .= '<image:image>'
                          . '<image:loc>' . htmlspecialchars($img, ENT_XML1) . '</image:loc>'
                          . '<image:title>' . $alt . '</image:title>'
                          . '</image:image>';
                }
                $xml .= '</url>';
            }
            $xml .= '</urlset>';
            return $xml;
        });

        if (empty($xml)) return ErrorController::error404();
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public static function xmlEscape($str) {
        if (empty($str)) return '';
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** @deprecated giữ tương thích nếu còn nơi gọi */
    public static function replaceSpecialCharactorXml($str) {
        return self::xmlEscape($str);
    }
}
