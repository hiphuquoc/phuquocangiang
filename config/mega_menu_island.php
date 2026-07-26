<?php

/**
 * Mega menu « Tour Biển đảo » — ảnh nền hero + mô tả (key dịch t()).
 *
 * - image: URL tuyệt đối (https://...) hoặc đường dẫn site (/images/...). Chuỗi rỗng: gradient mặc định.
 * - intro: key bản dịch giống mega_tour_* (lang_ui / CMS), ví dụ mega_tour_island_panel_intro
 *
 * Ghi đè bằng .env (tuỳ chọn):
 *   MEGA_ISLAND_IMG=https://...
 *   MEGA_ISLAND_INTRO_KEY=mega_tour_island_panel_intro
 */
return [
    'image' => (string) env('MEGA_ISLAND_IMG', 'https://hitour.vn/storage/images/upload/du-lich-bien-dao-type-manager-upload.webp'),
    'intro' => (string) env('MEGA_ISLAND_INTRO_KEY', 'mega_tour_island_panel_intro'),
];
