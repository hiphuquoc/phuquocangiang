# PHU QUOC TRAVEL UX/UI MASTER SKILL

> Phiên bản: v1.0  
> Mục tiêu: biến `superdong.dev` thành **premium travel commerce platform** cho Phú Quốc, không phải website giới thiệu thông thường.  
> Tham chiếu định hướng: mô hình OTA kiểu Mytour / Booking / Agoda / Traveloka (search-first, trust-first, conversion-first).

---

## 1) Core Design DNA

- **Travel Commerce First**: mọi khối đều phục vụ hành động đặt.
- **Destination First**: ưu tiên khám phá Phú Quốc bằng ảnh lớn + card trải nghiệm.
- **Search First**: search/booking là thành phần nổi bật nhất khi mở trang.
- **Conversion First**: CTA rõ, giá rõ, giảm nhiễu nhận thức.
- **Mobile First**: thao tác một tay, tap target lớn, flow ngắn.
- **Trust First**: rating/review/social proof xuất hiện liên tục.
- **Content Driven**: nội dung ngắn, scan nhanh, có thứ tự.
- **Experience Driven**: ảnh thật + hoạt động thật + lịch trình thật.

---

## 2) Product Feeling (bắt buộc)

### Không phải

- Luxury resort website
- Creative agency landing page
- Dribbble/animation showcase

### Phải là

- Premium travel platform
- OTA booking experience
- Destination discovery marketplace

---

## 3) UX Priority Stack

Mọi thành phần phải trả lời được một trong 5 mục tiêu:

1. **Tìm kiếm**
2. **Khám phá**
3. **So sánh**
4. **Tin tưởng**
5. **Đặt ngay**

Nếu một block không hỗ trợ 5 mục tiêu này, cân nhắc bỏ.

---

## 4) Information Hierarchy (không đảo thứ tự)

Mỗi landing page theo flow:

1. **Search**
2. **Popular Destinations**
3. **Best Experiences**
4. **Featured Tours**
5. **Reviews**
6. **Travel Guides**
7. **FAQ**
8. **Booking CTA**

---

## 5) Search & Booking Rules

- Search bar phải là khối dễ thấy nhất trên hero.
- Tab dịch vụ (tàu/tour/khách sạn/combo/vé) phải thao tác trong 1 chạm.
- Trường nhập tối giản, ưu tiên theo hành vi:
  - Điểm đi
  - Điểm đến
  - Ngày
  - Số khách
- CTA đặt phải có độ tương phản cao nhất và luôn trong vùng nhìn đầu tiên.

---

## 6) Card System (xương sống UI)

Mọi card sản phẩm cần có:

- Ảnh lớn
- Tiêu đề rõ nghĩa
- Rating
- Giá
- CTA

Người dùng phải ra quyết định sơ bộ trong ~3 giây.

---

## 7) Trust Design

Trust phải xuất hiện xuyên suốt:

- Rating tổng
- Review thật
- Lượt khách/lượt đặt
- Chính sách đổi/hủy
- Xác nhận tức thì

Không dồn trust vào một section duy nhất.

---

## 8) Visual Direction

- Modern, clean, rounded, airy, premium, fast-scannable.
- Rounded UI + soft shadow + minimal border + large imagery.
- Ocean palette: ocean blue, turquoise, sky blue, white, sand beige.
- Accent chỉ dùng cho **CTA và giá** (sunset orange/yellow).

### Image Direction

- Drone view
- Beach view
- Island landscape
- Real experiences
- Luxury resorts
- Local activities

---

## 9) Motion & Interaction

- Nhanh, nhẹ, có mục đích.
- Reveal/parallax nhẹ để tăng chiều sâu, không gây nhiễu.
- Ưu tiên phản hồi tức thì cho hover/click/tap.
- Tôn trọng `prefers-reduced-motion`.

---

## 10) Mobile UX Rules

- One-hand usage
- Sticky CTA ở vùng dễ chạm
- Bottom interaction cho filter/booking phụ
- Tap target đủ lớn
- Booking flow ngắn và rõ

---

## 11) Mytour-Inspired OTA Patterns (áp dụng có chọn lọc)

- Hero có search module đa dịch vụ dạng tab.
- Deal/promo và trust đặt gần khu vực search.
- Card listing giàu thông tin nhưng scan nhanh.
- Các cụm “điểm đến hot / trải nghiệm nổi bật / review” lặp theo nhịp để tăng chuyển đổi.

Không copy UI 1:1; chỉ học mô hình hành vi và cấu trúc chuyển đổi.

---

## 12) Implementation Checklist

- [ ] Hero = Search + Booking + CTA (first viewport)
- [ ] Section order đúng hierarchy OTA
- [ ] Tất cả card có image + title + rating + price + CTA
- [ ] Trust signal xuất hiện ở nhiều điểm
- [ ] Mobile flow vẫn đặt nhanh trong 1 tay
- [ ] Motion nhẹ, mượt, không trang trí dư thừa
- [ ] Nội dung “thấy → tin → muốn đi → đặt”

