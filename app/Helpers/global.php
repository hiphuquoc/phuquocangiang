<?php

/**
 * Global helpers cho hệ thống đa ngôn ngữ.
 *
 * Được nạp tự động qua composer.json `autoload.files`.
 */

use App\Helpers\SeoAlternates;
use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoTranslation;

if (!function_exists('current_locale')) {
    /**
     * Locale hiện tại.
     */
    function current_locale(): string {
        return app()->getLocale() ?: config('language.default_code', 'vi');
    }
}

if (!function_exists('t')) {
    /**
     * Dịch chuỗi UI (text cứng) theo locale hiện tại từ `config/lang_ui.php`.
     *
     * Cách dùng:
     *   {{ t('home') }}
     *   {{ t('tour_list_title', ['name' => $item->display_name]) }}
     *   {{ t('footer_call_brand', ['brand' => config('company.sortname')]) }}
     *
     * Lookup chain:
     *   1. config('lang_ui.<current_locale>.<key>')
     *   2. config('lang_ui.<fallback_code>.<key>')   (mặc định fallback = vi)
     *   3. Chính `$key` (để dev phát hiện key thiếu trong DOM).
     *
     * Placeholder dạng `:name`, `:brand`, `:year`… được thay thế bằng
     * giá trị tương ứng trong `$replace`. Nếu giá trị là số/array →
     * cast sang string an toàn.
     */
    function t(string $key, array $replace = []): string {
        $locale   = current_locale();
        $fallback = (string) config('language.fallback_code', 'vi');

        $value = config('lang_ui.' . $locale . '.' . $key);
        if ($value === null || $value === '') {
            $value = config('lang_ui.' . $fallback . '.' . $key);
        }
        if ($value === null || $value === '') {
            return $key;
        }
        if (!is_string($value)) {
            $value = (string) $value;
        }
        if (!empty($replace)) {
            $search  = [];
            $replaceVals = [];
            foreach ($replace as $k => $v) {
                if (!is_scalar($v) && !($v instanceof \Stringable) && $v !== null) {
                    $v = (string) json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $search[]      = ':' . $k;
                $replaceVals[] = (string) ($v ?? '');
            }
            $value = str_replace($search, $replaceVals, $value);
        }
        return $value;
    }
}

if (!function_exists('te')) {
    /**
     * Phiên bản HTML-escape của `t()` — tiện khi cần dùng trong attribute mà
     * không muốn đặt `{{ t() }}` (Blade tự escape) hoặc khi gọi từ PHP raw.
     */
    function te(string $key, array $replace = []): string {
        return e(t($key, $replace));
    }
}

if (!function_exists('t_raw')) {
    /**
     * Trả về raw (cho phép HTML) — dùng khi chuỗi dịch có chứa <strong>, <a>...
     * Trong Blade dùng `{!! t_raw('key', [...]) !!}`.
     */
    function t_raw(string $key, array $replace = []): string {
        return t($key, $replace);
    }
}

if (!function_exists('current_language')) {
    /**
     * Object Language của locale hiện tại (cached).
     */
    function current_language(): ?Language {
        return Language::byCode(current_locale());
    }
}

if (!function_exists('default_locale')) {
    function default_locale(): string {
        return config('language.default_code', 'vi');
    }
}

if (!function_exists('is_default_locale')) {
    function is_default_locale(): bool {
        return current_locale() === default_locale();
    }
}

if (!function_exists('seo_url')) {
    /**
     * Sinh URL công khai cho 1 entity, có locale prefix tự động.
     *
     * @param mixed       $entityOrSlug   model có ->seo, Seo, hoặc string slug_full
     * @param string|null $locale         override locale; default: hiện tại
     * @return string                     URL đầy đủ (relative — không đẫm domain)
     */
    function seo_url($entityOrSlug, ?string $locale = null): string {
        $locale ??= current_locale();
        $lang = Language::byCode($locale);
        $prefix = $lang && $lang->is_default ? '' : '/' . $locale;

        // Nếu là string -> coi là slug_full
        if (is_string($entityOrSlug)) {
            return $prefix . '/' . ltrim($entityOrSlug, '/');
        }

        // Model có seo_id / ->seo; hoặc truyền thẳng Seo model
        $seoId = null;
        if (is_object($entityOrSlug)) {
            if ($entityOrSlug instanceof Seo) {
                $seoId = (int) $entityOrSlug->id;
            } else {
                $seoId = $entityOrSlug->seo_id ?? optional($entityOrSlug->seo ?? null)->id;
            }
        }
        if (!$seoId) return $prefix . '/';

        $t = SeoTranslation::where('seo_id', $seoId)
                            ->where('language_id', $lang ? $lang->id : 0)
                            ->first();
        if ($t && !empty($t->slug_full)) {
            return $prefix . '/' . ltrim($t->slug_full, '/');
        }

        // fallback: slug_full mặc định trên seo
        $seo = is_object($entityOrSlug) ? ($entityOrSlug->seo ?? null) : null;
        if ($seo && !empty($seo->slug_full)) {
            return $prefix . '/' . ltrim($seo->slug_full, '/');
        }
        return $prefix . '/';
    }
}

if (!function_exists('seo_url_full')) {
    /**
     * URL đầy đủ kèm domain.
     */
    function seo_url_full($entityOrSlug, ?string $locale = null): string {
        return rtrim(env('APP_URL'), '/') . seo_url($entityOrSlug, $locale);
    }
}

if (!function_exists('home_url')) {
    /**
     * Trang chủ theo locale hiện tại: / hoặc /en/ …
     */
    function home_url(): string {
        return seo_url('');
    }
}

if (!function_exists('seo_alternates')) {
    function seo_alternates($entityOrSeo) {
        return SeoAlternates::for($entityOrSeo);
    }
}

if (!function_exists('locale_url')) {
    /**
     * Chuyển URL hiện tại sang locale khác (dùng trong language switcher).
     *
     * Thứ tự resolve:
     *   1. Nếu có entity + có SeoTranslation cho target locale → dùng URL chuẩn
     *      `SeoAlternates::urlFor()` (đảm bảo translated slug đúng).
     *   2. Fallback: tách locale prefix khỏi `request()->path()` rồi gắn lại
     *      prefix của target locale → đảm bảo URL luôn absolute, leading slash,
     *      không bị browser resolve thành "current/target" (vd `/ru/en`).
     *
     * Trả về path nội bộ (vd `/en/du-lich-phu-quoc`, `/ru/`) — phù hợp dùng
     * trong attribute `href`.
     */
    function locale_url(string $targetLocale, $entityOrSeo = null, ?string $fallback = null): string {
        if ($entityOrSeo) {
            $url = SeoAlternates::urlFor($entityOrSeo, $targetLocale);
            if (!empty($url)) return $url;
            // không có translation cho target → fall through path-based
        }

        // Tách locale prefix khỏi request path hiện tại
        $path = '/' . ltrim(request()->path(), '/');
        $segs = array_values(array_filter(explode('/', $path), fn($s) => $s !== ''));
        if (!empty($segs)) {
            $first = Language::byCode($segs[0]);
            if ($first) {
                array_shift($segs);
            }
        }

        $defaultCode = default_locale();
        $targetLang  = Language::byCode($targetLocale);
        $isTargetDefault = $targetLang ? (bool) $targetLang->is_default : ($targetLocale === $defaultCode);
        $newPrefix   = $isTargetDefault ? '' : '/' . $targetLocale;
        $rest        = implode('/', $segs);

        if ($rest === '') {
            // Trang chủ của target locale: '/' cho default, '/en' cho non-default
            return $newPrefix === '' ? '/' : $newPrefix;
        }
        return $newPrefix . '/' . $rest;
    }
}

if (!function_exists('module_enabled')) {
    function module_enabled(string $module): bool
    {
        return (bool) config('modules.enabled.' . $module, false);
    }
}

if (!function_exists('seo_type_enabled')) {
    function seo_type_enabled(string $seoType): bool
    {
        $module = config('modules.seo_type_map.' . $seoType);
        if ($module === null) {
            return true;
        }

        return module_enabled($module);
    }
}

if (!function_exists('fragment_type_enabled')) {
    function fragment_type_enabled(string $fragmentType): bool
    {
        $module = config('modules.fragment_type_map.' . $fragmentType);
        if ($module === null) {
            return true;
        }

        return module_enabled($module);
    }
}

if (!function_exists('single_island_mode')) {
    function single_island_mode(): bool
    {
        return (bool) config('modules.single_island', false);
    }
}

if (!function_exists('island_tour_location_id')) {
    function island_tour_location_id(): int
    {
        return (int) config('island.tour_location_id', 0);
    }
}

if (!function_exists('island_context')) {
    function island_context(): \App\Services\Island\IslandContextService
    {
        return app(\App\Services\Island\IslandContextService::class);
    }
}

if (!function_exists('island_name')) {
    function island_name(): string
    {
        return island_context()->name();
    }
}

if (!function_exists('island_nav')) {
    /**
     * @return array<string, string>
     */
    function island_nav(): array
    {
        return app(\App\Services\Island\IslandNavigationService::class)->links();
    }
}

if (!function_exists('seo_content_for_admin')) {
    function seo_content_for_admin(?\App\Models\Seo $seo, ?string $seoType = null, ?string $locale = null): string
    {
        if (!$seo) {
            return '';
        }

        return app(\App\Services\SeoContentService::class)->loadForAdmin($seo, $seoType, $locale);
    }
}

if (!function_exists('media_url')) {
    /**
     * URL hiển thị ảnh — hỗ trợ legacy /storage/... và path GCS (media/uploads/…).
     */
    function media_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage') || str_starts_with($path, '/images')) {
            return $path;
        }

        /** @var \App\Services\Media\GcsMediaStorageService $storage */
        $storage = app(\App\Services\Media\GcsMediaStorageService::class);

        if ($storage->isCloudPath($path) && \Illuminate\Support\Facades\Route::has('media.gcs')) {
            return route('media.gcs', ['path' => $path]);
        }

        return $path;
    }
}

if (!function_exists('media_variant_path')) {
    function media_variant_path(?string $path, string $variant = 'original'): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return app(\App\Services\Media\GcsMediaStorageService::class)
            ->resolveVariantPath($path, $variant);
    }
}

if (!function_exists('media_variant_url')) {
    function media_variant_url(?string $path, string $variant = 'original'): ?string
    {
        $resolved = media_variant_path($path, $variant);

        return $resolved !== null ? media_url($resolved) : null;
    }
}

if (!function_exists('media_absolute_url')) {
    function media_absolute_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $resolved = media_url($path) ?? $path;

        if (str_starts_with($resolved, 'http://') || str_starts_with($resolved, 'https://')) {
            return $resolved;
        }

        return url($resolved);
    }
}

if (!function_exists('media_exists')) {
    function media_exists(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        return app(\App\Services\Media\GcsMediaStorageService::class)->exists($path);
    }
}

if (!function_exists('media_info')) {
    /**
     * Metadata ảnh (kích thước, extension, dung lượng KB) từ path GCS hoặc legacy local.
     *
     * @return array{width: int|null, height: int|null, extension: string, size_kb: float, url: string|null}|null
     */
    function media_info(?string $path): ?array
    {
        if ($path === null || $path === '') {
            return null;
        }

        /** @var \App\Services\Media\GcsMediaStorageService $storage */
        $storage = app(\App\Services\Media\GcsMediaStorageService::class);
        $binary = $storage->get($path);

        if ($binary === null) {
            return null;
        }

        $infoPixel = @getimagesizefromstring($binary);

        return [
            'width' => $infoPixel[0] ?? null,
            'height' => $infoPixel[1] ?? null,
            'extension' => pathinfo($path, PATHINFO_EXTENSION) ?: 'webp',
            'size_kb' => round(strlen($binary) / 1024, 2),
            'url' => media_url($path),
        ];
    }
}

if (!function_exists('booking_route')) {
    /**
     * Route booking có/không prefix locale (vi: /tourBooking/form, en: /en/tourBooking/form).
     *
     * @param string $name  Phần sau "main." — vd: tourBooking.form, shipBooking.confirm
     */
    function booking_route(string $name, array $parameters = [], bool $absolute = true): string {
        $locale = current_locale();
        $lang   = Language::byCode($locale);
        $isDefault = $lang ? (bool) $lang->is_default : ($locale === default_locale());

        if (!$isDefault) {
            $routeName = 'main.' . $name . '.locale';
            $parameters = array_merge(['locale' => $locale], $parameters);
        } else {
            $routeName = 'main.' . $name;
        }

        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName, $parameters, $absolute);
        }

        $prefix = $isDefault ? '' : '/' . $locale;
        $path   = str_replace('.', '/', $name);

        return $prefix . '/' . $path . (empty($parameters) ? '' : '?' . http_build_query($parameters));
    }
}
