# superdong.dev — Phạm vi dự án (một đảo)

> Fork từ `hitour.dev` (du lịch toàn quốc + quốc tế). **superdong.dev** chỉ phục vụ **một đảo: Côn Đảo** (vé tàu Super Dong, tour, khách sạn, combo, vé vui chơi trên đảo).

## Giữ lại

| Module | Mô tả |
|--------|--------|
| Vé tàu | `ship_location`, `ship_info`, `ship_partner`, booking tàu |
| Tour trong nước | `tour_location`, `tour_info` — chỉ điểm đến trên đảo |
| Khách sạn | `hotel_location`, `hotel_info` |
| Combo | `combo_location`, `combo_info` |
| Vé vui chơi | `service_location`, `service_info` |
| Cho thuê xe | `carrental_location` |
| Cẩm nang | `guide_info` |
| CMS | Blog, trang tĩnh, chuyên mục |
| Booking | Tour, tàu, combo, khách sạn, dịch vụ |
| Admin core | Ảnh, nhân viên, redirect, cache |

## Đã tắt / gỡ khỏi UI

| Module | Lý do |
|--------|--------|
| Tour nước ngoài | `tour_continent`, `tour_country`, `tour_info_foreign` — phạm vi toàn cầu |
| Vé máy bay | `air_*` — không phù hợp site một đảo nhỏ |
| Mega menu đa vùng VN | Bắc / Trung / Nam / nhiều đảo — chỉ hiển thị nội dung đảo |
| Đối tác hàng không | Trang chủ |
| Công cụ SEO vệ tinh | Auto-post blogger — giữ redirect 301 nếu cần |

## Cấu hình

Bật/tắt module tại `config/modules.php`:

```php
'enabled' => [
    'tour_foreign' => false,
    'air'          => false,
    // ...
],
'single_island' => true,
```

Helper: `module_enabled()`, `seo_type_enabled()`, `fragment_type_enabled()`, `single_island_mode()`.

## Ghi chú kỹ thuật

- URL public của module tắt → **404** (`RoutingController`).
- Sitemap không liệt kê type đã tắt.
- Menu admin và header đã ẩn module tắt; controller/route admin vẫn tồn tại trong codebase (fork Hitour) để tránh break migration — có thể xóa hẳn ở phase sau.
