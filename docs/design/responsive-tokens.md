# Responsive Tokens & Spacing System

> **Nguồn triển khai:** `resources/sources/superdong/foundations/_responsive-tokens.scss`  
> **Đồng bộ pattern:** vitravel.dev `docs/04-design-system.md` §3 (đã triển khai 2026-07)  
> **Phạm vi:** Toàn bộ stack v2 (`body.sd-home-v2`, `resources/views/superdong/`, `resources/views/main/*-v2/`)

---

## 1. Tổng quan

Super Dong dùng **SCSS + CSS custom properties** thay vì Tailwind. Hệ responsive gồm:

1. **5 mốc `max-width` cố định** — cascade token qua `:root`
2. **Mixin SCSS** — `@include sd-bp-xl-max` … `sd-bp-sm-max` cho component logic
3. **Utility class** — `.sd-gap`, `.sd-stack`, `.sd-card-body`, `.sd-body-text` …
4. **Section follower** — `.sd-section + .sd-section { padding-top: 0 }` (tránh gap gấp đôi)

**Không thêm mốc ad-hoc** (`640px`, `600px`, `900px`…) — mọi media query mới phải map vào 1 trong 5 mốc dưới.

---

## 2. Breakpoint ladder (bắt buộc)

| Mốc | `max-width` | SCSS mixin max | SCSS min-width tương ứng | Vai trò |
|---|---|---|---|---|
| **BP-XL** | `1199px` | `sd-bp-xl-max` | `≥1200px` (`$sd-bp-xl`) | Laptop nhỏ — thu gutter, H2 giảm 1 bậc |
| **BP-LG** | `1023px` | `sd-bp-lg-max` | `≥1024px` (`$sd-bp-nav`) | Ẩn mobile nav; grid 2 cột; ship-row stack |
| **BP-MD+** | `990px` | `sd-bp-md-plus-max` | `≥991px` (`$sd-bp-lg`) | Form/split 2 cột → 1 cột |
| **BP-MD** | `768px` | `sd-bp-md-max` | `≥769px` (`$sd-bp-md`) | Typography mobile; FAB icon-only |
| **BP-SM** | `567px` | `sd-bp-sm-max` | `≥568px` (`$sd-bp-sm`) | Gutter tối thiểu; hero/section compact |

**Desktop nav:** hiện tại `≥1024px` (`$sd-bp-nav`) — khớp vitravel BP-LG.

---

## 3. Spacing scale (responsive)

### 3.1 Gutter khung

| Token | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 |
|---|---|---|---|---|---|---|
| `--sd-space-gutter` | **2rem** | **1.25rem** | **1rem** | **1rem** | **0.75rem** | **0.75rem** |

Dùng qua `@include sd-container` (padding-inline) hoặc `.sd-pad-x`.

### 3.2 Gap / stack / section

| Token | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 | Dùng cho |
|---|---|---|---|---|---|---|---|
| `--sd-space-gap-sm` | 0.75rem | 0.7rem | 0.65rem | 0.6rem | 0.5rem | 0.5rem | Meta row, chip |
| `--sd-space-gap` | 1.25rem | 1.1rem | 1rem | 0.95rem | 0.85rem | 0.75rem | Product grid |
| `--sd-space-gap-lg` | 2rem | 1.75rem | 1.5rem | 1.35rem | 1.15rem | 1rem | Split layout, footer |
| `--sd-space-stack` | 1.5rem | 1.25rem | 1.15rem | 1.05rem | 0.9rem | 0.8rem | Stack dọc trong section |
| `--sd-space-stack-lg` | 2.5rem | 2.15rem | 1.75rem | 1.55rem | 1.25rem | 1.1rem | Khối lớn |
| `--sd-space-heading-mb` | 2.25rem | 2rem | 1.65rem | 1.45rem | 1.15rem | 1rem | `.sd-section-head` |
| `--sd-space-section-y` | 4.5rem | 4rem | 3.25rem | 2.85rem | 2.15rem | 1.75rem | `.sd-section` |
| `--sd-space-section-y-sm` | 3rem | 2.75rem | 2.15rem | 1.85rem | 1.45rem | 1.2rem | Section nhỏ |
| `--sd-space-section-feature` | 5rem | 4.5rem | 4rem | 3.5rem | 3rem | 2.5rem | `.sd-section--feature` |

### 3.3 Card inner

| Token | Dùng cho |
|---|---|
| `--sd-space-card` | `.sd-card-body`, `.sd-card__body`, `.sd-pad` |
| `--sd-space-card-stack` | Gap title → meta → body (`.sd-card-inner`) |
| `--sd-space-card-stack-lg` | Footer card, CTA row |

### 3.4 Layout đặc thù Super Dong

| Token | Vai trò |
|---|---|
| `--sd-hero-pad-bottom` | `.sd-hero__body` padding-bottom |
| `--sd-trust-overlap` | `.sd-trust` margin-top (đè hero) |
| `--sd-trust-gap` | Trust grid gap desktop |
| `--sd-split-gap` | Hero/booking split, tour-location |
| `--sd-footer-pad-top` | Footer overlap CTA |

### 3.5 Buttons

| Token | Vai trò |
|---|---|
| `--sd-btn-pad-x` / `--sd-btn-pad-y` | `.sd-btn` primary/outline |
| `--sd-btn-pad-x-sm` / `--sd-btn-pad-y-sm` | CTA gọn header/card |
| `--sd-btn-gap` | Icon ↔ chữ |
| `--sd-space-hit` | `min-height` CTA (≥44px mobile) |

---

## 4. Typography scale (responsive)

Base `html`: `--sd-fs-html: 100%` (16px). Body map `--sd-fs-body`.

| Token | ≥1200 | ≤1199 | ≤1023 | ≤990 | ≤768 | ≤567 |
|---|---|---|---|---|---|---|
| `--sd-fs-body` | 1rem | 0.9875rem | 0.96875rem | 0.9375rem | 0.90625rem | **0.875rem** |
| `--sd-fs-meta` | 0.875rem | ↓ | ↓ | 0.8125rem | ↓ | 0.78125rem |
| `--sd-fs-kicker` | 0.75rem | ↓ | ↓ | 0.6875rem | ↓ | 0.65625rem |
| `--sd-lh-body` | 1.6 | 1.6 | 1.58 | 1.55 | 1.52 | 1.5 |
| `--sd-fs-section-title` | clamp | ↓ cascade | ↓ | ↓ | 1.75rem | 1.6rem |
| `--sd-fs-hero` | clamp 3.5rem | ↓ | ↓ | ↓ | ↓ | compact |

### Quy tắc typography

1. **Nội dung đọc chính** → `.sd-body-text` hoặc `@include sd-type-body` — **không** hạ xuống meta size.
2. **Meta / badge / label** → `var(--sd-fs-meta)` hoặc `@include sd-type-meta`.
3. **Tiêu đề section** → `var(--sd-fs-section-title)` qua `.sd-section-head__title` hoặc `@include sd-type-h2-section`.
4. **Tiêu đề card** → `var(--sd-fs-card-title)` qua `@include sd-type-card-title`.
5. **Hero H1** → `var(--sd-fs-hero)`.

---

## 5. Class & mixin dùng chung

### 5.1 Layout

| Class / mixin | Vai trò |
|---|---|
| `.sd-section` | Padding block `--sd-space-section-y`; follower bỏ padding-top |
| `.sd-section--spaced-top` | Khôi phục padding-top khi cần tách rộng |
| `.sd-section--feature` | Padding `--sd-space-section-feature` |
| `.sd-section__inner` | `@include sd-container` |
| `.sd-product-grid` | Grid + gap token |
| `@include sd-container` | Max-width + gutter |
| `@include sd-section-padding` | Block Y theo token |

### 5.2 Spacing utilities

| Class | Token |
|---|---|
| `.sd-gap` / `.sd-gap-sm` / `.sd-gap-lg` | `--sd-space-gap*` |
| `.sd-stack` / `.sd-stack-lg` | `--sd-space-stack*` |
| `.sd-mt` / `.sd-mt-lg` / `.sd-mb` | margin block |
| `.sd-pad` / `.sd-pad-x` / `.sd-pad-y` | padding khung |

### 5.3 Card inner

| Class | Token |
|---|---|
| `.sd-card-body` | `--sd-space-card` |
| `.sd-card-inner` | stack `--sd-space-card-stack` |
| `.sd-card-meta-row` | gap sm + default |
| `.sd-card-footer` / `.sd-card-footer-row` | `--sd-space-card-stack-lg` |

### 5.4 Typography

| Class / mixin | Vai trò |
|---|---|
| `.sd-body-text` | Body chính trong card/box |
| `.sd-item-title` | Tiêu đề cấp item (sans bold) |
| `@include sd-type-h2-section` | H2 section |
| `@include sd-type-body-section` | Mô tả dưới H2 |
| `@include sd-type-card-title` | H3 card |
| `@include sd-type-meta` | Meta, caption |

---

## 6. Section liền nhau (§3.5 vitravel)

```scss
.sd-section + .sd-section {
  border-top: 1px solid rgba($sd-slate-900, 0.06);
  padding-top: 0;
}
```

Khoảng cách giữa hai section = `padding-bottom` của section trước (một lần `--sd-space-section-y`).

Cần **tách rộng hơn**: thêm `.sd-section--spaced-top` lên section sau.

---

## 7. Mapping trang v2 — đã rà soát

| Trang / component | File | Class chính |
|---|---|---|
| Home | `main/home-v2/` | `.sd-section`, hero, trust, quick, tours grid |
| Hotel / Tour / Ship / Service detail | `main/*-v2/` | `.sd-section`, product-detail, gallery |
| Blog / Guide | `main/blog-v2/`, `guide-v2/` | `.sd-section`, blog SCSS |
| Category / Location listing | `main/category-v2/`, `*Location-v2/` | `.sd-product-grid`, tour-location |
| Chrome | `superdong/chrome/` | header, footer, topbar, float |
| Cards | `superdong/ui/cards/` | `.sd-card`, `.sd-card__body` |
| Section head | `superdong/ui/section-head` | `.sd-section-head` |
| Form / booking | `superdong/form/`, booking widget | form-controls, booking |
| FAQ / Reviews / Gallery | `superdong/sections/` | faq, reviews, gallery SCSS |

---

## 8. Checklist khi build trang mới

1. Section wrapper: `.sd-section` + `.sd-section__inner`
2. Grid: `.sd-product-grid` + `.sd-gap-lg` — không hardcode `gap: 1.25rem`
3. Stack dọc: `.sd-stack` — không `margin-top` ad-hoc lặp lại
4. Card: `.sd-card-body` hoặc `.sd-card__body` với `var(--sd-space-card)`
5. Body copy: `.sd-body-text`; meta: `@include sd-type-meta`
6. CTA: `.sd-btn` — padding từ `--sd-btn-pad-*`
7. Section heading: `x-superdong.ui.section-head` (margin `--sd-space-heading-mb`)
8. Media query chỉ tại 5 mốc §2
9. Section liền nhau: CSS §6 tự xử lý

---

## 9. Quy trình rà soát (grep)

```bash
# Tìm hardcode spacing cần thay
rg "padding:\s*[0-9]|margin-top:\s*[0-9]|gap:\s*[0-9]" resources/sources/superdong/

# Tìm breakpoint ad-hoc
rg "max-width:\s*(640|600|900|1100)px" resources/sources/superdong/
```

Map kết quả vào bảng §3–4 hoặc tạo class component mới — không inline spacing lặp lại.

---

## 10. Nhật ký triển khai (2026-07-28)

| Hạng mục | Chi tiết |
|---|---|
| Foundation | `_responsive-tokens.scss` — 5 breakpoint cascade |
| Mixins | `_tokens.scss` — map layout rhythm → CSS vars; `sd-bp-*-max` mixins |
| Base | `_base.scss` — `html`/`body` dùng `--sd-fs-*` |
| Layout | `_layout.scss` — section follower `padding-top: 0` |
| Utilities | `_utilities.scss` — `.sd-gap`, `.sd-stack`, `.sd-card-*`, `.sd-body-text` |
| Components | buttons, cards, section-head, hero — token hóa padding/typography |
| Breakpoints | Chuẩn hóa 640/600/1024 ad-hoc → mixin canonical |
| Docs | File này + cập nhật `layout-spacing-grid.md`, `superdong-design-system.md` |

**Legacy stack** (`resources/sources/main/style.scss`, views không `-v2`): chưa token hóa — migrate dần theo `implementation-roadmap.md` Phase 1–2.
