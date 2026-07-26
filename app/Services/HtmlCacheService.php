<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use App\Helpers\Url;

/**
 * HtmlCacheService.
 *
 * Mục đích: cung cấp 1 chỗ duy nhất quản lý cache HTML cho mọi trang public
 * (RoutingController, SitemapController, HeaderMainService...).
 *
 * Tính năng:
 *  - getOrRender($cacheKey, callable): lấy file cache nếu hợp lệ, không thì
 *    chạy callable -> lưu cache -> trả về.
 *  - clear($cacheKey)/clearAll(): xoá cache theo key hoặc toàn bộ.
 *  - Hỗ trợ gzip (giảm disk + IO), minify HTML và minify JS/CSS inline (tuỳ
 *    chọn bật ở `config/main.php`).
 *  - Disk có thể là `local` hoặc `gcs` (Google Cloud Storage) cấu hình ở
 *    `config('main.cache.disk')`.
 *  - Cache key có thể bao gồm "namespace" (ví dụ locale) để Phase 1 đa ngôn
 *    ngữ không cần đổi service: chỉ cần đặt key dạng `vi:tour-phu-quoc`.
 *  - Menu (desktop + mobile): `menuMain_{locale}` — `getOrRenderMenu()`, dùng
 *    chung mọi trang cùng ngôn ngữ (xem `headerMainCached.blade.php`).
 *
 * Ví dụ:
 *   $cacheKey = HtmlCacheService::buildKey($slugFull, ['page' => 2]);
 *   $html = app(HtmlCacheService::class)
 *               ->getOrRender($cacheKey, fn() => view('...')->render());
 */
class HtmlCacheService
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    protected string $cacheFolder;
    protected string $extension;
    protected int    $fileTtl;
    protected bool   $useHtmlCache;
    protected bool   $useGzip;
    protected bool   $useHtmlMinify;
    protected bool   $useJsCssMinify;

    /** Root URL chuẩn (APP_URL) đã chuẩn hoá không trailing slash. */
    protected string $canonicalRoot;
    /** Host của APP_URL, dùng để so sánh với request host khi quyết định lưu cache. */
    protected string $canonicalHost;
    /** Thời điểm cuối cùng `public/build/manifest.json` được build (cache-bust assets). */
    protected ?int $assetVersionAt = null;

    public function __construct()
    {
        $this->useHtmlCache   = (bool) env('APP_CACHE_HTML', false);
        $this->cacheFolder    = trim(config('main.cache.folderSave', 'public/caches'), '/');
        $this->extension      = config('main.cache.extension', 'html');
        $this->fileTtl        = (int) config('main.cache.ttl', 2592000);
        $this->useGzip        = (bool) config('main.cache.use_gzip', true);
        $this->useHtmlMinify  = (bool) config('main.cache.use_html_min', false);
        $this->useJsCssMinify = (bool) config('main.cache.use_jscss_min', false);

        $diskName             = config('main.cache.disk', 'local');
        $this->disk           = Storage::disk($diskName);

        $this->canonicalRoot  = rtrim((string) config('app.url'), '/');
        $this->canonicalHost  = (string) (parse_url($this->canonicalRoot, PHP_URL_HOST) ?: '');

        /* Khi `npm run build` chạy, Vite ghi lại `public/build/manifest.json`
         * với hash mới (app.OLDHASH.js -> app.NEWHASH.js). HTML cache cũ
         * vẫn trỏ đến hash cũ -> trình duyệt nhận 404 (text/html) cho
         * <script type="module"> gây "Failed to load module script: ...
         * MIME type of text/html". Lấy mtime của manifest để khi nó đổi
         * thì cache HTML cũ tự động bị coi là stale. */
        $manifest = public_path('build/manifest.json');
        $this->assetVersionAt = is_file($manifest) ? @filemtime($manifest) : null;
    }

    /**
     * Lấy HTML từ cache nếu có, nếu không thì chạy $renderCallback rồi lưu cache.
     *
     * @param string   $cacheKey
     * @param callable $renderCallback Trả về string HTML hoặc null/false để bỏ cache.
     */
    /**
     * @param bool $allowHomepagePersist Chỉ true từ HomeController — trang con không bao giờ ghi home.html.gz
     */
    public function getOrRender(string $cacheKey, callable $renderCallback, bool $allowHomepagePersist = false): ?string
    {
        /* Khi npm run dev đang chạy (file public/hot tồn tại), tắt cache HTML để Blade re-render live mỗi request */
        if (!$this->useHtmlCache || $cacheKey === '' || is_file(public_path('hot'))) {
            return $renderCallback();
        }

        $cachePath = $this->buildCachePath($cacheKey);

        if ($html = $this->getFromDisk($cachePath)) {
            return $this->prepareHtmlForServe($html);
        }

        $html = $renderCallback();
        if (!empty($html) && $this->isRequestHostCanonical()) {
            if ($this->shouldPersistCacheKey($cacheKey, $allowHomepagePersist)) {
                $this->saveToDisk($cachePath, $html);
            } elseif (self::isHomepageCacheKey($cacheKey) && config('app.debug')) {
                Log::debug('HtmlCache: skipped writing homepage cache file on non-home route', [
                    'cache_key' => $cacheKey,
                    'path'      => request()?->path(),
                ]);
            }
        }

        return !empty($html) ? $this->prepareHtmlForServe($html) : null;
    }

    /**
     * Cache key trang chủ — chỉ dùng từ HomeController (path `/` hoặc `/en`).
     * File: home.html.gz, en-home.html.gz, ...
     */
    public static function homepageCacheKey(?string $locale = null): string
    {
        $locale = strtolower(trim((string) ($locale ?? app()->getLocale())));
        $default = strtolower((string) config('language.default_code', 'vi'));
        if ($locale === '' || $locale === $default) {
            return 'home';
        }

        return $locale . '-home';
    }

    public static function isHomepageCacheKey(string $cacheKey): bool
    {
        $key = ltrim($cacheKey, '/');

        return $key === 'home' || (bool) preg_match('#^[a-z]{2}(?:-[a-z]{2})?-home$#', $key);
    }

    /**
     * Chỉ ghi file `home` / `{locale}-home` khi controller bật $allowHomepagePersist
     * VÀ request thật sự là trang chủ (path `/` hoặc `/en`).
     */
    private function shouldPersistCacheKey(string $cacheKey, bool $allowHomepagePersist): bool
    {
        if (!self::isHomepageCacheKey($cacheKey)) {
            return true;
        }

        return $allowHomepagePersist && $this->isCurrentRequestHomepage();
    }

    /**
     * Cache key từ slug_full SEO đã resolve (ổn định hơn parse lại request->path()).
     */
    public static function buildKeyFromSlugFull(string $slugFull, ?string $locale, array $params = []): string
    {
        $segments = array_values(array_filter(
            explode('/', trim($slugFull, '/')),
            fn ($s) => $s !== ''
        ));

        if (empty($segments)) {
            return self::homepageCacheKey($locale ?: app()->getLocale());
        }

        return self::buildKeyFromSegments($locale, $segments, $params);
    }

    /**
     * @param object $itemSeo Model Seo (có thể có relation translation)
     */
    public static function resolveSlugFullForCache(object $itemSeo): string
    {
        if (method_exists($itemSeo, 'relationLoaded')
            && $itemSeo->relationLoaded('translation')
            && !empty($itemSeo->translation?->slug_full)) {
            return trim((string) $itemSeo->translation->slug_full, '/');
        }

        return trim((string) ($itemSeo->slug_full ?? ''), '/');
    }

    private function isCurrentRequestHomepage(): bool
    {
        try {
            $request = request();
            if (!$request) {
                return false;
            }

            $path = trim(rawurldecode($request->path()), '/');
            if ($path === '') {
                return true;
            }

            $localeParam = $request->route('locale');
            if ($localeParam && strcasecmp($path, (string) $localeParam) === 0) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Cache key menu chính (desktop + mobile) theo ngôn ngữ.
     * File: public/caches/menuMain_vi.html.gz, menuMain_en.html.gz, ...
     */
    public static function menuCacheKey(?string $locale = null): string
    {
        $locale = strtolower(trim((string) ($locale ?? app()->getLocale())));
        $default = strtolower((string) config('language.default_code', 'vi'));
        if ($locale === '') {
            $locale = $default;
        }

        $prefix = (string) config('main.cache.menu_key_prefix', 'menuMain');

        return $prefix . '_' . $locale;
    }

    /**
     * Lấy HTML menu (headerMain: desktop + mobile bar + nav-mobile) từ cache theo locale.
     * Mọi trang cùng ngôn ngữ tái sử dụng một file — không render lại mega menu DB.
     */
    public function getOrRenderMenu(?string $locale, callable $renderCallback): string
    {
        $locale = strtolower(trim((string) ($locale ?? app()->getLocale())));
        if ($locale === '') {
            $locale = strtolower((string) config('language.default_code', 'vi'));
        }

        $render = fn () => $this->renderHtmlWithLocale($locale, $renderCallback);

        if (!$this->useHtmlCache) {
            return $render() ?? '';
        }

        $cacheKey  = self::menuCacheKey($locale);
        $cachePath = $this->buildCachePath($cacheKey);

        if ($html = $this->getFromDisk($cachePath)) {
            return $this->prepareHtmlForServe($html);
        }

        $html = $render();
        if (!empty($html) && $this->isRequestHostCanonical()) {
            $this->saveToDisk($cachePath, $html);
        }

        return $html ?? '';
    }

    public function clearMenu(?string $locale = null): void
    {
        if ($locale !== null && $locale !== '') {
            $this->clear(self::menuCacheKey($locale));

            return;
        }

        foreach (Language::active() as $lang) {
            $code = strtolower((string) ($lang->code ?? ''));
            if ($code !== '') {
                $this->clear(self::menuCacheKey($code));
            }
        }
        $this->clear(self::menuCacheKey((string) config('language.default_code', 'vi')));
    }

    /**
     * Render view/callback với locale cố định (ghi cache menu đúng ngôn ngữ).
     */
    private function renderHtmlWithLocale(string $locale, callable $renderCallback): ?string
    {
        $previousLocale = app()->getLocale();
        $lang = Language::byCode($locale);

        try {
            app()->setLocale($locale);
            view()->share('currentLocale', $locale);
            view()->share('currentLanguage', $lang);

            return $renderCallback();
        } finally {
            app()->setLocale($previousLocale);
            view()->share('currentLocale', $previousLocale);
            view()->share('currentLanguage', Language::byCode($previousLocale));
        }
    }

    /**
     * Kiểm tra request hiện tại có đến từ canonical host (APP_URL) hay không.
     *
     * Mục đích: ngăn cache HTML bị "đầu độc" khi request đi vào server bằng
     * IP/host khác (bot scan, uptime monitor, origin pull của CDN, curl từ
     * chính server bằng IP...). Trong những trường hợp đó, kết quả render
     * vẫn được trả về cho người gọi nhưng KHÔNG ghi xuống đĩa.
     *
     * Mặc định True nếu chưa cấu hình APP_URL hoặc không có request (CLI/queue).
     */
    private function isRequestHostCanonical(): bool
    {
        if ($this->canonicalHost === '') return true;

        try {
            $request = request();
            if (!$request) return true;
            $reqHost = strtolower((string) $request->getHost());
            return $reqHost === strtolower($this->canonicalHost);
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function clear(string $cacheKey): void
    {
        $this->deleteFromDisk($this->buildCachePath($cacheKey));
    }

    /**
     * Xóa toàn bộ file cache trong thư mục cấu hình.
     *
     * @return int Số file đã xoá
     */
    public function clearAll(): int
    {
        try {
            $files = $this->disk->allFiles($this->cacheFolder);
            if (empty($files)) return 0;
            $this->disk->delete($files);
            return count($files);
        } catch (\Throwable $e) {
            Log::error('HtmlCacheService clearAll failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Build cache key TỪ FULL URL request path.
     *
     * Quy ước (đồng bộ với docs/currency.md mục Cache HTML):
     *   - Default locale (vi) không có prefix trong URL -> key cũng không có prefix.
     *     `/tour-phu-quoc`                -> `tour-phu-quoc`
     *     `/tour-phu-quoc/option-7-days`  -> `tour-phu-quoc-option-7-days`
     *   - Locale khác (en, cn, ja, ...) prefix `{locale}-` vào key:
     *     `/en/phu-quoc-tours`            -> `en-phu-quoc-tours`
     *     `/cn/tour-phu-quoc` (chưa dịch) -> `cn-tour-phu-quoc`
     *   - Trang chủ: `homepageCacheKey()` (chỉ HomeController, path `/` hoặc `/en`):
     *     `/` (default locale)            -> `home`
     *     `/en`                           -> `en-home`
     *     `/cn`                           -> `cn-home`
     *
     * Cách dùng (mọi controller cache HTML đều gọi method này, KHÔNG tự build):
     *   $cacheKey = HtmlCacheService::buildKeyFromRequest($request, [
     *       'page'   => $request->query('page'),
     *       'search' => $request->query('search'),
     *   ]);
     *   $cacheKey .= '-' . strtolower(current_currency());
     */
    public static function buildKeyFromRequest(Request $request, array $params = []): string
    {
        $path = rawurldecode($request->path());
        [$locale, $segments] = Url::cleanRequestPathWithLocale($path);

        if (empty($segments)) {
            return self::homepageCacheKey($locale ?: app()->getLocale());
        }

        return self::buildKeyFromSegments($locale, $segments, $params);
    }

    /**
     * Build cache key từ (locale, segments). Tách riêng để test/dùng nội bộ.
     *
     * @param string|null $locale  null hoặc chuỗi rỗng = không có locale prefix trong URL
     *                             (mặc nhiên là default locale).
     * @param array       $segments  Segment đã strip locale; không được rỗng
     *                               (trang chủ dùng homepageCacheKey()).
     * @param array       $params    Query/page/search/sort... sẽ ksort + append.
     */
    public static function buildKeyFromSegments(?string $locale, array $segments, array $params = []): string
    {
        $segments = array_values(array_filter(
            array_map(fn($s) => trim((string) $s), $segments),
            fn($s) => $s !== ''
        ));

        if (empty($segments)) {
            throw new \InvalidArgumentException(
                'buildKeyFromSegments() requires non-empty segments; use homepageCacheKey() for homepage.'
            );
        }

        $defaultCode = strtolower((string) config('language.default_code', 'vi'));
        $localeCode  = $locale ? strtolower(trim((string) $locale, '/')) : '';
        $isDefault   = ($localeCode === '' || $localeCode === $defaultCode);

        $slug = implode('-', $segments);
        $base = $isDefault ? $slug : $localeCode . '-' . $slug;

        if (!empty($params)) {
            ksort($params);
            $parts = [];
            foreach ($params as $k => $v) {
                if ($v === null || $v === '' || $v === []) continue;
                if (is_array($v)) {
                    ksort($v);
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $parts[] = $k . '-' . preg_replace('/[^a-zA-Z0-9_\-]/u', '_', (string) $v);
            }
            if (!empty($parts)) $base .= '-' . implode('-', $parts);
        }

        return $base;
    }

    /**
     * @deprecated Dùng buildKeyFromRequest() để tự động kế thừa locale prefix
     *             từ URL. Hàm này còn lại chỉ để giữ tương thích với code cũ
     *             gọi `HtmlCacheService::buildKey($slugFull, $params)`.
     */
    public static function buildKey(string $slugFull, array $params = [], ?string $namespace = null): string
    {
        $base = self::buildKeyFromSlugFull($slugFull, null, $params);
        return $namespace ? trim($namespace, '/') . '/' . $base : $base;
    }

    // ---------------- Internal ----------------

    private function buildCachePath(string $cacheKey): string
    {
        $clean = ltrim($cacheKey, '/');
        return $this->cacheFolder . '/' . $clean . '.' . $this->extension;
    }

    private function saveToDisk(string $path, string $content): void
    {
        try {
            $dir = \dirname($path);
            if (!$this->disk->exists($dir)) {
                $this->disk->makeDirectory($dir);
            }

            $content = $this->prepareHtmlForCache($content);

            if ($this->useJsCssMinify) {
                $content = $this->minifyJsCssInline($content);
            }
            if ($this->useHtmlMinify && class_exists(\voku\helper\HtmlMin::class)) {
                $content = (new \voku\helper\HtmlMin())->minify($content);
            }

            if ($this->useGzip) {
                $this->disk->put($path . '.gz', gzencode($content, 6));
            } else {
                $this->disk->put($path, $content);
            }
        } catch (\Throwable $e) {
            Log::warning('HtmlCacheService saveToDisk failed: ' . $e->getMessage(), ['path' => $path]);
        }
    }

    /**
     * Quét nội dung HTML và thay các root URL có host là ĐỊA CHỈ IP (IPv4
     * hoặc IPv6) bằng canonical root từ APP_URL.
     *
     * Bug điển hình: request đi vào server bằng IP (vd https://180.93.43.78/)
     * khiến mọi asset() trong view render thành URL có host là IP. Hàm này
     * sửa lại trước khi ghi cache.
     *
     * Lưu ý: chỉ replace khi host là IP để KHÔNG động vào các URL bên thứ ba
     * (cdnjs, code.jquery.com, jsdelivr, hitour.vn, ...).
     */
    private function scrubNonCanonicalHosts(string $html): string
    {
        if ($this->canonicalRoot === '') return $html;

        // IPv4: scheme://1.2.3.4(:port)?  trước '/' hoặc kí tự kết thúc attribute
        $patternIpv4 = '#\bhttps?://(?:\d{1,3}\.){3}\d{1,3}(?::\d+)?(?=/|["\'\s>)])#u';
        // IPv6: scheme://[::1](:port)?
        $patternIpv6 = '#\bhttps?://\[[0-9a-fA-F:]+\](?::\d+)?(?=/|["\'\s>)])#u';

        $html = preg_replace($patternIpv4, $this->canonicalRoot, $html) ?? $html;
        $html = preg_replace($patternIpv6, $this->canonicalRoot, $html) ?? $html;
        return $html;
    }

    /**
     * Chuẩn hoá HTML trước khi ghi cache (và khi đọc cache cũ).
     * - Vite dev (`public/hot` → :5173) không được lưu vào cache.
     * - Asset `/build/*` luôn là path gốc tuyệt đối.
     */
    private function prepareHtmlForCache(string $html): string
    {
        $html = $this->scrubNonCanonicalHosts($html);
        $html = $this->rewriteViteDevUrlsToBuildAssets($html);

        return $html;
    }

    private function prepareHtmlForServe(string $html): string
    {
        $html = $this->prepareHtmlForCache($html);

        return $this->refreshAssetBustQuery($html);
    }

    /**
     * Cập nhật ?v= trên URL /build/ mỗi lần serve (kể cả HTML cache)
     * để CSS/JS không bị giữ bản cũ khi ASSET_CACHE_BUST=true.
     */
    private function refreshAssetBustQuery(string $html): string
    {
        if (!config('app.asset_cache_bust') || !function_exists('asset_bust_version')) {
            return $html;
        }

        $version = asset_bust_version();
        if ($version === '') {
            return $html;
        }

        $v = rawurlencode($version);

        return preg_replace_callback(
            '#(\s(?:href|src)=["\'])([^"\']*?/build/[^"\'?\s]+)(?:\?[^"\']*)?(["\'])#i',
            static function (array $m) use ($v): string {
                return $m[1] . $m[2] . '?v=' . $v . $m[3];
            },
            $html
        ) ?? $html;
    }

    /**
     * Khi `public/hot` tồn tại (hoặc HTML cache cũ còn URL source/dev),
     * @vite render URL dev/source. Ghi/serve cache sẽ vỡ CSS/JS — thay bằng
     * tag production từ manifest đúng entry (superdong vs main).
     */
    private function rewriteViteDevUrlsToBuildAssets(string $html): string
    {
        /* Nếu file public/hot đang tồn tại (npm run dev đang bật), giữ nguyên script @vite/client để trình duyệt kết nối Vite HMR Live Reload! */
        if (is_file(public_path('hot'))) {
            return $this->ensureRootAbsoluteBuildAssets($html);
        }

        $hasViteDev = (bool) preg_match('#@vite/client|://[^"\'>\s]*:5173/#', $html);
        $hasSourceAssets = (bool) preg_match(
            '#\s(?:href|src)=["\'][^"\']*/resources/(?:sources|js)/[^"\']+\.(?:scss|css|js)(?:\?[^"\']*)?["\']#i',
            $html
        );
        $hasRelativeBuild = (bool) preg_match('#\s(?:href|src)=["\']build/#i', $html);
        $isSuperdong = (bool) preg_match(
            '#\bsd-home-v2\b|superdong\.scss|/js/superdong\.js|/build/assets/superdong|/build/assets/home-v2#i',
            $html
        );
        $hasWrongMainCssOnSuperdong = $isSuperdong
            && (bool) preg_match('#/build/assets/style(?:\d*)\.css#i', $html)
            && !preg_match('#/build/assets/superdong(?:\d*)\.css#i', $html);

        if (!$hasViteDev && !$hasSourceAssets && !$hasRelativeBuild && !$hasWrongMainCssOnSuperdong) {
            return $this->ensureRootAbsoluteBuildAssets($html);
        }

        $entrypoints = $isSuperdong
            ? ['resources/sources/superdong.scss', 'resources/js/superdong.js']
            : ['resources/sources/main/style.scss'];

        // Gỡ Vite client / HMR / source SCSS-JS (kể cả khi hot trỏ APP_URL không có :5173).
        $html = preg_replace('#<script\b[^>]*(?:@vite/client|:5173/)[^>]*>\s*</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<link\b[^>]*:5173/[^>]*\/?>#i', '', $html) ?? $html;
        $html = preg_replace('#<script\b[^>]*:5173/[^>]*>\s*</script>#is', '', $html) ?? $html;
        $html = preg_replace(
            '#<link\b[^>]*(?:href=["\'][^"\']*/resources/sources/[^"\']+\.(?:scss|css)(?:\?[^"\']*)?["\'])[^>]*\/?>#i',
            '',
            $html
        ) ?? $html;
        $html = preg_replace(
            '#<script\b[^>]*(?:src=["\'][^"\']*/resources/js/[^"\']+\.js(?:\?[^"\']*)?["\'])[^>]*>\s*</script>#is',
            '',
            $html
        ) ?? $html;

        if ($hasWrongMainCssOnSuperdong) {
            $html = preg_replace(
                '#<link\b[^>]*(?:href=["\'][^"\']*/build/assets/style(?:\d*)\.css(?:\?[^"\']*)?["\'])[^>]*\/?>#i',
                '',
                $html
            ) ?? $html;
            $html = preg_replace(
                '#<link\b[^>]*(?:href=["\'][^"\']*/build/assets/style(?:\d*)\.css(?:\?[^"\']*)?["\'])[^>]*rel=["\']preload["\'][^>]*\/?>#i',
                '',
                $html
            ) ?? $html;
        }

        $needsCss = !preg_match('#/build/assets/(?:superdong|style)(?:\d*)\.css#i', $html);
        $needsJs = $isSuperdong && !preg_match('#/build/assets/(?:superdong|home-v2)(?:\d*)\.js#i', $html);

        if ($needsCss || $needsJs || $hasWrongMainCssOnSuperdong) {
            $productionTags = $this->renderProductionViteTags($entrypoints);
            if ($productionTags !== '') {
                if (preg_match('#<!-- BEGIN: Custom CSS-->#i', $html)) {
                    $html = preg_replace(
                        '#(<!-- BEGIN: Custom CSS-->\s*)#i',
                        '$1' . $productionTags . "\n",
                        $html,
                        1
                    ) ?? $html;
                } else {
                    $html = preg_replace('#</head>#i', $productionTags . "\n</head>", $html, 1) ?? $html;
                }
            }
        }

        return $this->ensureRootAbsoluteBuildAssets($html);
    }

    /**
     * Render @vite tags ở chế độ production (bỏ qua file `public/hot` tạm thời).
     *
     * @param list<string> $entrypoints
     */
    private function renderProductionViteTags(array $entrypoints): string
    {
        $hotFile = public_path('hot');
        $hotBackup = $hotFile . '.html-cache-off';
        $renamed = false;

        if (is_file($hotFile)) {
            if (is_file($hotBackup)) {
                @unlink($hotBackup);
            }
            if (@rename($hotFile, $hotBackup)) {
                $renamed = true;
            }
        }

        try {
            return Vite::useBuildDirectory('build')
                ->withEntryPoints($entrypoints)
                ->toHtml();
        } catch (\Throwable $e) {
            Log::warning('HtmlCacheService renderProductionViteTags: ' . $e->getMessage());

            return $this->fallbackViteTagsFromManifest($entrypoints);
        } finally {
            if ($renamed && is_file($hotBackup)) {
                @rename($hotBackup, $hotFile);
            }
        }
    }

    /**
     * @param list<string> $entrypoints
     */
    private function fallbackViteTagsFromManifest(array $entrypoints): string
    {
        $manifest = $this->readBuildManifest();
        if ($manifest === null) {
            return '';
        }

        $prefix = rtrim($this->canonicalRoot, '/');
        $tags   = '';

        foreach ($entrypoints as $entry) {
            if (empty($manifest[$entry]['file'])) {
                continue;
            }
            $file = ltrim((string) $manifest[$entry]['file'], '/');
            $href = asset_bust_url($prefix . '/build/' . $file);
            $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
            if (str_ends_with(strtolower($file), '.css')) {
                $tags .= '<link rel="stylesheet" href="' . $safe . '" />' . "\n";
            } else {
                $tags .= '<script type="module" src="' . $safe . '"></script>' . "\n";
            }
            foreach ($manifest[$entry]['css'] ?? [] as $cssFile) {
                $cssHref = asset_bust_url($prefix . '/build/' . ltrim((string) $cssFile, '/'));
                $tags .= '<link rel="stylesheet" href="' . htmlspecialchars($cssHref, ENT_QUOTES, 'UTF-8') . '" />' . "\n";
            }
        }

        return $tags;
    }

    /** @return array<string, array<string, mixed>>|null */
    private function readBuildManifest(): ?array
    {
        $path = public_path('build/manifest.json');
        if (!is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    /** href="build/..." trên URL sâu (/tour-xxx) → resolve sai → vỡ layout. */
    private function ensureRootAbsoluteBuildAssets(string $html): string
    {
        return preg_replace(
            '#(\s(?:href|src)=["\'])(?!https?://|/|data:|//)(build/)#i',
            '$1/$2',
            $html
        ) ?? $html;
    }

    private function getFromDisk(string $path): ?string
    {
        try {
            // Ưu tiên file gzip nếu có
            if ($this->useGzip || $this->disk->exists($path . '.gz')) {
                $gzPath = $path . '.gz';
                if (!$this->disk->exists($gzPath)) return null;
                if (!$this->isFresh($gzPath)) return null;
                $compressed = $this->disk->get($gzPath);
                return gzdecode($compressed) ?: null;
            }

            if (!$this->disk->exists($path)) return null;
            if (!$this->isFresh($path)) return null;
            return $this->disk->get($path);
        } catch (\Throwable $e) {
            Log::warning('HtmlCacheService getFromDisk failed: ' . $e->getMessage(), ['path' => $path]);
            return null;
        }
    }

    private function deleteFromDisk(string $path): void
    {
        try {
            foreach ([$path, $path . '.gz'] as $p) {
                if ($this->disk->exists($p)) $this->disk->delete($p);
            }
        } catch (\Throwable $e) {
            Log::warning('HtmlCacheService deleteFromDisk failed: ' . $e->getMessage(), ['path' => $path]);
        }
    }

    private function isFresh(string $path): bool
    {
        try {
            $lastModified = $this->disk->lastModified($path);
            if ($lastModified === false) return false;
            /* Stale ngay khi rebuild assets (manifest mtime mới hơn cache). */
            if ($this->assetVersionAt && $lastModified < $this->assetVersionAt) return false;
            return (time() - $lastModified) <= $this->fileTtl;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function minifyJsCssInline(string $html): string
    {
        if (!class_exists(\MatthiasMullie\Minify\CSS::class)) return $html;

        // Minify <style>...</style> inline
        $html = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/is', function ($matches) {
            try {
                $minifier = new \MatthiasMullie\Minify\CSS($matches[1]);
                return '<style>' . $minifier->minify() . '</style>';
            } catch (\Throwable $e) {
                return $matches[0];
            }
        }, $html);

        // Minify <script>...</script> inline (bỏ qua khi có src hoặc là JSON-LD)
        $html = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/is', function ($matches) {
            if (preg_match('/\bsrc\s*=/i', $matches[0]) ||
                preg_match('/\btype\s*=\s*[\'"]application\/ld\+json[\'"]/i', $matches[0])) {
                return $matches[0];
            }
            try {
                $minifier = new \MatthiasMullie\Minify\JS($matches[1]);
                return '<script>' . $minifier->minify() . '</script>';
            } catch (\Throwable $e) {
                return $matches[0];
            }
        }, $html);

        return $html;
    }
}
