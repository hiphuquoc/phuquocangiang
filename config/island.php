<?php

/**
 * Cấu hình đảo cho site single-island (home-v2, quick access, sections…).
 * Chỉ cần set ISLAND_TOUR_LOCATION_ID trong .env — mọi block trang chủ
 * load relation từ Tour Location tương ứng.
 *
 * @see App\Services\Island\IslandContextService
 */
return [
    /** ID bản ghi tour_location (bảng tour_location). */
    'tour_location_id' => (int) env('ISLAND_TOUR_LOCATION_ID', 0),

    /** Tên fallback khi chưa cấu hình ID hoặc không tìm thấy bản ghi. */
    'name_fallback' => env('ISLAND_NAME', env('APP_ISLAND_NAME', 'Đảo')),
];
