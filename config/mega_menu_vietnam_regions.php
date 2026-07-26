<?php

/**
 * Mega menu « Tour du lịch » — ảnh nền theo miền (rail Việt Nam).
 *
 * - Mỗi khóa là slug giống dữ liệu: south | central | north | other
 * - Giá trị: URL tuyệt đối (https://...) hoặc đường dẫn site (/images/...)
 * - Chuỗi rỗng: không dùng ảnh, hiển thị gradient mặc định theo miền
 *
 * Có thể ghi đè bằng biến môi trường (tuỳ chọn), ví dụ trong .env:
 *   MEGA_VN_IMG_SOUTH=https://...
 *   MEGA_VN_IMG_CENTRAL=/images/mega-vn/central.webp
 *   MEGA_VN_IMG_NORTH=
 *   MEGA_VN_IMG_OTHER=
 */
return [
    'images' => [
        'south' => (string) env(
            'MEGA_VN_IMG_SOUTH',
            'https://hitour.vn/storage/images/upload/du-lich-mien-nam-viet-nam-type-manager-upload.webp'
        ),
        'central' => (string) env(
            'MEGA_VN_IMG_CENTRAL',
            'https://hitour.vn/storage/images/upload/du-lich-mien-nam-viet-nam-type-manager-upload.webp'
        ),
        'north' => (string) env(
            'MEGA_VN_IMG_NORTH',
            'https://hitour.vn/storage/images/upload/du-lich-mien-nam-viet-nam-type-manager-upload.webp'
        ),
        'other' => (string) env(
            'MEGA_VN_IMG_OTHER',
            'https://hitour.vn/storage/images/upload/du-lich-mien-nam-viet-nam-type-manager-upload.webp'
        ),
    ],
];
