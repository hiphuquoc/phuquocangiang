# Component Patterns and UI Rules (Current UI + Home canvas)

## 1) Mục tiêu

Định nghĩa pattern để AI/dev tạo UI mới **không lệch tone** với UI hiện tại và với **trang chủ** đã chuẩn hóa trong `.pageHome`.

Nguyên tắc:

- Reuse trước, tạo mới sau.
- Không duplicate style cùng một chức năng.
- Listing card cùng “họ” với `tourList`.

## 2) Listing card family

### 2.1 Cấu trúc base (bắt buộc)

1. `*_item_gallery`  
2. `*_item_info`  
3. `*_item_action`  

Tour / vé: base `tourList_item`; không đặt giá trong block info thuần info (giá nằm vùng action / `productGridUnified`).

### 2.2 Shared action area

- `productGridUnified`: giá, giá gạch, nút, badge sale trên ảnh.
- Sửa giá/nút → ưu tiên sửa trong `productGridUnified`.

### 2.3 Sale badge

- Góc phải trên ảnh; format `Giảm xx%`.
- Không hai badge sale hai vị trí khác nhau.

## 3) Meta chips

- Quick facts ngắn; cho phép wrap; chip không sát viền card.
- Padding dọc nhỏ (`0.15rem`–`0.2rem`).
- **Home (`.pageHome`)**: chip ưu tiên nền brand rất nhạt + viền nhạt, bo pill — tránh nền xám đậm + viền xanh đậm chồng nhau.

## 4) Button rules

- CTA trên card: một nút chính; bo `10px`–`14px` (listing); có thể pill cho marketing.
- Text ngắn, động từ rõ (`Đặt vé ngay`, `Chi tiết`).
- Không nút quá to phá cân bằng card.

## 5) Description block (`tourList_item_info_desc`)

- Line-height dễ đọc; có thể nền nhẹ + border mềm để tách khối.
- Tránh đoạn văn dài liên tục trên listing.

## 6) Filter và view switch

- `filterBox`; toggle chỉ icon list/grid.
- Đổi view: đổi class (`tourGrid`), không rebuild DOM không cần thiết.

## 7) State patterns

- Loading: `loadingGridBox`.
- Empty: thông điệp ngắn + hướng bước tiếp.
- Error: message rõ + retry.

## 8) Governance (PR UI)

- Đã reuse `tourList` / `productGridUnified`?
- Có hardcode giá/CTA riêng?
- Test title dài/ngắn + mobile ~320px?

## 9) Home-only patterns (`.pageHome`)

| Khối | Class / ghi chú |
|------|------------------|
| Bọc toàn trang chủ (dưới slider) | `.pageHome` trong `home.blade.php` |
| Section tiêu đề | `.sectionBox_title`, `.sectionBox_desc` — spacing và màu đã chuẩn trong SCSS scope |
| Form tab + body | `.bookFormSort`, `.bookFormSort_head`, `.bookFormSort_body` — pill + một viền |
| Điểm đến biển đảo | `.islandLocationBox` — card bo, một shadow |
| Điểm nổi bật | `.specialLocationBox`, `.specialLocationBoxMobile` |
| Vé tàu | `.shipLocationShowcase` |
| Khách sạn | `.hotelDomesticShowcase` (intro + grid) |
| Vé dịch vụ (home) | `.filterBox` + `.serviceTicketCatalog.tourGrid .tourList_item` |
| Đối tác | `.homePartners` — logo cell bo, viền nhạt |

Khi thêm block marketing mới trên home: **chỉ** thêm rule con dưới `.pageHome` trong `style.scss` trừ khi component dùng chung site-wide.

## 10) Trang danh sách địa điểm (`.pageListing`)

- Bọc từ `@section('content')`: `sortBooking` + breadcrumb + `pageContent` (xem các `*Location/index.blade.php`).
- Style chung với home qua **`.pageHome, .pageListing`** trong `style.scss`.
- Card tour (`tourList.tourGrid.productGridUnified`), vé (`serviceTicketCatalog`), tàu (`shipGrid`), khách sạn (`hotelList`) đều nhận bo + viền + shadow một lớp trong scope đó.
