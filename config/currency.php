<?php

/**
 * Cấu hình hệ thống đa tiền tệ (Phase 1 — front-end / catalog).
 *
 * Giá GỐC trong DB luôn lưu bằng VND. Khi hiển thị, hệ thống sẽ:
 *   1. Đọc currency hiện tại từ cookie người dùng (nếu hợp lệ).
 *   2. Nếu chưa có → lấy `defaults_by_locale` theo locale hiện tại.
 *   3. Nếu vẫn không khớp → fallback về `default`.
 *
 * Helpers gọi qua `format_price()`, `convert_from_vnd()`,
 * `current_currency()` — xem `app/Helpers/currency.php`.
 *
 * Khi đổi tỷ giá / thêm currency mới: chỉ cần sửa file này, không cần đụng
 * code. Sau đó `php artisan cache:clear && php artisan view:clear` rồi clear
 * `HtmlCacheService` (cache key có namespace currency nên cache cũ vô hại).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Currency mặc định toàn site
    |--------------------------------------------------------------------------
    | Fallback cuối cùng nếu cookie và locale-default đều không khớp.
    */
    'default' => env('APP_CURRENCY_DEFAULT', 'VND'),

    /*
    |--------------------------------------------------------------------------
    | Currency dùng làm CHUẨN khi hiển thị tỷ giá so sánh trên picker UI
    |--------------------------------------------------------------------------
    | Khách hàng quốc tế quen với USD nên mặc định lấy USD làm chuẩn:
    | dropdown sẽ hiển thị "1 USD ≈ 25,800 ₫", "1 USD ≈ 0.92 €", "1 USD ≈ 156 ¥"...
    | Đổi sang VND/EUR... chỉ cần sửa config này.
    */
    'rate_base' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Mapping: locale → currency mặc định
    |--------------------------------------------------------------------------
    | Khi user CHƯA từng chọn currency (cookie trống), DetectCurrency
    | middleware sẽ resolve currency theo locale đang dùng. Pick currency
    | phổ biến nhất của thị trường nói ngôn ngữ đó:
    |   - EN khách quốc tế  → USD (mặc định toàn cầu)
    |   - ZH (TQ)           → CNY
    |   - ES (Tây BN/Mỹ La) → EUR
    |   - AR (Trung Đông)   → AED
    |   - ID (Indonesia)    → IDR
    |   - PT (Brazil/BĐN)   → BRL
    |   - RU (Nga)          → RUB
    |   - DE (Đức)          → EUR
    |   - FR (Pháp/Tây Phi) → EUR
    |   - JA (Nhật)         → JPY
    |   - KO (Hàn)          → KRW
    |   - TH (Thái)         → THB
    | User vẫn có thể đổi sang currency khác qua picker (vd EN khách Anh đổi
    | sang GBP, FR khách Canada đổi sang CAD…).
    */
    'defaults_by_locale' => [
        'vi' => 'VND',
        'en' => 'USD',
        'ko' => 'KRW',
        'ja' => 'JPY',
        'zh' => 'CNY',
        'es' => 'EUR',
        'ar' => 'AED',
        'id' => 'IDR',
        'pt' => 'BRL',
        'fr' => 'EUR',
        'de' => 'EUR',
        'ru' => 'RUB',
        'th' => 'THB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Danh sách currencies được hỗ trợ
    |--------------------------------------------------------------------------
    | Mỗi entry:
    |   vnd_per_unit     : 1 unit currency này = bao nhiêu VND. Chuyển từ
    |                      VND: displayed = vnd_amount / vnd_per_unit.
    |   symbol           : ký tự hiển thị ('đ', '$', '€', ...).
    |   symbol_position  : 'before' (USD/EUR…) | 'after' (VND).
    |   symbol_html      : HTML wrap khi format ra view (vd '<sup>đ</sup>').
    |   decimals         : số chữ số thập phân.
    |   thousands_sep    : ký tự ngăn nghìn.
    |   decimal_sep      : ký tự thập phân.
    |   name             : tên tiếng Anh (cho hreflang / a11y).
    |   name_local       : tên hiển thị trên UI dropdown.
    |   flag             : emoji cờ quốc gia (hoặc URL nếu cần).
    |   enabled          : true → xuất hiện trên currency picker UI.
    |   note             : ghi chú nhỏ trên dropdown (optional).
    |
    | LƯU Ý: Khi thay đổi `vnd_per_unit` (cập nhật tỷ giá), nên clear HTML
    | cache để các trang đã render lại theo tỷ giá mới.
    */
    'currencies' => [

        /* ============= Châu Á – Thái Bình Dương ============= */

        'VND' => [
            'vnd_per_unit'    => 1,
            'symbol'          => 'đ',
            'symbol_position' => 'after',
            'symbol_html'     => '<sup>đ</sup>',
            'decimals'        => 0,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Vietnamese Dong',
            'name_local'      => 'Đồng Việt Nam',
            'flag'            => '🇻🇳',
            'enabled'         => true,
            'note'            => 'Đồng nội tệ',
        ],
        'USD' => [
            'vnd_per_unit'    => 25800,
            'symbol'          => '$',
            'symbol_position' => 'before',
            'symbol_html'     => '$',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'US Dollar',
            'name_local'      => 'Đô la Mỹ',
            'flag'            => '🇺🇸',
            'enabled'         => true,
            'note'            => 'Recommended for international guests',
        ],

        /* ============= Châu Âu ============= */

        'EUR' => [
            'vnd_per_unit'    => 28500,
            'symbol'          => '€',
            'symbol_position' => 'before',
            'symbol_html'     => '€',
            'decimals'        => 2,
            'thousands_sep'   => '.',
            'decimal_sep'     => ',',
            'name'            => 'Euro',
            'name_local'      => 'Euro',
            'flag'            => '🇪🇺',
            'enabled'         => true,
            'note'            => 'Dành cho khách EU/Pháp/Đức/Bồ Đào Nha/TBN',
        ],
        'GBP' => [
            'vnd_per_unit'    => 33000,
            'symbol'          => '£',
            'symbol_position' => 'before',
            'symbol_html'     => '£',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'British Pound',
            'name_local'      => 'Bảng Anh',
            'flag'            => '🇬🇧',
            'enabled'         => true,
            'note'            => 'Dành cho khách Anh',
        ],
        'CHF' => [
            'vnd_per_unit'    => 28800,
            'symbol'          => 'Fr',
            'symbol_position' => 'before',
            'symbol_html'     => 'Fr',
            'decimals'        => 2,
            'thousands_sep'   => "'",
            'decimal_sep'     => '.',
            'name'            => 'Swiss Franc',
            'name_local'      => 'Franc Thụy Sĩ',
            'flag'            => '🇨🇭',
            'enabled'         => true,
            'note'            => 'Dành cho khách Thụy Sĩ',
        ],

        /* ============= Bắc Mỹ ============= */

        'CAD' => [
            'vnd_per_unit'    => 19000,
            'symbol'          => 'C$',
            'symbol_position' => 'before',
            'symbol_html'     => 'C$',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Canadian Dollar',
            'name_local'      => 'Đô la Canada',
            'flag'            => '🇨🇦',
            'enabled'         => true,
            'note'            => 'Dành cho khách Canada',
        ],

        /* ============= Châu Đại Dương ============= */

        'AUD' => [
            'vnd_per_unit'    => 17000,
            'symbol'          => 'A$',
            'symbol_position' => 'before',
            'symbol_html'     => 'A$',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Australian Dollar',
            'name_local'      => 'Đô la Úc',
            'flag'            => '🇦🇺',
            'enabled'         => true,
            'note'            => 'Dành cho khách Úc',
        ],

        /* ============= Mỹ Latinh ============= */

        'BRL' => [
            'vnd_per_unit'    => 4500,
            'symbol'          => 'R$',
            'symbol_position' => 'before',
            'symbol_html'     => 'R$',
            'decimals'        => 2,
            'thousands_sep'   => '.',
            'decimal_sep'     => ',',
            'name'            => 'Brazilian Real',
            'name_local'      => 'Real Brazil',
            'flag'            => '🇧🇷',
            'enabled'         => true,
            'note'            => 'Dành cho khách Brazil',
        ],
        'MXN' => [
            'vnd_per_unit'    => 1450,
            'symbol'          => 'Mex$',
            'symbol_position' => 'before',
            'symbol_html'     => 'Mex$',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Mexican Peso',
            'name_local'      => 'Peso Mexico',
            'flag'            => '🇲🇽',
            'enabled'         => true,
            'note'            => 'Dành cho khách Mexico',
        ],
        'ARS' => [
            'vnd_per_unit'    => 28,
            'symbol'          => 'AR$',
            'symbol_position' => 'before',
            'symbol_html'     => 'AR$',
            'decimals'        => 0,
            'thousands_sep'   => '.',
            'decimal_sep'     => ',',
            'name'            => 'Argentine Peso',
            'name_local'      => 'Peso Argentina',
            'flag'            => '🇦🇷',
            'enabled'         => true,
            'note'            => 'Dành cho khách Argentina',
        ],

        /* ============= Trung Đông ============= */

        'AED' => [
            'vnd_per_unit'    => 7020,
            'symbol'          => 'AED',
            'symbol_position' => 'before',
            'symbol_html'     => 'AED',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'UAE Dirham',
            'name_local'      => 'Dirham UAE',
            'flag'            => '🇦🇪',
            'enabled'         => true,
            'note'            => 'Dành cho khách UAE / Trung Đông',
        ],
        'SAR' => [
            'vnd_per_unit'    => 6880,
            'symbol'          => 'SAR',
            'symbol_position' => 'before',
            'symbol_html'     => 'SAR',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Saudi Riyal',
            'name_local'      => 'Riyal Ả Rập',
            'flag'            => '🇸🇦',
            'enabled'         => true,
            'note'            => 'Dành cho khách Ả Rập Saudi',
        ],
        'EGP' => [
            'vnd_per_unit'    => 520,
            'symbol'          => 'E£',
            'symbol_position' => 'before',
            'symbol_html'     => 'E£',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Egyptian Pound',
            'name_local'      => 'Bảng Ai Cập',
            'flag'            => '🇪🇬',
            'enabled'         => true,
            'note'            => 'Dành cho khách Ai Cập',
        ],

        /* ============= Đông Á ============= */

        'JPY' => [
            'vnd_per_unit'    => 165,
            'symbol'          => '¥',
            'symbol_position' => 'before',
            'symbol_html'     => '¥',
            'decimals'        => 0,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Japanese Yen',
            'name_local'      => 'Yên Nhật',
            'flag'            => '🇯🇵',
            'enabled'         => true,
            'note'            => 'Dành cho khách Nhật',
        ],
        'KRW' => [
            'vnd_per_unit'    => 18,
            'symbol'          => '₩',
            'symbol_position' => 'before',
            'symbol_html'     => '₩',
            'decimals'        => 0,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Korean Won',
            'name_local'      => 'Won Hàn Quốc',
            'flag'            => '🇰🇷',
            'enabled'         => true,
            'note'            => 'Dành cho khách Hàn',
        ],
        'CNY' => [
            'vnd_per_unit'    => 3550,
            'symbol'          => '¥',
            'symbol_position' => 'before',
            'symbol_html'     => '¥',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Chinese Yuan',
            'name_local'      => 'Nhân dân tệ',
            'flag'            => '🇨🇳',
            'enabled'         => true,
            'note'            => 'Dành cho khách Trung Quốc',
        ],

        /* ============= Đông Nam Á ============= */

        'THB' => [
            'vnd_per_unit'    => 720,
            'symbol'          => '฿',
            'symbol_position' => 'before',
            'symbol_html'     => '฿',
            'decimals'        => 2,
            'thousands_sep'   => ',',
            'decimal_sep'     => '.',
            'name'            => 'Thai Baht',
            'name_local'      => 'Baht Thái',
            'flag'            => '🇹🇭',
            'enabled'         => true,
            'note'            => 'Dành cho khách Thái',
        ],
        'IDR' => [
            'vnd_per_unit'    => 1.6,
            'symbol'          => 'Rp',
            'symbol_position' => 'before',
            'symbol_html'     => 'Rp',
            'decimals'        => 0,
            'thousands_sep'   => '.',
            'decimal_sep'     => ',',
            'name'            => 'Indonesian Rupiah',
            'name_local'      => 'Rupiah Indonesia',
            'flag'            => '🇮🇩',
            'enabled'         => true,
            'note'            => 'Dành cho khách Indonesia',
        ],

        /* ============= Đông Âu & Trung Á ============= */

        'RUB' => [
            'vnd_per_unit'    => 290,
            'symbol'          => '₽',
            'symbol_position' => 'after',
            'symbol_html'     => '₽',
            'decimals'        => 0,
            'thousands_sep'   => ' ',
            'decimal_sep'     => ',',
            'name'            => 'Russian Ruble',
            'name_local'      => 'Rúp Nga',
            'flag'            => '🇷🇺',
            'enabled'         => true,
            'note'            => 'Dành cho khách Nga',
        ],
        'KZT' => [
            'vnd_per_unit'    => 52,
            'symbol'          => '₸',
            'symbol_position' => 'after',
            'symbol_html'     => '₸',
            'decimals'        => 0,
            'thousands_sep'   => ' ',
            'decimal_sep'     => ',',
            'name'            => 'Kazakhstani Tenge',
            'name_local'      => 'Tenge Kazakhstan',
            'flag'            => '🇰🇿',
            'enabled'         => true,
            'note'            => 'Dành cho khách Kazakhstan / Trung Á',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie lưu lựa chọn của người dùng
    |--------------------------------------------------------------------------
    */
    'cookie' => [
        'name'      => 'app_currency',
        'ttl_days'  => 365,
        // SameSite=Lax đảm bảo cookie đi kèm request top-level từ link ngoài
        // mà không bị block; Secure tự động theo APP_ENV (https only ở prod).
        'same_site' => 'lax',
        'secure'    => env('SESSION_SECURE_COOKIE', null),
        'http_only' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hiển thị "giá liên hệ" khi quy đổi cho ra số quá nhỏ
    |--------------------------------------------------------------------------
    | Một số currency có vnd_per_unit lớn (USD/EUR) → giá VND nhỏ (vài chục
    | nghìn) khi quy đổi có thể < 1 USD. Khi `min_display` >0 và kết quả
    | nhỏ hơn ngưỡng, helper trả về chuỗi `contact_label`.
    | Mặc định 0 = luôn hiển thị số.
    */
    'min_display'   => 0,
    'contact_label' => 'Liên hệ',

    /*
    |--------------------------------------------------------------------------
    | Endpoint chuyển đổi currency (client gọi để set cookie)
    |--------------------------------------------------------------------------
    */
    'switch_endpoint' => '/currency/switch',
];
