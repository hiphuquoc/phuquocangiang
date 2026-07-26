<?php

namespace App\Helpers;

use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Url {

    /** in-memory cache cho 1 request — tránh truy DB nhiều lần khi 1 request load nhiều partial */
    protected static array $memoizeCheck = [];
    protected static array $memoizeBreadcrumb = [];

    /**
     * Tìm dòng SEO theo URL đầy đủ (slug_full) trong locale chỉ định.
     *
     * Phase 1 multilingual:
     *  - Nếu có $locale -> tìm trong seo_translations (locale, slug_full).
     *  - Nếu không có -> giữ logic legacy: query bảng `seo` theo slug_full.
     *  - Trả về Seo (có gắn @relation translation) cho controller dùng.
     *
     * @param string|null $slugFull
     * @param string|null $locale  (optional)
     * @return Seo|null
     */
    public static function checkUrlExists($slugFull, ?string $locale = null) {
        if (empty($slugFull)) return null;
        $slugFull = trim($slugFull, '/');

        $locale ??= app()->getLocale();
        $key = $locale . '|' . $slugFull;

        if (array_key_exists($key, self::$memoizeCheck)) {
            return self::$memoizeCheck[$key];
        }

        $info = null;

        // 1) Truy vấn qua seo_translations nếu bảng tồn tại
        if (Schema::hasTable('seo_translations')) {
            $lang = Language::byCode($locale);
            if ($lang) {
                $row = SeoTranslation::query()
                        ->where('language_id', $lang->id)
                        ->where('status', 'published')
                        ->whereRaw('slug_full COLLATE utf8mb4_bin = ?', [$slugFull])
                        ->first();
                if ($row) {
                    $info = Seo::find($row->seo_id);
                    if ($info) {
                        // Gắn translation đã tìm được vào Seo để controller có thể dùng trực tiếp
                        $info->setRelation('translation', $row);
                    }
                }
            }
        }

        // 2) Fallback: legacy query bảng seo
        if (!$info) {
            $info = Seo::select('*')
                        ->where('slug_full', $slugFull)
                        ->first();
        }

        return self::$memoizeCheck[$key] = $info;
    }

    /**
     * Build breadcrumb dựa trên slug_full (đi ngược cây cha qua bảng `seo`).
     * Phase 1: đọc slug từ seo_translations nếu có để hiển thị đúng locale.
     */
    public static function buildBreadcrumb($slugFull) {
        if (empty($slugFull)) return null;
        $slugFull = trim($slugFull, '/');

        $locale = app()->getLocale();
        $key = $locale . '|' . $slugFull;
        if (array_key_exists($key, self::$memoizeBreadcrumb)) {
            return self::$memoizeBreadcrumb[$key];
        }

        $segments = array_values(array_filter(explode('/', $slugFull), fn($s) => $s !== ''));
        if (empty($segments)) return null;

        $result = new \Illuminate\Database\Eloquent\Collection;

        if (Schema::hasTable('seo_translations')) {
            $lang = Language::byCode($locale);
            if ($lang) {
                // Build chain dần từ '' -> 'a' -> 'a/b' -> ...
                $accum = [];
                $chain = [];
                foreach ($segments as $seg) {
                    $accum[] = $seg;
                    $chain[] = implode('/', $accum);
                }
                $rows = SeoTranslation::with('seo')
                    ->where('language_id', $lang->id)
                    ->whereIn('slug_full', $chain)
                    ->get()
                    ->keyBy('slug_full');

                // V3.0: nếu thiếu segment ở locale hiện tại, fallback default locale
                // cho từng segment đó (tránh 500 khi bản dịch chưa đủ).
                $missingSegments = array_values(array_filter($chain, fn($sf) => empty($rows[$sf])));
                if (!empty($missingSegments)) {
                    $defaultCode = config('language.default_code', 'vi');
                    $defaultLang = ($defaultCode === $locale) ? $lang : Language::byCode($defaultCode);
                    if ($defaultLang) {
                        $fallbackRows = SeoTranslation::with('seo')
                            ->where('language_id', $defaultLang->id)
                            ->whereIn('slug_full', $missingSegments)
                            ->get()
                            ->keyBy('slug_full');
                        foreach ($fallbackRows as $sf => $row) {
                            if (empty($rows[$sf])) $rows[$sf] = $row;
                        }
                    }
                }

                foreach ($chain as $sf) {
                    if (empty($rows[$sf])) {
                        self::$memoizeBreadcrumb[$key] = null;
                        return null;
                    }
                    // Mỗi item: Seo + đã có translation
                    $seo = $rows[$sf]->seo;
                    if ($seo) {
                        $seo->setRelation('translation', $rows[$sf]);
                        $result[] = $seo;
                    }
                }
                return self::$memoizeBreadcrumb[$key] = $result;
            }
        }

        // Legacy fallback
        $rows = Seo::select('*')
                    ->whereIn('slug', $segments)
                    ->get()
                    ->keyBy('slug');
        foreach ($segments as $slug) {
            if (empty($rows[$slug])) {
                self::$memoizeBreadcrumb[$key] = null;
                return null;
            }
            $result[] = $rows[$slug];
        }
        return self::$memoizeBreadcrumb[$key] = $result;
    }

    /**
     * Cắt query string + hash khỏi URL (dùng trong middleware/redirect).
     */
    public static function removeUrlParamsAndHash($url) {
        $parsed = parse_url($url);
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'].'://' : '';
        $host   = $parsed['host'] ?? '';
        $path   = $parsed['path'] ?? '';
        return $scheme . $host . $path;
    }

    /**
     * Chuẩn hoá path từ request: bỏ phần tử rỗng, bỏ "public", bỏ query/hash ở segment cuối,
     * và TÁCH locale prefix (nếu có) — trả về [$locale, $cleanSegments].
     *
     * @return array{0: string|null, 1: array}
     */
    public static function cleanRequestPathWithLocale(string $path): array {
        $tmp = explode('/', $path);
        $clean = array_values(array_filter($tmp, fn($s) => !empty($s) && $s !== 'public'));
        if (!empty($clean)) {
            $clean[count($clean)-1] = preg_replace('#([\?|\#]+).*$#imsU', '', end($clean));
        }
        $locale = null;
        if (!empty($clean) && Language::byCode($clean[0])) {
            $locale = array_shift($clean);
        }
        return [$locale, $clean];
    }

    /**
     * Backwards-compat: chỉ trả mảng segments (không tách locale).
     */
    public static function cleanRequestPath(string $path): array {
        [, $clean] = self::cleanRequestPathWithLocale($path);
        return $clean;
    }
}
