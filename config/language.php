<?php

/**
 * Cấu hình ngôn ngữ — single source of truth.
 *
 * Khoá `default_code` chỉ định ngôn ngữ mặc định (URL không có prefix).
 * Mỗi mục trong `list` là 1 ngôn ngữ, code giống `languages.code` trong DB.
 *
 * Quy ước:
 *  - default_code = 'vi' -> URL công khai: /<slug>
 *  - các locale khác = /<locale>/<slug>  (ví dụ /en/, /zh-cn/, /zh-tw/)
 *  - hreflang dùng đúng `code` (vi, en, zh-CN, zh-TW, ja, ko, es, fr, de, ru).
 *  - og:locale dùng `og_locale` (vi_VN, en_US, zh_CN, zh_TW, ja_JP, ...).
 *
 * Cập nhật danh sách: chỉnh sửa 'list' rồi chạy:
 *   php artisan migrate    (migration 2026_05_05_140000_seed_languages_full sẽ upsert)
 *   php artisan cache:clear
 */
return [

    'default_code' => env('APP_DEFAULT_LOCALE', 'vi'),

    'fallback_code' => env('APP_FALLBACK_LOCALE', 'vi'),

    /* nếu true: khi locale không phải default, mọi link/menu/breadcrumb tự thêm prefix /{locale}/ */
    'use_subfolder_for_default' => false,

    /**
     * Danh sách ngôn ngữ hỗ trợ (10 thị trường mục tiêu).
     * is_active: bật/tắt việc xuất hiện ở public route + menu + sitemap.
     * is_default: chỉ duy nhất 1 row có giá trị này (URL không có prefix).
     */
    'list' => [
        'vi' => [
            'code'        => 'vi',
            'name'        => 'Tiếng Việt',
            'name_native' => 'Tiếng Việt',
            'flag'        => '/images/flags/vi.svg',
            'og_locale'   => 'vi_VN',
            'hreflang'    => 'vi',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => true,
            'sort'        => 1,
        ],
        'en' => [
            'code'        => 'en',
            'name'        => 'Tiếng Anh',
            'name_native' => 'English',
            'flag'        => '/images/flags/en.svg',
            'og_locale'   => 'en_US',
            'hreflang'    => 'en',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 2,
        ],
        'zh-cn' => [
            'code'        => 'zh-cn',
            'name'        => 'Tiếng Trung (Giản thể)',
            'name_native' => '简体中文',
            'flag'        => '/images/flags/zh-cn.svg',
            'og_locale'   => 'zh_CN',
            'hreflang'    => 'zh-CN',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 3,
        ],
        'zh-tw' => [
            'code'        => 'zh-tw',
            'name'        => 'Tiếng Trung (Phồn thể)',
            'name_native' => '繁體中文',
            'flag'        => '/images/flags/zh-tw.svg',
            'og_locale'   => 'zh_TW',
            'hreflang'    => 'zh-TW',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 4,
        ],
        'ja' => [
            'code'        => 'ja',
            'name'        => 'Tiếng Nhật',
            'name_native' => '日本語',
            'flag'        => '/images/flags/ja.svg',
            'og_locale'   => 'ja_JP',
            'hreflang'    => 'ja',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 5,
        ],
        'ko' => [
            'code'        => 'ko',
            'name'        => 'Tiếng Hàn',
            'name_native' => '한국어',
            'flag'        => '/images/flags/ko.svg',
            'og_locale'   => 'ko_KR',
            'hreflang'    => 'ko',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 6,
        ],
        'es' => [
            'code'        => 'es',
            'name'        => 'Tiếng Tây Ban Nha',
            'name_native' => 'Español',
            'flag'        => '/images/flags/es.svg',
            'og_locale'   => 'es_ES',
            'hreflang'    => 'es',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 7,
        ],
        'fr' => [
            'code'        => 'fr',
            'name'        => 'Tiếng Pháp',
            'name_native' => 'Français',
            'flag'        => '/images/flags/fr.svg',
            'og_locale'   => 'fr_FR',
            'hreflang'    => 'fr',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 8,
        ],
        'de' => [
            'code'        => 'de',
            'name'        => 'Tiếng Đức',
            'name_native' => 'Deutsch',
            'flag'        => '/images/flags/de.svg',
            'og_locale'   => 'de_DE',
            'hreflang'    => 'de',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 9,
        ],
        'ru' => [
            'code'        => 'ru',
            'name'        => 'Tiếng Nga',
            'name_native' => 'Русский',
            'flag'        => '/images/flags/ru.svg',
            'og_locale'   => 'ru_RU',
            'hreflang'    => 'ru',
            'dir'         => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
            'sort'        => 10,
        ],
    ],
];
