<?php

namespace App\Http\Middleware;

use App\Services\CurrencyManager;
use Closure;
use Illuminate\Http\Request;

/**
 * DetectCurrency middleware.
 *
 * Resolve currency cho request hiện tại theo thứ tự ưu tiên:
 *  1. Query `?currency=USD` (1 lần, sẽ set cookie).
 *  2. Cookie `config('currency.cookie.name')` nếu hợp lệ + supported.
 *  3. `config('currency.defaults_by_locale')[<locale>]`.
 *  4. `config('currency.default')`.
 *
 * Phải chạy SAU DetectLocale để có thể đọc app()->getLocale().
 *
 * Side-effects:
 *  - Set `CurrencyManager::setCurrent()`.
 *  - Share `$currentCurrency` + `$currentCurrencyMeta` + `$availableCurrencies`
 *    cho tất cả view.
 *  - Inject `currency` vào request attributes (controller có thể dùng cho
 *    cache key).
 *  - Nếu user truyền query `?currency=USD` → set/refresh cookie.
 */
class DetectCurrency
{
    public function handle(Request $request, Closure $next)
    {
        $manager  = app(CurrencyManager::class);
        $cookieKey = (string) config('currency.cookie.name', 'app_currency');

        $resolved = null;
        $shouldPersistCookie = false;

        // 1) Query override
        $queryCurrency = $request->query('currency');
        if (!empty($queryCurrency) && $manager->isSupported((string) $queryCurrency)) {
            $resolved = strtoupper((string) $queryCurrency);
            $shouldPersistCookie = true;
        }

        // 2) Cookie
        if ($resolved === null) {
            $cookieValue = $request->cookie($cookieKey);
            if (!empty($cookieValue) && $manager->isSupported((string) $cookieValue)) {
                $resolved = strtoupper((string) $cookieValue);
            }
        }

        // 3) Default theo locale hiện tại
        if ($resolved === null) {
            $locale = app()->getLocale() ?: (string) config('language.default_code', 'vi');
            $resolved = $manager->defaultForLocale($locale);
        }

        $manager->setCurrent($resolved);
        $current = $manager->current();

        // Share cho view + đẩy vào request attributes
        view()->share('currentCurrency', $current);
        view()->share('currentCurrencyMeta', $manager->currentMeta());
        view()->share('availableCurrencies', $manager->available());
        $request->attributes->set('currency', $current);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Refresh cookie nếu user vừa đổi currency qua query, hoặc nếu chưa từng
        // có cookie thì cũng ghi 1 lần để lần sau hit cache HTML đúng namespace.
        if ($shouldPersistCookie || empty($request->cookie($cookieKey))) {
            $minutes = (int) config('currency.cookie.ttl_days', 365) * 24 * 60;
            $sameSite = (string) config('currency.cookie.same_site', 'lax');
            $secure   = config('currency.cookie.secure', null);
            $httpOnly = (bool) config('currency.cookie.http_only', false);

            // Dùng cookie queue thay vì cookie() facade để không bị
            // EncryptCookies mã hoá (chúng ta cần JS đọc được).
            // Cookie native qua headers để chắc chắn không bị encrypt.
            try {
                $response->headers->setCookie(
                    cookie()->make(
                        $cookieKey,
                        $current,
                        $minutes,
                        '/',
                        null,
                        is_null($secure) ? $request->isSecure() : (bool) $secure,
                        $httpOnly,
                        false,
                        $sameSite
                    )
                );
            } catch (\Throwable $e) {
                // ignore — không chặn response chỉ vì cookie
            }
        }

        return $response;
    }
}
