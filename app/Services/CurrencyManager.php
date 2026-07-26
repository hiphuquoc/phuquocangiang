<?php

namespace App\Services;

/**
 * CurrencyManager.
 *
 * Singleton service quản lý currency hiện tại của session người dùng.
 * Toàn bộ logic resolve / convert / format được tập trung tại đây để có
 * 1 source of truth cho:
 *  - Middleware DetectCurrency
 *  - Helpers `format_price()`, `convert_from_vnd()`, ...
 *  - View / Blade
 *  - Cache key (RoutingController dùng `currency` làm 1 phần namespace)
 *
 * Pattern: bind singleton ở AppServiceProvider qua
 *   $this->app->singleton(CurrencyManager::class);
 * (Mặc định Laravel cho phép resolve class qua container; ta dựa vào
 *  `app(CurrencyManager::class)` để giữ singleton trong scope request.)
 *
 * Lưu ý: KHÔNG đọc cookie tại đây. Việc đọc cookie + validate được
 * thực hiện trong DetectCurrency middleware, sau đó gọi `setCurrent()`.
 */
class CurrencyManager
{
    /** Currency code đang được áp dụng cho request hiện tại (đã validated). */
    protected ?string $current = null;

    /** Cache metadata của các currency để khỏi đọc config lặp lại. */
    protected ?array $cachedCurrencies = null;

    /* ---------------------- Resolve / state ---------------------- */

    /**
     * Set currency hiện tại (đã được validate là supported).
     * Nếu code không hợp lệ → tự fallback về default.
     */
    public function setCurrent(string $code): void
    {
        $code = strtoupper(trim($code));
        $this->current = $this->isSupported($code) ? $code : $this->fallbackDefault();
    }

    /**
     * Trả về currency code hiện tại.
     * Thứ tự lazy resolve khi chưa được middleware set sẵn:
     *  1. Cookie `app_currency` (đọc trực tiếp từ request, hữu ích cho các
     *     route booking không đi qua DetectCurrency middleware).
     *  2. Mặc định theo locale hiện tại (`defaults_by_locale[<locale>]`).
     *  3. `config('currency.default')`.
     */
    public function current(): string
    {
        if ($this->current === null) {
            $this->current = $this->resolveFromRequest();
        }
        return $this->current;
    }

    /** Đọc cookie trực tiếp; fallback locale-default; fallback global default. */
    protected function resolveFromRequest(): string
    {
        try {
            if (app()->bound('request')) {
                $req = app('request');
                $cookieName = (string) config('currency.cookie.name', 'app_currency');
                $value      = $req->cookie($cookieName);
                if (!empty($value) && $this->isSupported((string) $value)) {
                    return strtoupper((string) $value);
                }
            }
        } catch (\Throwable $e) {
            // ignore - chuyển sang fallback locale-default
        }
        return $this->resolveDefaultForCurrentLocale();
    }

    /** Lấy meta đầy đủ của currency hiện tại. */
    public function currentMeta(): array
    {
        return $this->meta($this->current());
    }

    /** Lấy meta theo code. Trả về meta của default nếu code không hợp lệ. */
    public function meta(?string $code = null): array
    {
        $code = strtoupper(trim((string) $code));
        $all  = $this->allCurrencies();
        if ($code !== '' && isset($all[$code])) {
            return $all[$code] + ['code' => $code];
        }
        $default = $this->fallbackDefault();
        return ($all[$default] ?? []) + ['code' => $default];
    }

    /** True nếu currency được khai báo trong config (kể cả enabled=false). */
    public function isSupported(string $code): bool
    {
        return isset($this->allCurrencies()[strtoupper($code)]);
    }

    /** Resolve currency mặc định theo 1 locale cụ thể. */
    public function defaultForLocale(string $locale): string
    {
        $map = (array) config('currency.defaults_by_locale', []);
        $code = $map[$locale] ?? $this->fallbackDefault();
        return $this->isSupported($code) ? strtoupper($code) : $this->fallbackDefault();
    }

    /** Danh sách currencies được phép hiển thị trên picker UI. */
    public function available(): array
    {
        $out = [];
        foreach ($this->allCurrencies() as $code => $meta) {
            if (!($meta['enabled'] ?? true)) continue;
            $out[$code] = $meta + ['code' => $code];
        }
        return $out;
    }

    /* ---------------------- Rate display ---------------------- */

    /** Currency code dùng làm chuẩn hiển thị tỷ giá (mặc định USD). */
    public function rateBase(): string
    {
        $code = strtoupper((string) config('currency.rate_base', 'USD'));
        return $this->isSupported($code) ? $code : $this->fallbackDefault();
    }

    /**
     * 1 đơn vị `$baseCode` (mặc định rate_base/USD) tương đương bao nhiêu
     * đơn vị `$targetCode`. Dùng cho dropdown picker:
     *   "1 USD ≈ 25,800 ₫"  (target = VND)
     *   "1 USD ≈ 0.92 €"    (target = EUR)
     *
     * Công thức: 1 base = (base.vnd_per_unit / target.vnd_per_unit) target.
     */
    public function rateFromBase(string $targetCode, ?string $baseCode = null): float
    {
        $base   = $this->meta($baseCode ?? $this->rateBase());
        $target = $this->meta($targetCode);
        $baseVnd   = max(1e-9, (float) ($base['vnd_per_unit']   ?? 1));
        $targetVnd = max(1e-9, (float) ($target['vnd_per_unit'] ?? 1));
        return $baseVnd / $targetVnd;
    }

    /**
     * Format chuỗi tỷ giá để hiển thị trên UI.
     * Trả về vd:  "≈ 25,800 ₫"  hoặc  "≈ 0.92 €".
     *
     * - Tự tăng decimals lên 2 nếu rate < 1 nhưng target.decimals = 0
     *   (tránh hiển thị "0 €" khi rate ~0.92).
     * - Dùng symbol_html (HTML) hay symbol thuần tuỳ `$html`.
     */
    public function formatRateFromBase(string $targetCode, ?string $baseCode = null, bool $html = true): string
    {
        $target   = $this->meta($targetCode);
        $rate     = $this->rateFromBase($targetCode, $baseCode);
        $decimals = (int) ($target['decimals'] ?? 0);
        if ($rate < 1 && $decimals < 2) $decimals = 2;
        if ($rate >= 100) $decimals = 0;
        $thouSep  = (string) ($target['thousands_sep'] ?? ',');
        $decSep   = (string) ($target['decimal_sep']   ?? '.');
        $symbol   = $html
            ? (string) ($target['symbol_html'] ?? ($target['symbol'] ?? ''))
            : (string) ($target['symbol']      ?? '');
        return number_format($rate, $decimals, $decSep, $thouSep) . ' ' . $symbol;
    }

    /* ---------------------- Convert / format ---------------------- */

    /**
     * Quy đổi 1 số tiền (đang ở VND) sang currency hiển thị.
     *
     * @param int|float|string|null $amountVnd
     * @param string|null           $to        currency code (default = current)
     */
    public function convertFromVnd($amountVnd, ?string $to = null): float
    {
        if ($amountVnd === null || $amountVnd === '') return 0.0;
        $amount = (float) $amountVnd;
        $meta   = $this->meta($to ?? $this->current());
        $rate   = max(1, (float) ($meta['vnd_per_unit'] ?? 1));
        return $amount / $rate;
    }

    /**
     * Format 1 số (đã ở đúng đơn vị currency) thành chuỗi hiển thị.
     *
     * @param int|float $amount
     * @param string|null $code  currency code muốn format theo (default = current)
     * @param bool        $html  true → dùng `symbol_html` (vd <sup>đ</sup>)
     */
    public function format($amount, ?string $code = null, bool $html = true): string
    {
        $meta     = $this->meta($code ?? $this->current());
        $decimals = (int) ($meta['decimals'] ?? 0);
        $thouSep  = (string) ($meta['thousands_sep'] ?? ',');
        $decSep   = (string) ($meta['decimal_sep'] ?? '.');
        $position = $meta['symbol_position'] ?? 'after';
        $symbol   = $html
            ? (string) ($meta['symbol_html'] ?? ($meta['symbol'] ?? ''))
            : (string) ($meta['symbol']      ?? '');

        $num = number_format((float) $amount, $decimals, $decSep, $thouSep);

        return $position === 'before' ? $symbol . $num : $num . $symbol;
    }

    /**
     * Quy đổi VND → currency hiện tại (hoặc $to) rồi format.
     *
     * @param int|float|string|null $amountVnd
     * @param string|null           $to        override currency
     * @param bool                  $html      true → dùng symbol_html
     */
    public function formatFromVnd($amountVnd, ?string $to = null, bool $html = true): string
    {
        if ($amountVnd === null || $amountVnd === '') {
            return (string) config('currency.contact_label', 'Liên hệ');
        }

        $code   = $to ?? $this->current();
        $value  = $this->convertFromVnd($amountVnd, $code);
        $min    = (float) config('currency.min_display', 0);

        if ($min > 0 && $value > 0 && $value < $min) {
            return (string) config('currency.contact_label', 'Liên hệ');
        }

        return $this->format($value, $code, $html);
    }

    /* ---------------------- Internal helpers ---------------------- */

    /** Default cuối cùng — luôn supported. */
    protected function fallbackDefault(): string
    {
        $default = strtoupper((string) config('currency.default', 'VND'));
        $all     = $this->allCurrencies();
        if (isset($all[$default])) return $default;
        // an toàn: nếu config bị lỗi, dùng key đầu tiên có sẵn
        $first = array_key_first($all);
        return $first ? strtoupper($first) : 'VND';
    }

    /** Resolve default cho locale đang active (cho fallback trong CLI). */
    protected function resolveDefaultForCurrentLocale(): string
    {
        try {
            $locale = app()->getLocale() ?: (string) config('language.default_code', 'vi');
            return $this->defaultForLocale($locale);
        } catch (\Throwable $e) {
            return $this->fallbackDefault();
        }
    }

    /** Đọc + cache toàn bộ block `currencies` trong config. */
    protected function allCurrencies(): array
    {
        if ($this->cachedCurrencies !== null) return $this->cachedCurrencies;
        $list = (array) config('currency.currencies', []);
        // normalize key in case có lower-case
        $norm = [];
        foreach ($list as $code => $meta) {
            $norm[strtoupper((string) $code)] = is_array($meta) ? $meta : [];
        }
        return $this->cachedCurrencies = $norm;
    }
}
