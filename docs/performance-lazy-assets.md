# Tối ưu tải ảnh / nền (lazy) — hàm dùng chung

Tài liệu mô tả cách trì hoãn tải tài nguyên hình ảnh cho UI **ẩn khi load trang** (mega menu, dropdown ngôn ngữ, …), tránh chiếm băng thông và tranh luồng với LCP.

**Nguồn JS:** `resources/views/main/snippets/scripts-default.blade.php` (global, không module bundler).

**Thứ tự script:** Cần đảm bảo `scripts-default` được render **trước** `@stack('scripts-custom')` (hoặc trước partial gọi `hitourHydrateLazySrcImages`), để `window.hitourHydrateLazySrcImages` tồn tại khi menu mở.

---

## 1. Nền CSS (`background-image`) — `data-lazy-bg`

**Khi dùng:** ảnh hiển thị bằng `background-image` trên `span`/`div` (rail Tour VN, hero Tour chung).

**Markup:**

- Không gắn `style="background-image:…"` trong HTML.
- URL đặt tại `data-lazy-bg="{{ e($url) }}"` (hoặc JSON tương đương nếu cần escape đặc biệt).
- Thêm class marker lazy (vd: `js-megaMenuTTBgLazy`) và class placeholder gradient cho đến khi tải xong.

**Hàm:**

| Hàm | Vai trò |
|-----|--------|
| `scheduleHydrateMegaMenuTourTravelLazyBgs()` | Gọi `hydrateMegaMenuTourTravelLazyBgs` ngay (vd. khi đổi tab VN / châu / đảo). Idempotent theo `data-lazy-bg-done` từng ô. |
| `hydrateMegaMenuTourTravelLazyBgs($root)` | jQuery: `$root` mặc định `.megaMenu--tourTravel` và `.megaMenu--hotelTravel`. Dùng `new Image()`, `decoding='async'`, `onload` gán `style.backgroundImage` và gỡ class placeholder. |

**Kích hoạt hiện tại:**

- Hover / `focusin` vào `<li>` header chứa `.megaMenu--tourTravel` hoặc `.megaMenu--hotelTravel`.
- Mỗi lần `openMegaMenuTourTravel(id)` (đổi tab Tour) hoặc `openMegaMenuHotelTravel(id)` (đổi tab Khách sạn).

**Mở rộng:** Có thể gọi `hydrateMegaMenuTourTravelLazyBgs($(container))` với container con nếu tái sử dụng cùng convention `data-lazy-bg` ngoài menu Tour (cần chỉnh selector hoặc tách hàm generic `hydrateLazyBackgrounds(root)` sau này).

---

## 2. Thẻ `<img>` — `data-lazy-src`

**Khi dùng:** Cờ ngôn ngữ (hoặc avatar) trong dropdown đóng; ảnh nhỏ trong panel chỉ hiện sau tương tác.

**Markup:**

```html
<img
  src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
  width="24"
  height="24"
  decoding="async"
  alt="…"
  data-lazy-src="{{ e($realUrl) }}"
/>
```

- `src` ban đầu: GIF trong suốt 1×1 (nhẹ, không request mạng).
- **Không** dùng đồng thời `loading="lazy"` nếu đã chủ động hydrate khi mở — tránh hai cơ chế chồng nhau.

**Hàm:**

| API | Vai trò |
|-----|--------|
| `hydrateLazySrcImages(root)` | Vanilla: `root` là `Element` hoặc `document`. Mọi `img[data-lazy-src]` trong `root` được gán `src` một lần (`data-lazy-src-done`). |
| `window.hitourHydrateLazySrcImages` | Alias global để gọi từ `@push('scripts-custom')` / partial khác. |

**Ví dụ Region switcher:** Cờ trên nút trigger dùng `data-lazy-src`; khi bind mỗi `[data-region-switcher]`, gọi `hitourHydrateLazySrcImages(trigger)` (chỉ subtree trigger). Cờ trong menu đóng vẫn hydrate khi mở (`hitourHydrateLazySrcImages(menu)`).

**Mở rộng:** Bất kỳ dropdown nào: khi mở, `hitourHydrateLazySrcImages(panelElement)`.

---

## 3. Nguyên tắc nhanh

1. **Cờ region switcher (trigger):** `data-lazy-src` + hydrate ngay sau khi bind (`hitourHydrateLazySrcImages(trigger)`), không tải SVG cùng lúc parse HTML.
2. **Ảnh trong menu đóng:** `data-lazy-src` + hydrate khi mở; hoặc `data-lazy-bg` + hydrate ngay khi panel mở (hover/focus), không trì hoãn bằng idle nếu người dùng đã thấy placeholder.
3. **Sau khi hydrate:** Có thể `removeAttribute('data-lazy-src')` để DOM gọn (đã làm trong `hydrateLazySrcImages`).
4. **Lỗi tải (bg):** `data-lazy-bg-error` và reset `data-lazy-bg-done` để có thể retry thủ công sau này nếu cần.

---

## 4. Checklist khi thêm UI mới

- [ ] Ảnh có thấy ngay khi load trang không? → Nếu không, lazy.
- [ ] Đã gọi hydrate đúng thời điểm **mở** panel / tab?
- [ ] Đã thêm `width`/`height` hoặc CSS cố định để tránh CLS?
- [ ] `alt` / `aria-label` vẫn đúng khi `src` là placeholder?
