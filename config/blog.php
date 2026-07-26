<?php

/**
 * Nhãn (badge) hiển thị trên lưới Điểm đến / Đặc sản — trang danh mục tour.
 * Admin chọn khi sửa bài viết (blog). Key lưu DB: highlight_tag.
 */
return [
    'highlight_tags' => [
        'featured' => [
            'label'   => 'Nổi bật',
            'variant' => 'featured',
        ],
        'recommended' => [
            'label'   => 'Đề xuất',
            'variant' => 'recommended',
        ],
        'popular' => [
            'label'   => 'Ưa chuộng',
            'variant' => 'popular',
        ],
        'trending' => [
            'label'   => 'Xu hướng',
            'variant' => 'trending',
        ],
        'must_see' => [
            'label'   => 'Nhất định ghé',
            'variant' => 'must_see',
        ],
        'local_pick' => [
            'label'   => 'Gợi ý địa phương',
            'variant' => 'local_pick',
        ],
        'editor_choice' => [
            'label'   => 'Biên tập chọn',
            'variant' => 'editor_choice',
        ],
    ],
];
