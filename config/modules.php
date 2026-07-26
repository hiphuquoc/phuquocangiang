<?php

/**
 * Phạm vi module superdong.dev — du lịch một đảo (Côn Đảo).
 * Fork từ hitour.dev (toàn quốc / quốc tế); tắt module không dùng tại đây.
 *
 * @see docs/scope-single-island.md
 */
return [
    'single_island' => true,

    /** @deprecated Dùng island_name() helper — lấy từ Tour Location hoặc ISLAND_NAME trong .env */
    'island_name'   => env('ISLAND_NAME', 'Côn Đảo'),

    /** Bật giao diện trang chủ mới (prototype home-v2) tại `/` và `/{locale}`. */
    'use_home_v2'   => true,

    /** Bật giao diện mới cho trang danh mục tour (tour_location). */
    'use_tour_location_v2' => true,

    /** Bật giao diện mới cho trang danh mục vé tàu (ship_location). */
    'use_ship_location_v2' => true,

    /** Bật giao diện mới cho trang danh mục vé vui chơi (service_location). */
    'use_service_location_v2' => true,

    /** Bật giao diện mới cho trang chi tiết blog (blog_info). */
    'use_blog_v2' => true,

    /** Bật giao diện mới cho trang danh mục blog (category_info). */
    'use_category_v2' => true,

    /** Bật giao diện mới cho trang chi tiết khách sạn (hotel_info). */
    'use_hotel_v2' => true,

    /** Bật giao diện mới cho trang danh mục khách sạn (hotel_location). */
    'use_hotel_location_v2' => true,

    /** Bật giao diện mới cho trang danh mục thuê xe (carrental_location). */
    'use_carrental_location_v2' => true,

    /** Bật giao diện mới cho trang cẩm nang (guide_info). */
    'use_guide_v2' => true,

    /** Bật giao diện mới cho trang chi tiết tour (tour_info). */
    'use_tour_v2' => true,

    /** Bật giao diện mới cho trang chi tiết vé tàu (ship_info). */
    'use_ship_v2' => true,

    /** Bật giao diện mới cho trang chi tiết vé vui chơi (service_info). */
    'use_service_v2' => true,

    'enabled' => [
        'tour'         => true,
        'tour_foreign' => false,
        'ship'         => true,
        'air'          => false,
        'hotel'        => true,
        'combo'        => true,
        'service'      => true,
        'carrental'    => true,
        'guide'        => true,
        'blog'         => true,
        'page'         => true,
        'category'     => true,
        'tool_seo'     => false,
    ],

    /** seo.type → module key */
    'seo_type_map' => [
        'tour_location'     => 'tour',
        'tour_info'         => 'tour',
        'tour_continent'    => 'tour_foreign',
        'tour_country'      => 'tour_foreign',
        'tour_info_foreign' => 'tour_foreign',
        'ship_location'     => 'ship',
        'ship_partner'      => 'ship',
        'ship_info'         => 'ship',
        'air_location'      => 'air',
        'air_info'          => 'air',
        'air_partner'       => 'air',
        'hotel_location'    => 'hotel',
        'hotel_info'        => 'hotel',
        'combo_location'    => 'combo',
        'combo_info'        => 'combo',
        'service_location'  => 'service',
        'service_info'      => 'service',
        'carrental_location'=> 'carrental',
        'guide_info'        => 'guide',
        'blog_info'         => 'blog',
        'page_info'         => 'page',
        'category_info'     => 'category',
    ],

    /** fragment pageType → module key */
    'fragment_type_map' => [
        'tour-location'    => 'tour',
        'tour-country'     => 'tour_foreign',
        'tour-continent'   => 'tour_foreign',
        'air-location'     => 'air',
        'ship-location'    => 'ship',
        'combo-location'   => 'combo',
        'service-location' => 'service',
        'home'             => 'service',
    ],
];
