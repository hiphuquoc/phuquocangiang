<?php

return [
    'name'              => 'Hitour',
    'webname'           => 'Hitour.vn',
    'description'       => 'Trang Tour du lịch trực tuyến hàng đầu Việt Nam ®Hitour',
    /* Thông báo validate form */
    'message_validate'  => [
        'not_empty'     => 'Không để trống trường này!',
    ],
    'unit_currency'     => 'đ',
    'logo_square'       => 'https://hitour.vn/storage/images/upload/logo-share-type-manager-upload.png',
    'icon-arrow-email'  => 'https://hitour.vn/images/main/icon-arrow-email.png',
    'avatar_home'       => 'https://hitour.vn/storage/images/upload/banner-hitour-1-type-manager-upload.webp',
    'svg'               => [
        'loading_main'      => '/storage/images/svg/loading_plane_bge9ecef.svg',
        'loading_main_nobg' => '/storage/images/svg/loading_plane_transparent.svg'
    ],
    'title_list_service_sidebar'        => 'Có thể bạn cần?',
    /* Background hỗ trợ loading */
    'background_slider_home'            => '/images/main/background-slider-home.jpg',
    'cache'     => [
        'folderSave'    => 'public/caches',     /* không có trailing slash để Storage::makeDirectory hoạt động đúng */
        'extension'     => 'html',
        'disk'          => 'local',             /* đổi sang 'gcs' khi muốn lưu cache lên Google Cloud */
        'ttl'           => 2592000,             /* 30 ngày */
        'use_gzip'      => true,                /* lưu file .gz để tiết kiệm disk + IO */
        'use_html_min'  => false,               /* bật khi production sẵn sàng (cần test kỹ) */
        'use_jscss_min' => false,               /* bật khi production sẵn sàng */
        /* Menu desktop + mobile: public/caches/menuMain_{locale}.html(.gz) — dùng chung mọi trang cùng ngôn ngữ */
        'menu_key_prefix' => 'menuMain',
    ],
    'rating_rule'       => [
        [
            'text'  => 'Rất tuyệt',
            'score' => '9'
        ],
        [
            'text'  => 'Tuyệt vời',
            'score' => '8'
        ],
        [
            'text'  => 'Rất tốt',
            'score' => '7'
        ],
        [
            'text'  => 'Tốt',
            'score' => '6'
        ],
        [
            'text'  => 'Tạm được',
            'score' => '5'
        ],
        [
            'text'  => 'Hơi tệ',
            'score' => '3'
        ],
        [
            'text'  => 'Rất tệ',
            'score' => '0'
        ]
    ],
    'hotel_type'    => [
        'Khách sạn', 'Khu nghỉ dưỡng', 'Homestay', 'Nhà nghỉ', 'Căn hộ', 'Nhà khách gia đình', 'Biệt thự', 'Nhà riêng','Khác'
    ],
    'hotel_time_receive' => [
        'Tôi chưa biết', '14h00 - 15h00', '15h00 - 16h00', '16h00 - 17h00', '17h00 - 18h00', '18h00 - 19h00', '20h00 - 21h00', '21h00 - 22h00', '22h00 - 23h00', '23h00 - 00h00', '00h00 - 01h00 (hôm sau)', '01h00 - 02h00 (hôm sau)'
    ],

    /*
    |--------------------------------------------------------------------------
    | Footer — khối « Dự án Hitour » (tiêu đề + danh sách link)
    |--------------------------------------------------------------------------
    | Chỉnh sửa tại đây để thêm/bớt/đổi URL mà không sửa Blade.
    | Mỗi phần tử links: label (bắt buộc), url (bắt buộc), title (tuỳ chọn, mặc định = label),
    | target (mặc định _blank), rel (mặc định nofollow noopener).
    */
    'footer' => [
        'eco_projects' => [
            'title' => 'Dự án Hitour',
            'links' => [
                [
                    'label' => 'Hợp tác kinh doanh',
                    'url' => 'https://hoptackinhdoanh.com',
                    'title' => 'Hợp tác kinh doanh',
                ],
                [
                    'label' => 'Chậu vân gỗ nghệ thuật',
                    'url' => 'https://zenpot.vn',
                    'title' => 'Chậu vân gỗ',
                ],
                [
                    'label' => 'Hình nền điện thoại',
                    'url' => 'https://name.com.vn',
                    'title' => 'Hình nền điện thoại',
                ],
                [
                    'label' => 'Bất động sản',
                    'url' => 'https://viland.net',
                    'title' => 'Bất động sản',
                ],
            ],
        ],
        /*
        |--------------------------------------------------------------------------
        | Footer — logo chứng nhận (Bộ Công Thương, …)
        |--------------------------------------------------------------------------
        | Chỉ đặt logo «Đã thông báo / Đã đăng ký» sau khi shop đã được duyệt trên online.gov.vn.
        | href: URL trang WebDetails của doanh nghiệp; để trống thì chỉ hiển thị ảnh (không bọc thẻ a).
        | src: đường dẫn trong public hoặc URL ảnh chính thống.
        |
        | href trỏ WebDetails trên online.gov.vn; để trống thì chỉ hiện ảnh (sau khi đã được duyệt thông báo).
        */
        'trust_badges' => [
            [
                'src' => 'https://webmedia.com.vn/images/2021/09/logo-da-thong-bao-bo-cong-thuong-mau-xanh.png',
                'alt' => 'Đã thông báo Bộ Công Thương',
                'href' => '',
                'title' => 'Thông tin website thương mại điện tử — online.gov.vn',
                'width' => 200,
                'height' => 76,
                'target' => '_blank',
                'rel' => 'nofollow noopener noreferrer',
            ],
        ],
    ],
];