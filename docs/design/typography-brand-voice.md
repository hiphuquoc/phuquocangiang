# Typography and Brand Voice (UI Current Fit)

## 1) Brand voice

Giong dieu HiTour:
- Chac chan, than thien, tu van nhu nguoi ban dong hanh.
- Khong khoa truong, khong "sale speech" qua muc.
- Ngan gon, uu tien thong tin giup ra quyet dinh nhanh.

## 2) Typography mapped to current UI

He thong hien tai dang dung font family tu bien SCSS (`$fontSegoeBold`, `$fontTitle`). Khi them phan moi, map theo role sau:

- `page-title`: 1.6-2rem, bold
- `section-title`: 1.25-1.45rem, bold
- `card-title`: 1.0-1.1rem, semibold/bold
- `meta/body-sm`: 0.82-0.92rem
- `price-now`: 1.15-1.25rem, bold
- `cta-card`: 0.9-0.95rem

Rule:
- Listing card khong dung body < 0.82rem.
- Gia va CTA phai la cap nhan manh nhat sau title.

## 3) Copy hierarchy in card

Thu tu text:
1. Ten tour/ve (title)
2. Rating/meta chips
3. Description ngan
4. Gia cu (neu co) -> gia hien tai -> CTA

Do dai:
- Card title: toi da 2 dong.
- Description listing: 1-2 dong (co clamp neu can).
- Meta chips: ngan gon, 2-3 tu.

## 4) Microcopy rules

- Dung tu hanh dong ro: `Dat ve ngay`, `Chi tiet`, `Xem them`.
- Meta uu tien tu khoa de scan: `Ve dien tu`, `Ho tro 24/7`, `x goi ve`.
- Viet hoa dau cau thong thuong, khong viet hoa toan bo.

## 5) Number formatting

- Gia: format ngan cach nghin + don vi (`d`/`sup` theo style hien tai).
- Ty le saleoff: so nguyen + `%`.
- Rating: 1 chu so thap phan.

## 6) Accessibility text rules

- Body line-height >= 1.4.
- Text secondary van dat AA tren background card.
- Khong truyen thong tin sale/hanh dong chi bang mau.

## 7) Trang chủ (`.pageHome`) — tiêu đề & mô tả section

- **Section title** (`.sectionBox_title`): màu `#0f172a`, `letter-spacing: -0.02em`, cỡ trong mixin `titleSection` (clamp nhẹ trên mobile nếu chỉnh sau này).
- **Section desc** (`.sectionBox_desc`): `#64748b`, `line-height` ~1.55–1.6, `max-width` ~42rem để dòng không quá dài trên desktop.
- **Marketing intro tối** (ví dụ khối khách sạn): chữ sáng trên nền tối — tách biệt hierarchy với section sáng; không dùng cùng một gray cho cả nền sáng và nền tối.

Giữ một “giọng” HiTour: tiêu đề rõ, mô tả hỗ trợ quyết định, không khẩu hiệu rỗng.
