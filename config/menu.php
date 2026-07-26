<?php
return [
    'left-menu-admin'   => [
        [
            'name'      => 'Tour du lịch',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-suitcase-rolling"></i>',
            'child'     => [
                [
                    'name'  => '1. Trong nước',
                    'route' => '',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                    'child' => [
                        [
                            'name'  => '1.1. Điểm đến',
                            'route' => 'admin.tourLocation.list',
                            'icon'  => '<i data-feather=\'circle\'></i>'
                        ],
                        [
                            'name'  => '1.2. Chi tiết Tour',
                            'route' => 'admin.tour.list',
                            'icon'  => '<i data-feather=\'circle\'></i>'
                        ]
                    ]
                ],
                [
                    'name'  => '2. Điểm khởi hành',
                    'route' => 'admin.tourDeparture.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '3. Đối tác Tour',
                    'route' => 'admin.tourPartner.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ]
            ]
        ],
        [
            'name'      => 'Khách sạn',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-hotel"></i>',
            'child'     => [
                [
                    'name'  => '1. Điểm đến',
                    'route' => 'admin.hotelLocation.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. Chi tiết Hotel',
                    'route' => 'admin.hotel.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                // [
                //     'name'  => '3. Đối tác hotel',
                //     'route' => 'admin.hotelPartner.list',
                //     'icon'  => '<i data-feather=\'circle\'></i>'
                // ]
            ]
        ],
        [
            'name'      => 'Combo du lịch',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-award"></i>',
            'child'     => [
                [
                    'name'  => '1. Điểm đến',
                    'route' => 'admin.comboLocation.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '2. Chi tiết Combo',
                    'route' => 'admin.combo.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '3. Đối tác Combo',
                    'route' => 'admin.comboPartner.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ]
            ]
        ],
        [
            'name'      => 'Vé tàu',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-ship"></i>',
            'child'     => [
                [
                    'name'  => '1. Bến cảng',
                    'route' => 'admin.shipPort.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                
                [
                    'name'  => '2. Điểm khởi hành',
                    'route' => 'admin.shipDeparture.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '3. Điểm đến',
                    'route' => 'admin.shipLocation.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '4. Hãng Tàu',
                    'route' => 'admin.shipPartner.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                [
                    'name'  => '5. Chi tiết Tàu',
                    'route' => 'admin.ship.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ]
            ]
        ],
        [
            'name'      => 'Vé vui chơi',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-ticket"></i>',
            'child'     => [
                [
                    'name'  => '1. Điểm đến',
                    'route' => 'admin.serviceLocation.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ],
                
                [
                    'name'  => '2. Chi tiết dịch vụ',
                    'route'     => 'admin.service.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ]
            ]
        ],
        [
            'name'      => 'Cẩm nang du lịch',
            'route'     => 'admin.guide.list',
            'icon'      => '<i class="fa-solid fa-book"></i>',    
        ],
        [
            'name'      => 'Cho thuê xe',
            'route'     => 'admin.carrentalLocation.list',
            'icon'      => '<i class="fa-solid fa-car-side"></i>',    
        ],
        [
            'name'      => 'Trang chủ',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-house"></i>',
            'child'     => [
                [
                    'name'  => 'Hero',
                    'route' => 'admin.homeHero.view',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                ],
                [
                    'name'  => 'Gallery Trải nghiệm đảo',
                    'route' => 'admin.homeIslandGallery.view',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                ],
                [
                    'name'  => 'Khách hàng nói gì',
                    'route' => 'admin.homeReviews.view',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                ],
                [
                    'name'  => 'Câu hỏi thường gặp',
                    'route' => 'admin.homeFaq.view',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                ],
                [
                    'name'  => 'Newsletter',
                    'route' => 'admin.homeNewsletter.view',
                    'icon'  => '<i data-feather=\'circle\'></i>',
                ],
            ],
        ],
        [
            'name'      => 'Quản lí Trang',
            'route'     => 'admin.page.list',
            'icon'      => '<i class="fa-solid fa-bookmark"></i>'
        ],
        [
            'name'      => 'Quản lí Blog',
            'route'     => '',
            'icon'      => '<i class="fa-solid fa-blog"></i>',    
            'child'     => [
                [
                    'name'      => 'Chuyên mục',
                    'route'     => 'admin.category.list',
                    'icon'      => '<i data-feather=\'circle\'></i>',    
                ],
                [
                    'name'      => 'Bài viết',
                    'route'     => 'admin.blog.list',
                    'icon'      => '<i data-feather=\'circle\'></i>',    
                ]
            ]
        ],
        [
            'name'  => 'Nhân viên tư vấn',
            'route' => 'admin.staff.list',
            'icon'  => '<i class="fa-solid fa-users"></i>'
        ],
        [
            'name'  => 'Quản lí Booking',
            'route' => '',
            'icon'  => '<i class="fa-solid fa-file-pen"></i>',
            'child' => [
                [
                    'name'  => '1. Booking chung',
                    'route' => 'admin.booking.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ], 
                [
                    'name'  => '2. Booking Tàu',
                    'route' => 'admin.shipBooking.list',
                    'icon'  => '<i data-feather=\'circle\'></i>'
                ]
            ]
        ],
        [
            'name'      => 'Quản lí Ảnh',
            'route'     => 'admin.image.list',
            'icon'      => '<i class="fa-solid fa-image"></i>'
        ],
        [
            'name'  => 'Redirect 301',
            'route' => 'admin.redirect.list',
            'icon'  => '<i class="fa-solid fa-share-from-square"></i>',
        ]
    ]
];