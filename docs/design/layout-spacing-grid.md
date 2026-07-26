# Layout, Spacing, and Grid (Execution Guide)

## 1) Breakpoints

- `xs`: < 576  
- `sm`: >= 576  
- `md`: >= 768  
- `lg`: >= 992  
- `xl`: >= 1200  
- `2xl`: >= 1440  

Container khuyến nghị:

- Listing dày (tour / vé / combo): 1280–1320  
- Nội dung đọc dài: 760–860  

## 2) Spacing tokens (4pt)

4, 8, 12, 16, 20, 24, 32, 40, 48, 64.

Rule:

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
