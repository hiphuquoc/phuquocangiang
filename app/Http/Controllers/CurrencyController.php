<?php

namespace App\Http\Controllers;

use App\Services\CurrencyManager;
use Illuminate\Http\Request;

/**
 * CurrencyController.
 *
 * Endpoint progressive enhancement cho currency picker. JS ưu tiên set cookie
 * client-side + reload (xem `headerTop.blade.php`); endpoint server này là
 * fallback cho:
 *   - Trình duyệt tắt JS.
 *   - Crawler / API client muốn switch theo URL.
 *
 * Cách dùng:
 *   GET  /currency/switch?to=USD&redirect=/du-lich-phu-quoc
 *   POST /currency/switch    body: { to=USD, redirect=/x }
 *
 * Tham số:
 *   - `to`        : currency code muốn set.
 *   - `redirect`  : URL nội bộ để quay về (mặc định: referer hoặc /).
 *
 * Cookie: được set qua DetectCurrency middleware (tự refresh khi resolved
 * từ query `?currency=`). Để chắc chắn, ở đây cũng queue cookie tay.
 */
class CurrencyController extends Controller
{
    public function switch(Request $request)
    {
        $manager = app(CurrencyManager::class);
        $to      = strtoupper((string) ($request->input('to') ?: $request->query('to')));
        if ($to === '' || !$manager->isSupported($to)) {
            $to = $manager->current();
        }

        $manager->setCurrent($to);

        $cookieName = (string) config('currency.cookie.name', 'app_currency');
        $minutes    = (int) config('currency.cookie.ttl_days', 365) * 24 * 60;
        $sameSite   = (string) config('currency.cookie.same_site', 'lax');
        $secure     = config('currency.cookie.secure', null);
        $httpOnly   = (bool) config('currency.cookie.http_only', false);

        $cookie = cookie(
            $cookieName,
            $to,
            $minutes,
            '/',
            null,
            is_null($secure) ? $request->isSecure() : (bool) $secure,
            $httpOnly,
            false,
            $sameSite
        );

        $redirect = $this->sanitizeRedirect(
            $request->input('redirect') ?: $request->query('redirect') ?: ($request->headers->get('referer') ?: '/')
        );

        return redirect($redirect)->withCookie($cookie);
    }

    /**
     * Chỉ cho phép redirect về URL nội bộ (path bắt đầu bằng `/`).
     * Trường hợp URL đầy đủ → chỉ giữ path + query.
     */
    private function sanitizeRedirect(?string $raw): string
    {
        if (empty($raw)) return '/';

        // Nếu là URL absolute → tách lấy path + query (loại bỏ host bên ngoài).
        if (preg_match('#^https?://#i', $raw)) {
            $parts = parse_url($raw);
            $path  = $parts['path']  ?? '/';
            $query = !empty($parts['query']) ? '?' . $parts['query'] : '';
            $raw   = $path . $query;
        }

        if (!str_starts_with($raw, '/')) {
            $raw = '/' . ltrim($raw, '/');
        }

        // Strip ?currency=... khỏi redirect để không lặp lại switch
        $raw = preg_replace('/([?&])currency=[^&]*(&|$)/', '$1', $raw) ?? $raw;
        $raw = rtrim($raw, '?&');

        return $raw === '' ? '/' : $raw;
    }
}
