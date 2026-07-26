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

    'variants' => [
        'original' => ['suffix' => '', 'width' => null],
        'small' => ['suffix' => '-small', 'width' => 450],
        'medium' => ['suffix' => '-medium', 'width' => 800],
    ],

    /** Suffix legacy (ảnh cũ trước khi chuẩn hóa) */
    'legacy_suffixes' => ['-400', '-750', '-250', '-460'],
];
