<?php

return [
    /*
    | Prefix GCS cho ảnh upload CMS.
    | DB lưu path gốc: "{upload_prefix}/ten-file.webp"
    | Biến thể: ten-file-small.webp (450px), ten-file-medium.webp (800px)
    */
    'upload_prefix' => env('GCS_MEDIA_UPLOAD_PREFIX', 'media/uploads'),

    'disk' => env('GCS_MEDIA_DISK', 'gcs'),

    'quality' => (int) env('GCS_MEDIA_QUALITY', 90),

    /*
    | Các prefix object trên GCS — dùng để nhận diện cloud path (media_url, delete, exists…).
    | Mọi ảnh mới nên nằm trong một trong các prefix này.
    */
    'cloud_prefixes' => array_values(array_filter(array_map(
        static fn ($prefix) => trim((string) $prefix, '/'),
        [
            env('GCS_MEDIA_UPLOAD_PREFIX', 'media/uploads'),
            env('GCS_HOTEL_UPLOAD_PREFIX', 'hotels'),
            env('GCS_HERO_UPLOAD_PREFIX', 'hero'),
            env('GCS_ISLAND_GALLERY_PREFIX', 'island-gallery'),
            env('GCS_REVIEW_AVATAR_PREFIX', 'review-avatars'),
        ]
    ))),

    'variants' => [
        'original' => ['suffix' => '', 'width' => null],
        'small' => ['suffix' => '-small', 'width' => 450],
        'medium' => ['suffix' => '-medium', 'width' => 800],
    ],

    /** Suffix legacy (ảnh cũ trước khi chuẩn hóa) */
    'legacy_suffixes' => ['-400', '-750', '-250', '-460'],
];
