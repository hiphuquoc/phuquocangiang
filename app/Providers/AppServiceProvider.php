<?php

namespace App\Providers;

use App\Http\Controllers\AdminTranslationController;
use App\Helpers\SeoAlternates;
use App\Models\Language;
use App\Models\Seo;
use App\Models\SeoContentTranslation;
use App\Models\SeoTranslation;
use App\Services\EntityTranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Helpers global cho hệ thống đa tiền tệ.
        // `composer.json -> autoload.files` đã include file này, nhưng ta
        // require_once ở đây như fallback an toàn cho trường hợp ai đó deploy
        // mà chưa kịp chạy `composer dump-autoload` — tránh fatal error.
        // `if (!function_exists(...))` guards bên trong giúp idempotent.
        $currencyHelpers = base_path('app/Helpers/currency.php');
        if (is_file($currencyHelpers)) {
            require_once $currencyHelpers;
        }

        // CurrencyManager: 1 instance/request — chia sẻ giữa middleware,
        // controller, view, helpers để cùng trạng thái currency hiện tại.
        $this->app->singleton(\App\Services\CurrencyManager::class);
        $this->app->singleton(\App\Services\Island\IslandContextService::class);
        $this->app->singleton(\App\Services\Island\IslandNavigationService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * Đặt các hook sau:
     *  0. forceCanonicalUrl → ép asset()/url()/route() luôn sinh URL theo
     *                          APP_URL bất kể Host header request đến,
     *                          tránh cache HTML bị "đầu độc" bởi request
     *                          gọi thẳng IP server.
     *  1. Seo::saved   → (a) ghi nhớ seo_id vừa được lưu (cho CREATE flow);
     *                    (b) auto-sync seo_translations cho DEFAULT locale với
     *                        các field title / description / seo_title / slug vừa nhập ở form chính.
     *                        Tránh stale khi admin sửa entity bằng form gốc.
     *  2. terminating  → (legacy) đọc translations[] từ request (nếu form gửi)
     *                    và auto-persist. Hiện tại form admin gốc không gửi
     *                    translations[] nữa (đã chuyển sang trang dịch riêng),
     *                    nhưng giữ hook này như safety net cho code cũ.
     */
    public function boot()
    {
        /* Đồng bộ locale app với language.default_code (vi) — tránh fallback en từ config/app.php */
        app()->setLocale(config('language.default_code', 'vi'));

        $this->registerRouteMiddlewareAliases();

        $this->forceCanonicalUrl();
        $this->bootMultilingualAutoPersist();
        $this->bootDefaultLocaleSeoSync();
        $this->bootSeoContentAdminPersist();
        $this->bootTranslationOriginContext();
        $this->bootSuperdongNavigation();
    }

    private function bootSuperdongNavigation(): void
    {
        View::composer([
            'superdong.chrome.header',
            'superdong.chrome.footer',
            'superdong.chrome.mobile-nav',
            'superdong.chrome.nav',
        ], function ($view): void {
            $navService = app(\App\Services\Island\IslandNavigationService::class);
            $menu = $navService->mainMenu();
            $view->with('islandNav', $menu['links']);
            $view->with('islandMenu', $menu['items']);
            $view->with('islandBlogCategories', $navService->blogCategoryLinks());
        });
    }

    /**
     * Force URL generator dùng APP_URL làm root.
     *
     * Lý do: Laravel mặc định sinh URL tuyệt đối (asset/url/route) dựa trên
     * Host header của request hiện tại. Nếu request đi vào server bằng IP
     * (bot scan, uptime monitor, origin pull của CDN, curl từ chính server...),
     * mọi asset() sẽ render thành https://<IP>/... → khi APP_CACHE_HTML=true,
     * HTML xấu này sẽ được ghi xuống public/caches và phục vụ cho mọi user
     * tiếp theo → vỡ giao diện.
     *
     * Ép root URL & scheme theo APP_URL fix triệt để vấn đề này.
     */
    /**
     * Đăng ký alias middleware trên Router (fallback khi Kernel chưa sync sau upgrade Laravel).
     */
    private function registerRouteMiddlewareAliases(): void
    {
        $aliases = [
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'checkRedirect'  => \App\Http\Middleware\CheckRedirect::class,
            'detectLocale'   => \App\Http\Middleware\DetectLocale::class,
            'detectCurrency' => \App\Http\Middleware\DetectCurrency::class,
            'ai.key'         => \App\Http\Middleware\EnsureAiApiKey::class,
        ];

        foreach ($aliases as $name => $class) {
            Route::aliasMiddleware($name, $class);
        }
    }

    private function forceCanonicalUrl(): void
    {
        $appUrl = (string) config('app.url');
        if ($appUrl === '') return;

        URL::forceRootUrl($appUrl);

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * V3.1: Tự động inject context cho banner switcher trên TRANG GỐC.
     *
     * Khi admin xem trang edit của 1 entity (route admin.<entity>.view với param id),
     * View Composer này sẽ:
     *  - Tra route name → seo.type
     *  - Lookup entity + seo theo id
     *  - Đếm số ngôn ngữ đã có bản dịch (qua seo_translations + seo_content_translations)
     *  - Share biến $translationOriginSeo / $translationOriginEntity / $translationOriginLanguages
     *    / $translationOriginCurrent / $translationOriginStatus vào layout main.blade.php
     *
     * Layout sẽ tự render `admin.snippets.translationOriginBanner` ở đầu trang
     * (khi không phải translation mode).
     */
    private function bootTranslationOriginContext(): void
    {
        View::composer('admin.layouts.main', function ($view) {
            try {
                if (!app()->bound('request')) return;
                $request = app('request');
                $route = $request->route();
                if (!$route) return;
                $name = $route->getName() ?? '';

                // Chỉ áp dụng cho route admin.<entity>.view
                if (!str_ends_with($name, '.view') || !str_starts_with($name, 'admin.')) return;

                // Bỏ qua khi đã ở translation mode (banner khác)
                $shared = $view->getFactory()->getShared();
                if (!empty($shared['translationMode'])) return;

                $type = self::routeNameToSeoType($name);
                if (!$type) return;

                $cfg = config('tablemysql.' . $type);
                $modelClass = $cfg['model'] ?? null;
                if (!$modelClass || !class_exists($modelClass)) return;

                $id = (int) ($request->route('id') ?? $request->get('id') ?? 0);
                if ($id <= 0) return;

                $entity = $modelClass::where('id', $id)->with('seo')->first();
                if (!$entity || empty($entity->seo)) return;

                $seo         = $entity->seo;
                $defaultCode = config('language.default_code', 'vi');
                $allLangs    = Language::active();
                $current     = Language::byCode($defaultCode);
                $previewUrl  = SeoAlternates::urlFor($seo, $defaultCode);
                if (empty($previewUrl) && function_exists('seo_url_full')) {
                    $previewUrl = seo_url_full($entity, $defaultCode);
                }

                // Đếm trạng thái dịch theo language_id
                $statusMap = [];
                if (Schema::hasTable('seo_translations')) {
                    $rows = DB::table('seo_translations')
                        ->where('seo_id', $seo->id)
                        ->whereNotNull('title')
                        ->where('title', '!=', '')
                        ->pluck('language_id')
                        ->all();
                    foreach ($rows as $lid) $statusMap[(int) $lid] = true;
                }

                $view->with([
                    'translationOriginSeo'        => $seo,
                    'translationOriginEntity'     => $entity,
                    'translationOriginLanguages'  => $allLangs,
                    'translationOriginCurrent'    => $current,
                    'translationOriginStatus'     => $statusMap,
                    'translationOriginPreviewUrl' => $previewUrl,
                ]);
            } catch (\Throwable $e) {
                Log::warning('translationOriginContext composer failed: ' . $e->getMessage());
            }
        });
    }

    /**
     * Map route name → seo.type (dùng cho View Composer & các helper khác).
     */
    public static function routeNameToSeoType(string $routeName): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = [
                'admin.tour.view'              => 'tour_info',
                'admin.tourLocation.view'      => 'tour_location',
                'admin.tourContinent.view'     => 'tour_continent',
                'admin.tourCountry.view'       => 'tour_country',
                'admin.tourPartner.view'       => 'tour_partner',
                'admin.tourDeparture.view'     => 'tour_departure',
                'admin.tourInfoForeign.view'   => 'tour_info_foreign',
                'admin.ship.view'              => 'ship_info',
                'admin.shipLocation.view'      => 'ship_location',
                'admin.shipPartner.view'       => 'ship_partner',
                'admin.shipDeparture.view'     => 'ship_departure',
                'admin.shipPort.view'          => 'ship_port',
                'admin.service.view'           => 'service_info',
                'admin.serviceLocation.view'   => 'service_location',
                'admin.air.view'               => 'air_info',
                'admin.airLocation.view'       => 'air_location',
                'admin.airPartner.view'        => 'air_partner',
                'admin.airDeparture.view'      => 'air_departure',
                'admin.airPort.view'           => 'air_port',
                'admin.combo.view'             => 'combo_info',
                'admin.comboLocation.view'     => 'combo_location',
                'admin.comboPartner.view'      => 'combo_partner',
                'admin.hotel.view'             => 'hotel_info',
                'admin.hotelLocation.view'     => 'hotel_location',
                'admin.guide.view'             => 'guide_info',
                'admin.category.view'          => 'category_info',
                'admin.blog.view'              => 'blog_info',
                'admin.page.view'              => 'page_info',
                'admin.carrentalLocation.view' => 'carrental_location',
            ];
        }
        return $map[$routeName] ?? null;
    }

    /** Route admin create/update/view → seo.type (dùng khi persist form POST). */
    public static function resolveSeoTypeFromAdminRoute(string $routeName): ?string
    {
        $direct = self::routeNameToSeoType($routeName);
        if ($direct !== null) {
            return $direct;
        }

        if (!str_starts_with($routeName, 'admin.')) {
            return null;
        }

        foreach (['.create', '.update'] as $suffix) {
            if (!str_ends_with($routeName, $suffix)) {
                continue;
            }

            $viewRoute = substr($routeName, 0, -strlen($suffix)) . '.view';

            return self::routeNameToSeoType($viewRoute);
        }

        return null;
    }

    /**
     * Auto-persist translations từ admin form mà không cần sửa từng controller.
     *
     * Yêu cầu kích hoạt:
     *  - Là HTTP request thuộc route name `admin.*`.
     *  - Request có chứa `translations` (input array).
     *  - `seo_translations` table tồn tại (bật khi đã chạy migrate Phase 1).
     *
     * Cách hoạt động:
     *  1. Listener `Seo::saved` ghi seo_id vừa được lưu vào `$request->attributes`.
     *  2. Closure `terminating` được gọi sau khi response gửi xong. Lúc này entity
     *     gốc (Tour, Ship, …) chắc chắn đã tồn tại trong DB → tra qua
     *     `config('tablemysql.{type}.model')` và `where('seo_id', $seo->id)`.
     *  3. Gọi `EntityTranslationService::persistFromRequest()` để upsert
     *     seo_translations + <entity>_translations cho mọi locale.
     *
     * Mọi exception đều được log nhưng không bubble lên — tránh ảnh hưởng UX admin.
     */
    private function bootMultilingualAutoPersist(): void
    {
        // 1) Khi Seo được lưu, ghi nhớ id để terminating closure dùng.
        Seo::saved(function (Seo $seo) {
            if (!app()->bound('request')) return;
            $request = request();
            if (!$request) return;
            $route = $request->route();
            if (!$route) return;
            $name = $route->getName() ?? '';
            if (!str_starts_with($name, 'admin.')) return;

            // Chỉ giữ id MỚI NHẤT (cuối cùng) — để khớp với entity sẽ insert sau.
            $request->attributes->set('__lastSavedSeoId', $seo->id);
        });

        // 2) Sau khi response gửi xong → persist translations.
        $this->app->terminating(function () {
            try {
                if (!app()->bound('request')) return;
                $request = request();
                if (!$request) return;
                $route = $request->route();
                if (!$route) return;
                $name = $route->getName() ?? '';
                if (!str_starts_with($name, 'admin.')) return;

                $translations = $request->input('translations');
                if (empty($translations) || !is_array($translations)) return;

                if (!Schema::hasTable('seo_translations')) return;

                // seo_id ưu tiên: form input (UPDATE), fallback đến id ghi nhớ từ Seo::saved (CREATE).
                $seoId = (int) ($request->input('seo_id') ?: 0);
                if ($seoId <= 0) {
                    $seoId = (int) ($request->attributes->get('__lastSavedSeoId') ?: 0);
                }
                if ($seoId <= 0) return;

                $seo = Seo::find($seoId);
                if (!$seo) return;

                $cfg = config('tablemysql.' . $seo->type);
                $modelClass = $cfg['model'] ?? null;
                $entity = null;
                if ($modelClass && class_exists($modelClass)) {
                    $entity = $modelClass::where('seo_id', $seo->id)->first();
                }

                EntityTranslationService::persistFromRequest($seo, $entity, $translations);
            } catch (\Throwable $e) {
                Log::warning('Auto-persist translations failed: ' . $e->getMessage(), [
                    'route' => optional(request()->route())->getName(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
    }

    /**
     * Sync seo_translations cho DEFAULT locale mỗi khi Seo::saved.
     *
     * Lý do: form admin gốc submit thẳng vào bảng `seo`. Nếu không sync, bảng
     * `seo_translations[lang_id=default]` sẽ bị stale → frontend ở default locale
     * vẫn đọc từ `seo` (OK), nhưng nếu code dần chuyển sang dùng `seo_translations`
     * cho mọi locale, sẽ thấy dữ liệu cũ. Hook này giải quyết triệt để.
     */
    private function bootDefaultLocaleSeoSync(): void
    {
        Seo::saved(function (Seo $seo) {
            try {
                if (!Schema::hasTable('seo_translations')) return;
                $defaultCode = config('language.default_code', 'vi');
                $defaultLang = Language::byCode($defaultCode);
                if (!$defaultLang) return;

                // Lấy fields từ Seo
                $payload = [
                    'seo_id'          => $seo->id,
                    'language_id'     => $defaultLang->id,
                    'title'           => $seo->title,
                    'description'     => $seo->description,
                    'seo_title'       => $seo->seo_title       ?? $seo->title,
                    'seo_description' => $seo->seo_description ?? $seo->description,
                    'slug'            => $seo->slug,
                    'slug_full'       => $seo->slug_full       ?? $seo->slug,
                    'link_canonical'  => $seo->link_canonical,
                    'status'          => 'published',
                    'translated_by'   => 'auto-sync',
                    'updated_at'      => now(),
                ];

                $existing = DB::table('seo_translations')
                    ->where('seo_id', $seo->id)
                    ->where('language_id', $defaultLang->id)
                    ->first();
                if ($existing) {
                    DB::table('seo_translations')->where('id', $existing->id)->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('seo_translations')->insertOrIgnore($payload);
                }
            } catch (\Throwable $e) {
                Log::warning('Default seo_translations sync failed: ' . $e->getMessage(), [
                    'seo_id' => $seo->id ?? null,
                ]);
            }
        });
    }

    /**
     * Khi admin lưu form gốc có textarea #content → đồng bộ seo_content_translations.
     *
     * Frontend ưu tiên đọc DB; nếu chỉ ghi file legacy thì textarea lần sau vẫn trống
     * và trang public không phản ánh chỉnh sửa mới.
     */
    private function bootSeoContentAdminPersist(): void
    {
        $this->app->terminating(function (): void {
            try {
                if (!app()->bound('request')) {
                    return;
                }

                $request = request();
                if (!$request || !$request->isMethod('POST')) {
                    return;
                }

                $route = $request->route();
                if (!$route) {
                    return;
                }

                $routeName = (string) ($route->getName() ?? '');
                if (!str_starts_with($routeName, 'admin.')) {
                    return;
                }

                $seoType = self::resolveSeoTypeFromAdminRoute($routeName);
                if ($seoType === null) {
                    return;
                }

                if (!$request->has('content')) {
                    return;
                }

                $content = $request->input('content');
                if (!is_string($content)) {
                    return;
                }

                if (!Schema::hasTable('seo_content_translations')) {
                    return;
                }

                $seoId = (int) ($request->input('seo_id') ?: 0);
                if ($seoId <= 0) {
                    $seoId = (int) ($request->attributes->get('__lastSavedSeoId') ?: 0);
                }
                if ($seoId <= 0) {
                    return;
                }

                $seo = Seo::find($seoId);
                if (!$seo) {
                    return;
                }

                $slug = trim((string) ($request->input('slug') ?: $seo->getRawOriginal('slug') ?: ''));
                if ($slug === '') {
                    return;
                }

                app(\App\Services\SeoContentService::class)->persistForAdmin($seo, $slug, $content);
            } catch (\Throwable $e) {
                Log::warning('Seo content admin persist failed: ' . $e->getMessage(), [
                    'route' => optional(request()->route())->getName(),
                ]);
            }
        });
    }
}
