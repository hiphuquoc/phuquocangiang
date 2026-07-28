# Layout, Spacing, and Grid (Execution Guide)

> **Hệ responsive đầy đủ:** [responsive-tokens.md](./responsive-tokens.md) — cascade 5 breakpoint, CSS vars, utility class.  
> File này giữ quy tắc thực thi cụ thể cho listing/card/home.

## 1) Breakpoints

**Chuẩn bắt buộc (max-width cascade):** `1199` → `1023` → `990` → `768` → `567` px.

**Min-width (layout grid):**

| Token SCSS | Min-width | Ghi chú |
|---|---|---|
| `$sd-bp-sm` | **568px** | = BP-SM + 1 |
| `$sd-bp-md` | **769px** | Tablet |
| `$sd-bp-lg` | **991px** | = BP-MD+ + 1 |
| `$sd-bp-nav` | **1024px** | Desktop nav |
| `$sd-bp-xl` | **1200px** | Container desktop |
| `$sd-bp-2xl` | **1440px** | Wide — không nới container |

Container khuyến nghị:

- Listing dày (tour / vé / combo): **1240px** (`$sd-container`)
- Nội dung đọc dài: 760–860px

## 2) Spacing tokens

**Responsive (ưu tiên):** `--sd-space-gutter`, `--sd-space-gap*`, `--sd-space-section-y`, `--sd-space-card*` — xem [responsive-tokens.md](./responsive-tokens.md) §3.

**Static 4pt (compile-time):** `$sd-space-1` … `$sd-space-16` trong `_tokens.scss` — chỉ dùng cho micro-spacing trong component (icon offset, badge inset).

- Trong một component tối đa ~3 mức spacing khác nhau.
- Tránh nhảy spacing ngẫu nhiên giữa các card cùng loại.

## 3) Listing grid standards

`tourList.tourGrid`:

- Desktop: 3 cột  
- Tablet: 2 cột  
- Mobile: 1 cột  

Gap: desktop `1.25rem`; tablet 16–20; mobile 12–16.

## 4) Spacing nội bộ card listing

Gallery → Info → Action:

- Info padding: 12–16  
- Action padding: 12–16  
- Meta → desc: 6–10  
- Desc → action: 10–14  

## 5) Price và CTA (`productGridUnified`)

- Giá ưu tiên trái; nút phải; không dồn giá sát nút.
- Mobile xuống dòng: giữ thứ tự đọc — giá trước, CTA sau.

## 6) Badge placement

- Sale: top-right ảnh; khoảng cách mép ảnh 8–12px.
- Thời gian / vị trí: có thể top-left.

## 7) Description block rhythm

`tourList_item_info_desc`: line-height 1.4–1.5; có thể nền nhẹ + border mềm; tránh khối text trắng trơn khó scan.

## 8) Responsive checklist

- Text dài không vỡ width card.
- Meta chips wrap hợp lý.
- Giá/CTA không nhảy khi title 1–2 dòng.
- Ảnh ratio cố định, hạn chế CLS.

## 9) Trang chủ — nhịp dọc (`.pageHome`)

- **Không** dùng cặp `border-top` + `border-bottom` dày trên `.sectionBox.withBorder .container` trong scope home.
- Phân tách section: **một** `border-top` nhạt (`rgba(15, 23, 42, 0.06)`) giữa hai section `withBorder` liền nhau.
- Padding dọc section: `clamp(1.65rem, 3.4vw, 2.45rem)` (đã áp trong SCSS).
- Hai khối đối tác liền nhau: vẫn có đường ngăn nhưng màu đồng bộ nhạt hơn trong `.pageHome`.

Khi thêm section mới trên home: giữ cùng nhịp padding; không thêm viền box thứ ba chồng lên card con.
