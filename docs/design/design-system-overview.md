# HiTour / Super Dong Design System — Baseline + Home canvas

> **Super Dong (superdong.dev):** dùng [`superdong-design-system.md`](superdong-design-system.md) làm source of truth — sky palette, Roboto + Be Vietnam Pro, breakpoint/spacing đồng bộ.

## 1) Mục tiêu

Tài liệu là nguồn tham chiếu để AI và dev thiết kế phần mới **đồng bộ** với `hitour.dev`, ưu tiên:

- Conversion: tên sản phẩm, giá, ưu đãi, CTA đọc được trong 2–3 giây.
- Giảm style ad-hoc: tái sử dụng pattern và token đã chốt.
- **Trang chủ & marketing blocks**: phong cách **modern / clean / round** — ít lớp viền chồng, một lớp bóng hoặc một viền nhạt.

## 2) Ngôn ngữ giao diện

### 2.1 Listing (Tour, Vé, Combo, Air, Hotel card)

- Family class: `tourList`, `tourGrid`, `tourList_item`, `tourList_item_action`.
- Vé vui chơi: `serviceTicketCatalog` + khung action `productGridUnified`.
- Badge giảm giá: trên ảnh, góc phải trên (`tourList_item_gallery_top_saleoff`).

### 2.2 Filter + view switch

- `filterBox` cho listing có lọc.
- Icon view: chỉ cặp `table-list` / `table-cells`.
- Desktop mặc định grid.

### 2.3 Thứ tự hierarchy card

1. Thumbnail + badge  
2. Title  
3. Rating / meta  
4. Mô tả ngắn  
5. Giá + CTA  

Không đảo giá/CTA lên trên title.

## 3) Token & bề mặt (map SCSS)

| Vai trò | Biến / ghi chú |
|--------|----------------|
| Brand / CTA | `$colorLv1`, `$colorLv2` |
| Accent phụ | `$colorSLv1`, `$colorSLv2` |
| Giá | `$colorPrice` |
| Tiêu đề | `$colorTitle` |
| Mô tả phụ | `$colorDesc`, slate `#64748b` cho home |

**Bo góc (2026 — ưu tiên round hơn baseline listing cũ):**

- **Card / panel marketing (trang chủ)**: `18px`–`24px`; chip / tab dạng pill: `999px`.
- **Listing dense (tour/ve nội dung dài)**: giữ `10px`–`14px` nếu chưa refactor; khi đồng bộ home thì có thể nới tối đa `18px`.

**Đổ bóng:**

- Một lớp: `0 16px 36px -24px rgba(15, 23, 42, 0.2)` (tùy khối chỉnh biên độ).
- Tránh: `box-shadow` + `border` đậm + `background` pattern cùng lúc trừ khi cần contrast rõ.

**Viền:**

- Ưu tiên **một** ranh giới: `1px solid rgba(148, 163, 184, 0.2)` hoặc chỉ shadow.
- Tránh: `border-top` + `border-bottom` trên cùng một `container` tạo “kép” giữa hai section — trên trang chủ dùng wrapper `.pageHome` (xem dưới).

## 4) “Clean canvas” — trang chủ (`.pageHome`) & trang danh sách theo địa điểm (`.pageListing`)

- **Trang chủ**: toàn bộ khối dưới slider trong `home.blade.php` bọc **`pageHome`**.
- **Danh sách theo điểm** (tour / vé vui chơi / tàu / khách sạn tại một địa điểm): `tourLocation/index`, `serviceLocation/index`, `shipLocation/index`, `hotelLocation/index` bọc **`pageListing`** (gồm `sortBooking`, breadcrumb và `pageContent`).

Cùng một lớp SCSS **`.pageHome, .pageListing`** cho form tab pill, section `withBorder` một đường phân cách, filter/card bo tròn. **`pageListing`** có thêm khối SCSS riêng: nền `backgroundPrimaryGradiend` nhạt hơn, `titlePage`, `contentBox`, ô giá tàu (`shipGrid` row).

Nguyên tắc:

1. **Section**: bỏ cặp `border-top` + `border-bottom` trên `.sectionBox.withBorder .container`; chỉ giữ **một** đường phân cách nhạt `border-top` giữa hai section liền nhau.
2. **Tiêu đề section**: màu `#0f172a`, `letter-spacing: -0.02em`; mô tả `#64748b`, `max-width` ~42rem.
3. **Form đặt chỗ** (`bookFormSort`): tab dạng **pill** trong thanh frosted; thân form **một** viền nhạt + bo `20px`, không dải `border-top` xanh dày chồng lên nền xám cũ.
4. **Grid đặc thù home** (đảo, điểm nổi bật, tàu, đối tác, vé): bo lớn hơn, shadow một lớp, viền slate nhạt — chi tiết SCSS nằm trong `.pageHome { ... }` tại `resources/sources/main/style.scss`.

Khi thêm section mới trên trang chủ: đặt markup **bên trong** `.pageHome` và tái dụng pattern trên.

## 5) Rule tái sử dụng component

1. Kiểm tra đã có `tourList` / `productGridUnified` chưa.  
2. Style riêng → wrapper + override **bên trong** wrapper (ví dụ `.pageHome .…`).  
3. Tránh sửa global selector khi chỉ cần scope trang chủ.

## 6) UX quality bar (listing)

- So sánh nhanh ít nhất ~3 card trên desktop.
- Giá + CTA không nhảy layout khi title dài/ngắn.
- Meta chip có padding, không dính viền.
- Mobile: CTA tối thiểu ~40px chiều cao vùng chạm.

## 7) Accessibility

- Contrast AA cho text / chip / button.
- Icon trang trí: `aria-hidden="true"`; icon mang nghĩa cần label.
- `focus-visible` rõ (outline / ring).

## 8) Definition of done

- Đúng structure trong docs này + `components-patterns.md`.
- Không duplicate style giá/CTA nếu đã có `productGridUnified`.
- QA desktop / tablet / mobile; text dài không vỡ layout.
- Section trang chủ nằm trong `.pageHome` và tuân “Clean canvas” mục 4.
