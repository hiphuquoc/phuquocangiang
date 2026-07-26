# Photography, Visual Direction, and Motion

## 1) Visual direction for current listings

Listing card (tour/ve) la man hinh ra quyet dinh nhanh, nen image va motion phai phuc vu viec so sanh:
- Anh sang, ro chu de, co diem nhan chinh.
- Badge saleoff va label time/location doc duoc tren anh.
- Khong de image "chiem song" qua muc lam loang hierarchy text-gia-CTA.

## 2) Image standards

### 2.1 Ratio and crop
- Listing image uu tien 750x460 (hoac ratio tuong duong).
- Dung `object-fit: cover`.
- Khong crop mat nguoi/landmark chinh.

### 2.2 Overlay readability
- Neu co badge, dam bao contrast cao voi nen anh.
- Giu khoang cach badge voi mep anh 8-12px.

### 2.3 Performance
- Lazyload cho listing image.
- Co loading placeholder.
- Tranh anh >300KB cho thumbnail listing.

## 3) Motion rules in card UI

- Hover card: translateY nhe (1-2px) + shadow tang nhe.
- Transition tong: 200-300ms, `ease`.
- Khong dung animation lien tuc tren listing cards.

**Trang chủ (`.pageHome`)**: hover card / tile giữ biên độ nhỏ (`translateY` ~3–4px tối đa trên khối lớn); ảnh có thể zoom nhẹ (`scale` ~1.03–1.04) nếu đã có trong SCSS — tránh bounce hoặc parallax liên tục.

## 4) Reduced motion

- Ton trong `prefers-reduced-motion`.
- Neu reduced motion bat, tat transform hover lon.

## 5) QA checklist

- Image khong meo, khong vo ratio.
- Badge khong de len text title khi responsive.
- Hover motion khong gay layout shift.
