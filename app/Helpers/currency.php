<?php

/**
 * Global helpers cho hệ thống đa tiền tệ.
 *
 * Tất cả đều là wrapper mỏng quanh `App\Services\CurrencyManager`. Mục đích:
 *  - Gọi gọn từ Blade (`{!! format_price($tour->price_show) !!}`).
 *  - Đảm bảo logic resolve / convert / format không lặp lại ở nhiều nơi.
 *
 * Quy ước: TẤT CẢ giá trong DB lưu bằng VND. Helper `format_price()` /
 * `convert_from_vnd()` mặc định nhận input VND, output theo currency hiện tại.
 *
 * Đăng ký autoload qua `composer.json -> autoload.files`.
 */

use App\Services\CurrencyManager;

if (!function_exists('currency_manager')) {
    function currency_manager(): CurrencyManager
    {
        return app(CurrencyManager::class);
    }
}

if (!function_exists('current_currency')) {
    /** Currency code hiện tại của session (vd 'VND', 'USD'). */
    function current_currency(): string
    {
        return currency_manager()->current();
    }
}

if (!function_exists('current_currency_meta')) {
    /** Meta đầy đủ của currency hiện tại. */
    function current_currency_meta(): array
    {
        return currency_manager()->currentMeta();
    }
}

if (!function_exists('available_currencies')) {
    /** Danh sách currencies enabled cho UI picker (key = code). */
    function available_currencies(): array
    {
        return currency_manager()->available();
    }
}

if (!function_exists('currency_symbol')) {
    /** Symbol char (vd 'đ', '$'). */
    function currency_symbol(?string $code = null): string
    {
        $meta = currency_manager()->meta($code);
        return (string) ($meta['symbol'] ?? '');
    }
}

if (!function_exists('convert_from_vnd')) {
    /**
     * Quy đổi 1 số tiền VND sang currency.
     *
     * @param int|float|string|null $amountVnd
     * @param string|null           $to  default = current currency
     */
    function convert_from_vnd($amountVnd, ?string $to = null): float
    {
        return currency_manager()->convertFromVnd($amountVnd, $to);
    }
}

if (!function_exists('format_price')) {
    /**
     * Format giá VND → chuỗi hiển thị HTML theo currency hiện tại.
     * Output mặc định có wrap symbol_html (vd `<sup>đ</sup>`) nên cần
     * dùng `{!! format_price(...) !!}` trong Blade.
     *
     * @param int|float|string|null $amountVnd
     * @param array{currency?:string,html?:bool,fallback?:string} $opts
     */
    function format_price($amountVnd, array $opts = []): string
    {
        if ($amountVnd === null || $amountVnd === '' || (is_numeric($amountVnd) && (float) $amountVnd <= 0)) {
            return (string) ($opts['fallback'] ?? config('currency.contact_label', 'Liên hệ'));
        }
        $to   = $opts['currency'] ?? null;
        $html = array_key_exists('html', $opts) ? (bool) $opts['html'] : true;
        return currency_manager()->formatFromVnd($amountVnd, $to, $html);
    }
}

if (!function_exists('format_price_plain')) {
    /**
     * Phiên bản plain-text (không HTML) — dùng cho title / aria-label / JSON.
     *
     * @param int|float|string|null $amountVnd
     */
    function format_price_plain($amountVnd, ?string $to = null): string
    {
        if ($amountVnd === null || $amountVnd === '' || (is_numeric($amountVnd) && (float) $amountVnd <= 0)) {
            return (string) config('currency.contact_label', 'Liên hệ');
        }
        return currency_manager()->formatFromVnd($amountVnd, $to, false);
    }
}

if (!function_exists('currency_default_for_locale')) {
    function currency_default_for_locale(string $locale): string
    {
        return currency_manager()->defaultForLocale($locale);
    }
}

if (!function_exists('schema_currency')) {
    /** Currency đại diện cho JSON-LD theo locale (không theo cookie user). */
    function schema_currency(?string $locale = null): string
    {
        return currency_default_for_locale($locale ?? app()->getLocale());
    }
}

if (!function_exists('schema_price_amount')) {
    /**
     * Giá VND → số dùng trong schema.org (làm tròn, không HTML).
     *
     * @param int|float|string|null $amountVnd
     */
    function schema_price_amount($amountVnd, ?string $currency = null): float
    {
        if ($amountVnd === null || $amountVnd === '' || !is_numeric($amountVnd)) {
            return 0.0;
        }
        $currency = $currency ?? schema_currency();

        return round(currency_manager()->convertFromVnd($amountVnd, $currency), 2);
    }
}

if (!function_exists('currency_rate_base')) {
    /** Currency dùng làm chuẩn hiển thị tỷ giá (vd 'USD'). */
    function currency_rate_base(): string
    {
        return currency_manager()->rateBase();
    }
}

if (!function_exists('rate_from_base')) {
    /**
     * 1 đơn vị base (mặc định USD) tương đương bao nhiêu đơn vị target.
     * Vd: rate_from_base('VND') → 25800 (1 USD = 25,800 VND).
     */
    function rate_from_base(string $targetCode, ?string $baseCode = null): float
    {
        return currency_manager()->rateFromBase($targetCode, $baseCode);
    }
}

if (!function_exists('format_rate_from_base')) {
    /**
     * Chuỗi tỷ giá đã format: "25,800 <sup>đ</sup>" / "0.92 €".
     * Mặc định trả HTML (chứa symbol_html). Dùng `{!! ... !!}` trong Blade.
     */
    function format_rate_from_base(string $targetCode, ?string $baseCode = null, bool $html = true): string
    {
        return currency_manager()->formatRateFromBase($targetCode, $baseCode, $html);
    }
}
