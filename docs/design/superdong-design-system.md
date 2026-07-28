# Super Dong — Design System v1.0

> Phạm vi: `superdong.dev` (marketing, listing, booking, trang chủ).  
> Tông: **xanh da trời / bầu trời** — tươi, tin cậy, du lịch biển đảo.  
> SCSS tokens: `resources/sources/superdong/foundations/_tokens.scss` + `_responsive-tokens.scss`  
> Responsive guide: [responsive-tokens.md](./responsive-tokens.md)  
> Prototype: `public/prototype/home-v2/`

---

## 1. Triết lý UX

| Nguyên tắc | Áp dụng |
|------------|---------|
| Rõ ràng trong 3 giây | Tên sản phẩm, giá, CTA đọc được ngay |
| Một hệ màu | Sky blue + trắng + highlight vàng-trắng |
| Nhịp thở | Spacing 4pt, tối đa 3 mức spacing trong 1 component |
| Mobile-first | Breakpoint đồng bộ toàn dự án |
| Tiếng Việt | **Roboto** body, **Be Vietnam Pro** heading — không dùng serif lỗi dấu |

---

## 2. Breakpoints (đồng bộ)

> Chi tiết cascade token: [responsive-tokens.md](./responsive-tokens.md) §2.

| Token | Min-width | Container | Ghi chú |
|-------|-----------|-----------|---------|
| `xs` | — | 100% − gutter | Mobile default |
| `sm` | **568px** | idem | Form 2 cột nhỏ |
| `md` | **769px** | idem | Tablet, trust 4 cột |
| `lg` | **991px** | idem | Grid 2 cột listing |
| `nav` | **1024px** | — | Desktop nav hiện |
| `xl` | **1200px** | max **1240px** | Desktop chuẩn |
| `2xl` | **1440px** | max **1240px** | Wide — không nới container |

SCSS mixin max-width: `@include sd-bp-lg-max { ... }`  
SCSS mixin min-width: `@include sd-bp($sd-bp-md) { ... }`

---

## 3. Màu sắc — Sky palette

### 3.1 Brand sky

| Token | Hex | Vai trò |
|-------|-----|---------|
| `sky-950` | `#0C1929` | Top bar, footer nền sâu |
| `sky-900` | `#0C4A6E` | Hero overlay, section dark |
| `sky-800` | `#075985` | Hover primary |
| `sky-700` | `#0369A1` | Link, icon accent |
| `sky-600` | `#0284C7` | **Primary CTA**, badge brand |
| `sky-500` | `#0EA5E9` | Accent sáng, gradient |
| `sky-400` | `#38BDF8` | Hotline, icon trên dark |
| `sky-100` | `#E0F2FE` | Nền section nhạt |
| `sky-50` | `#F0F9FF` | Page background / wave fill |

### 3.2 Text trên nền tối (hero, dark section)

| Token | Hex | Class | Dùng khi |
|-------|-----|-------|----------|
| Trắng | `#FFFFFF` | `.sd-text--white` | Tiêu đề, mô tả, nav trên hero |
| Highlight | `#FFF9E3` | `.sd-text--highlight` | Từ nhấn, tagline, eyebrow trên ảnh |
| Highlight mạnh | `#FEF08A` | `.sd-text--highlight-strong` | Số thống kê (15+, 50K+, 4.9★) |

### 3.3 Text trên nền sáng

| Token | Hex | Dùng |
|-------|-----|------|
| Heading | `#0F172A` | H1–H3 |
| Body | `#475569` | Đoạn văn |
| Muted | `#64748B` | Meta, caption |
| Giá | `#E11D48` | `price_now` |

### 3.4 CTA phụ (ánh nắng)

| Token | Hex | Vai trò |
|-------|-----|---------|
| `sun-500` | `#FBBF24` | Nút CTA nổi trên hero (Đặt ngay) |
| `sun-600` | `#F59E0B` | Hover |

**Cấm** dùng palette teal/coral cũ (`#0d9488`, `#f97316`) trên UI mới.

---

## 4. Typography

### 4.1 Font stack

```html
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
```

| Vai trò | Font | Weight | Ghi chú |
|---------|------|--------|---------|
| Body, form, meta | **Roboto** | 400, 500, 700 | Bản Google Fonts có subset Vietnamese |
| Heading, CTA, badge | **Be Vietnam Pro** | 600, 700, 800 | Hỗ trợ dấu tiếng Việt tốt |

### 4.2 Scale

| Level | Font | Size (mobile → desktop) | Line-height |
|-------|------|-------------------------|-------------|
| Display (H1 hero) | Be Vietnam Pro 800 | 2.25rem → 3.5rem | 1.08 |
| H2 section | Be Vietnam Pro 700 | 1.75rem → 2.5rem | 1.12 |
| H3 card | Be Vietnam Pro 700 | 1rem → 1.05rem | 1.35 |
| Body | Roboto 400 | 0.95rem → 1rem | 1.65 |
| Small / meta | Roboto 400 | 0.78–0.82rem | 1.4 |
| Eyebrow | Be Vietnam Pro 700 | 0.72rem | 1.2, uppercase, tracking 0.12em |
| Price | Roboto 700 | 1.1–1.15rem | 1.2 |

---

## 5. Spacing (4pt grid)

| Token | px | rem | Dùng |
|-------|-----|-----|------|
| `space-1` | 4 | 0.25 | Chip padding nhỏ |
| `space-2` | 8 | 0.5 | Gap icon-text |
| `space-3` | 12 | 0.75 | Card padding mobile |
| `space-4` | 16 | 1 | Card padding desktop |
| `space-5` | 20 | 1.25 | Section gap nhỏ |
| `space-6` | 24 | 1.5 | Section padding |
| `space-8` | 32 | 2 | Section head margin |
| `space-10` | 40 | 2.5 | Hero padding |
| `space-12` | 48 | 3 | Section vertical |
| `space-16` | 64 | 4 | Section lớn |

**Rule:** tối đa 3 mức spacing khác nhau trong một component.

Section vertical: `padding: clamp(3rem, 6vw, 5rem) 0`.

---

## 6. Radius & shadow

| Token | Value | Dùng |
|-------|-------|------|
| `radius-sm` | 12px | Input, button nhỏ |
| `radius-md` | 18px | Card, tab active |
| `radius-lg` | 24px | Card lớn, booking widget |
| `radius-xl` | 32px | Hero booking, CTA box |
| `radius-pill` | 999px | Badge, tab, segmented nav |
| `shadow-sm` | `0 4px 16px -6px rgba(15,23,42,.12)` | Tab active |
| `shadow-md` | `0 16px 40px -20px rgba(15,23,42,.22)` | Trust bar |
| `shadow-lg` | `0 32px 64px -32px rgba(12,74,110,.28)` | Card hover, booking |

---

## 7. Badge & sign

### 7.1 Badge types

| Class | Nền | Chữ | Vị trí |
|-------|-----|-----|--------|
| `.sd-badge--sale` | `#E11D48` | trắng | Góc phải ảnh card |
| `.sd-badge--brand` | `sky-600` | trắng | Góc trái ảnh |
| `.sd-badge--hero` | `sun-500` | `#0C4A6E` | Trong hero pill |
| `.sd-eyebrow` | `sky-100` + border | `sky-800` | Trên H2 section |

### 7.2 Sign / icon inline

- Icon trước meta: 14–16px, màu `sky-600` trên nền sáng, `sky-400` trên nền tối.
- Segmented nav icon: emoji/SVG 1rem trong capsule pill.

---

## 8. Components chuẩn

### 8.1 Hero shell

- **Ảnh nền:** `config('design.hero_image')` — mặc định hero biển Phú Quốc.
- **Overlay:** gradient sky-950 → sky-900.
- **Viền dưới:** SVG wave (`sd-hero__wave`) fill `sky-50`.
- **Nav:** segmented glass nav (`sd-header__nav`) với active pill rõ trạng thái.

### 8.2 Booking widget

- Tab pill trong nền `slate-100`; active = trắng + shadow-sm.
- Submit: gradient `sky-600` → `sky-800`.

### 8.3 Card listing

- Thumbnail 16:11, badge sale top-right.
- Footer: giá trái, CTA phải.
- Hover: `translateY(-4px)` + shadow-lg.

---

## 9. Checklist khi làm UI mới

- [ ] Dùng token sky, không hard-code hex lẻ
- [ ] Heading Be Vietnam Pro, body Roboto
- [ ] Text trên dark: white / highlight đúng class
- [ ] Breakpoint khớp bảng §2
- [ ] Spacing từ scale §5
- [ ] Badge đúng loại §7
- [ ] Test tiếng Việt có dấu (ữ, ờ, ự…)

---

## 10. Liên kết

- [Layout & spacing chi tiết](./layout-spacing-grid.md)
- [Typography & brand voice](./typography-brand-voice.md)
- [Components patterns](./components-patterns.md)
- [Phu Quoc Travel UX/UI Master Skill](./phu-quoc-travel-ux-ui-master-skill.md)
- [Scope dự án](../scope-single-island.md)
