<?php

/**
 * Mapping seo.type -> entity metadata.
 *
 * Mục đích:
 *  - Loại bỏ chuỗi if/else dài trong RoutingController.
 *  - Một nơi duy nhất khai báo: model, view, content storage path,
 *    relations cần eager load, có vào sitemap không, và những cột nào cần
 *    dịch (`translatable`).
 *  - Phase 1 sử dụng `translatable` để: (a) sinh batch <entity>_translations
 *    migrations, (b) admin tabs đa ngôn ngữ tự động, (c) auto fallback đọc/ghi.
 *  - Phase 2 (V3.0) thêm `translation_relations`: liệt kê các bảng quan hệ
 *    chứa nội dung dịch được (tour_content, tour_timetable, tour_option,
 *    question_answer_info, ...). Trang AdminTranslation sinh form tự động
 *    cho mọi relation theo block này.
 *
 * Cách dùng:
 *  $cfg = config('tablemysql.tour_info');
 *  $modelClass = $cfg['model'];
 *  $translatable = $cfg['translatable'] ?? [];
 *  $relations    = $cfg['translation_relations'] ?? [];
 *
 * Convention `translation_relations[<key>]`:
 *  - 'model'      => FQCN model gốc của relation
 *  - 'fk'         => cột FK trong bảng relation trỏ về entity gốc
 *                    (ví dụ tour_content.tour_info_id, faq dùng reference_id)
 *  - 'fields'     => mảng cột dịch được
 *  - 'label'      => tên hiển thị trên UI admin
 *  - 'multiple'   => true=hasMany, false=hasOne
 *  - 'extra_filter' => mảng where bổ sung (vd FAQ: relation_table='tour_info')
 *  - 'order_by'   => cột sort tăng dần (mặc định 'id')
 */
return [
    /* ===== TOUR ===== */
    'tour_location' => [
        'model'         => \App\Models\TourLocation::class,
        'view'          => 'main.tourLocation.index',
        'content_dir'   => 'public/contents/tourLocations/',
        'with'          => ['seo', 'airLocations', 'guides', 'shipLocations', 'carrentalLocations', 'serviceLocations', 'destinations', 'specials'],
        'sitemap'       => true,
        'translatable'  => ['name', 'display_name', 'description', 'note'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'tour_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                => 'question',
                    'answer'                  => 'answer',
                    'question_answer_info_id' => '__id',
                ],
            ],
        ],
    ],
    'tour_info' => [
        'model'         => \App\Models\Tour::class,
        'view'          => 'main.tour.index',
        'content_dir'   => 'public/contents/tours/',
        'with'          => ['seo', 'locations', 'staffs.infoStaff', 'options.prices', 'departure', 'content', 'timetables'],
        'sitemap'       => true,
        'translatable'  => ['name', 'pick_up', 'transport', 'departure_schedule'],
        /* V3.1: form gốc tour không có ô <input name="name">. Tên tour được
         * derive từ input "title" (cột seo.title). Map alias để khi save
         * bản dịch, cột tour_translations.name tự đồng bộ với title đã dịch. */
        'translatable_input_aliases' => [
            'name' => 'title',
        ],
        'translation_relations' => [
            'content' => [
                'model' => \App\Models\TourContent::class, 'fk' => 'tour_info_id',
                'fields' => ['special_content','special_list','include','not_include','policy_child','menu','hotel','policy_cancel','note'],
                'label' => 'Nội dung Tour (sections)', 'multiple' => false,
                /* V3.1 input mapping: form gốc (admin.tour.formInfo) đặt textarea name=
                   "special_content"... ngay ở root form → chỉ cần tên cột DB. */
                'input_layout' => 'top_level',
            ],
            'timetables' => [
                'model' => \App\Models\TourTimetable::class, 'fk' => 'tour_info_id',
                'fields' => ['title', 'content', 'content_sort'],
                'label' => 'Lịch trình tour', 'multiple' => true, 'order_by' => 'id',
                'input_layout' => 'array',
                'input_array_name' => 'timetable',
                'input_id_alias'   => 'tour_timetable_id',
                'input_field_aliases' => [
                    'tour_timetable_title'        => 'title',
                    'tour_timetable_content'      => 'content',
                    'tour_timetable_content_sort' => 'content_sort',
                    'tour_timetable_id'           => '__id',
                ],
            ],
            'options' => [
                'model' => \App\Models\TourOption::class, 'fk' => 'tour_info_id',
                'fields' => ['name'],
                'label' => 'Tùy chọn (Options)', 'multiple' => true, 'order_by' => 'id',
                /* AJAX modal — UI mode dịch sẽ override modal endpoint qua
                   admin.translation.option.save (POST tour_option_id + name) */
                'input_layout' => 'ajax',
                'ajax_input_id_alias' => 'tour_option_id',
                'ajax_input_field_aliases' => [
                    'name' => 'name',
                ],
            ],
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'Câu hỏi thường gặp (FAQ)', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'tour_info'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'tour_continent' => [
        'model'         => \App\Models\TourContinent::class,
        'view'          => 'main.tourContinent.index',
        'content_dir'   => 'public/contents/tourContinents/',
        'with'          => ['seo', 'tourCountries', 'airLocations', 'serviceLocations', 'guides.infoGuide.seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'tour_continent'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'tour_country' => [
        'model'         => \App\Models\TourCountry::class,
        'view'          => 'main.tourCountry.index',
        'content_dir'   => 'public/contents/tourCountries/',
        'with'          => ['seo', 'tours', 'airLocations', 'serviceLocations', 'guides'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'tour_country'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'tour_info_foreign' => [
        'model'         => \App\Models\TourInfoForeign::class,
        'view'          => 'main.tourInfoForeign.index',
        'content_dir'   => 'public/contents/tours/',
        'with'          => ['seo', 'staffs.infoStaff', 'options.prices', 'departure', 'content', 'timetables'],
        'sitemap'       => true,
        'translatable'  => ['name', 'pick_up', 'transport', 'departure_schedule'],
        'translatable_input_aliases' => ['name' => 'title'],
        'translation_relations' => [
            'content' => [
                'model' => \App\Models\TourContentForeign::class, 'fk' => 'tour_info_foreign_id',
                'fields' => ['special_content','special_list','include','not_include','policy_child','menu','hotel','policy_cancel','note'],
                'label' => 'Nội dung Tour (sections)', 'multiple' => false,
            ],
            'timetables' => [
                'model' => \App\Models\TourTimetableForeign::class, 'fk' => 'tour_info_foreign_id',
                'fields' => ['title', 'content', 'content_sort'],
                'label' => 'Lịch trình', 'multiple' => true, 'order_by' => 'id',
            ],
            'options' => [
                'model' => \App\Models\TourOptionForeign::class, 'fk' => 'tour_info_foreign_id',
                'fields' => ['option'],
                'label' => 'Tùy chọn', 'multiple' => true, 'order_by' => 'id',
                'input_layout' => 'ajax',
                'ajax_input_id_alias' => 'tour_option_foreign_id',
                'ajax_input_field_aliases' => [
                    'option' => 'option',
                ],
            ],
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'tour_info_foreign'],
            ],
        ],
    ],

    /* ===== SHIP ===== */
    'ship_location' => [
        'model'         => \App\Models\ShipLocation::class,
        'view'          => 'main.shipLocation.index',
        'content_dir'   => 'public/contents/shipLocations/',
        'with'          => ['seo', 'district', 'ships', 'tourLocations', 'categories'],
        'sitemap'       => true,
        'translatable'  => ['name', 'display_name', 'description', 'note'],
        'translatable_input_aliases' => ['name' => 'title'],
        // "Bảng giá & Lịch tàu mặc định" là textarea schedule lưu file legacy.
        'translatable_inputs' => ['schedule'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'ship_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'ship_partner' => [
        'model'         => \App\Models\ShipPartner::class,
        'view'          => 'main.shipPartner.index',
        'content_dir'   => 'public/contents/shipPartners/',
        'with'          => ['seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'ship_partner'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'ship_info' => [
        'model'         => \App\Models\Ship::class,
        'view'          => 'main.ship.index',
        'content_dir'   => 'public/contents/ships/',
        'with'          => ['seo', 'partners', 'portDeparture', 'portLocation', 'location'],
        'sitemap'       => true,
        'translatable'  => ['name', 'note'],
        'translatable_input_aliases' => ['name' => 'title'],
        // "Bảng giá & Lịch tàu mặc định" là textarea schedule lưu file legacy.
        'translatable_inputs' => ['schedule'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'ship_info'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],

    /* ===== SERVICE ===== */
    'service_location' => [
        'model'         => \App\Models\ServiceLocation::class,
        'view'          => 'main.serviceLocation.index',
        'content_dir'   => 'public/contents/serviceLocations/',
        'with'          => ['seo', 'services', 'tourLocations'],
        'sitemap'       => true,
        'translatable'  => ['name', 'display_name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'service_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'service_info' => [
        'model'         => \App\Models\Service::class,
        'view'          => 'main.service.index',
        'content_dir'   => 'public/contents/services/',
        'with'          => ['seo', 'serviceLocation'],
        'sitemap'       => true,
        'translatable'  => ['name'],
        'translation_relations' => [
            'options' => [
                'model' => \App\Models\ServiceOption::class, 'fk' => 'service_info_id',
                'fields' => ['name'],
                'label' => 'Tùy chọn dịch vụ', 'multiple' => true, 'order_by' => 'id',
            ],
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'service_info'],
            ],
        ],
    ],

    /* ===== AIR ===== */
    'air_location' => [
        'model'         => \App\Models\AirLocation::class,
        'view'          => 'main.airLocation.index',
        'content_dir'   => 'public/contents/airLocations/',
        'with'          => ['seo', 'airs', 'tourLocations'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description', 'display_name'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'air_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'air_partner' => [
        'model'         => \App\Models\AirPartner::class,
        'view'          => null,
        'content_dir'   => 'public/contents/airPartners/',
        'with'          => ['seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'air_partner'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'air_info' => [
        'model'         => \App\Models\Air::class,
        'view'          => 'main.air.index',
        'content_dir'   => 'public/contents/airs/',
        'with'          => ['seo', 'airLocation'],
        'sitemap'       => true,
        'translatable'  => ['name'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'air_info'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],

    /* ===== COMBO ===== */
    'combo_location' => [
        'model'         => \App\Models\ComboLocation::class,
        'view'          => 'main.comboLocation.index',
        'content_dir'   => 'public/contents/comboLocations/',
        'with'          => ['seo', 'combos'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'combo_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'combo_info' => [
        'model'         => \App\Models\Combo::class,
        'view'          => 'main.combo.index',
        'content_dir'   => null,
        'with'          => ['seo', 'locations', 'staffs.infoStaff', 'options.prices'],
        'sitemap'       => true,
        'translatable'  => ['name'],
        'translation_relations' => [
            'content' => [
                'model' => \App\Models\ComboContent::class, 'fk' => 'combo_info_id',
                'fields' => ['name', 'content'],
                'label' => 'Nội dung Combo (sections)', 'multiple' => true, 'order_by' => 'ordering',
            ],
            'options' => [
                'model' => \App\Models\ComboOption::class, 'fk' => 'combo_info_id',
                'fields' => ['name'],
                'label' => 'Tùy chọn Combo', 'multiple' => true, 'order_by' => 'id',
            ],
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'combo_info'],
            ],
        ],
    ],

    /* ===== HOTEL ===== */
    'hotel_location' => [
        'model'         => \App\Models\HotelLocation::class,
        'view'          => 'main.hotelLocation.index',
        'content_dir'   => 'public/contents/hotelLocations/',
        'with'          => ['seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description', 'display_name'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'hotel_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],
    'hotel_info' => [
        'model'         => \App\Models\Hotel::class,
        'view'          => 'main.hotel.index',
        'content_dir'   => 'public/contents/hotelInfos/',
        'with'          => ['seo', 'comments', 'rooms'],
        'sitemap'       => true,
        'translatable'  => ['name', 'address'],
        'translation_relations' => [
            'content' => [
                'model' => \App\Models\HotelContent::class, 'fk' => 'hotel_info_id',
                'fields' => ['name', 'content'],
                'label' => 'Nội dung Khách sạn (sections)', 'multiple' => true, 'order_by' => 'ordering',
            ],
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'hotel_info'],
            ],
        ],
    ],

    /* ===== CARRENTAL ===== */
    'carrental_location' => [
        'model'         => \App\Models\CarrentalLocation::class,
        'view'          => 'main.carrentalLocation.index',
        'content_dir'   => 'public/contents/carrentalLocations/',
        'with'          => ['seo', 'tourLocations'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description', 'location_name'],
        'translation_relations' => [
            'faqs' => [
                'model' => \App\Models\QuestionAnswer::class, 'fk' => 'reference_id',
                'fields' => ['question', 'answer'],
                'label' => 'FAQ', 'multiple' => true,
                'extra_filter' => ['relation_table' => 'carrental_location'],
                'input_layout' => 'array',
                'input_array_name' => 'question_answer',
                'input_id_alias'   => 'question_answer_info_id',
                'input_field_aliases' => [
                    'question'                 => 'question',
                    'answer'                   => 'answer',
                    'question_answer_info_id'  => '__id',
                ],
            ],
        ],
    ],

    /* ===== GUIDE ===== */
    'guide_info' => [
        'model'         => \App\Models\Guide::class,
        'view'          => 'main.guide.index',
        'content_dir'   => 'public/contents/guides/',
        'with'          => ['seo', 'tourLocations'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description', 'display_name'],
    ],

    /* ===== BLOG / CATEGORY / PAGE ===== */
    'category_info' => [
        'model'         => \App\Models\Category::class,
        'view'          => null,
        'content_dir'   => null,
        'with'          => ['seo', 'tourLocations'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
    ],
    'blog_info' => [
        'model'         => \App\Models\Blog::class,
        'view'          => 'main.blog.index',
        'content_dir'   => 'public/contents/blogs/',
        'with'          => ['seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description', 'note'],
    ],
    'page_info' => [
        'model'         => \App\Models\Page::class,
        'view'          => 'main.page.index',
        'content_dir'   => 'public/contents/pages/',
        'with'          => ['seo'],
        'sitemap'       => true,
        'translatable'  => ['name', 'description'],
    ],
];
