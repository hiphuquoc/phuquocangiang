# Hitour.dev — Tài liệu kiến trúc hệ thống & đa ngôn ngữ chuẩn SEO

> Phiên bản: **3.1** (cập nhật sau Phase 2.5: REUSE FORM ADMIN GỐC cho trang dịch + Magic locale-aware accessor)
> Phạm vi: Toàn bộ hệ thống `hitour.dev` (Laravel 9 + MySQL).
> Mục tiêu: Mỗi ngôn ngữ là **1 trang đầy đủ nội dung dịch**, có **link relational giữa các phiên bản**.

---

## Mục lục

0. [TÓM TẮT V3.0 — Kiến trúc đa ngôn ngữ DEEP TRANSLATIONS](#0-tóm-tắt-v30--kiến-trúc-đa-ngôn-ngữ-deep-translations)
1. [Tổng quan hệ thống hiện tại](#1-tổng-quan-hệ-thống-hiện-tại)
2. [Phân tích chi tiết các loại trang](#2-phân-tích-chi-tiết-các-loại-trang)
3. [Đánh giá hệ thống hiện tại & Trạng thái Phase 0](#3-đánh-giá-hệ-thống-hiện-tại--trạng-thái-phase-0)
4. [Thiết kế lại hệ thống đa ngôn ngữ](#4-thiết-kế-lại-hệ-thống-đa-ngôn-ngữ)
5. [Kế hoạch nâng cấp & Migration](#5-kế-hoạch-nâng-cấp--migration)
6. [Best Practices & các lỗi thường gặp](#6-best-practices--các-lỗi-thường-gặp)
7. [Phụ lục](#7-phụ-lục)

---

## 0. TÓM TẮT V3.1 — Reuse FORM ADMIN GỐC cho trang dịch + Magic locale-aware

### 0.0. Khác biệt V3.1 so với V3.0

V3.0 đã xây xong **backend deep translations** (12 bảng `_translations` mới, `seo_content_translations`, controller dịch riêng). V3.0 admin UI dùng **form custom rút gọn** trong `admin/translation/edit.blade.php` — gây feedback từ user: "form không giống trang gốc, các trường không cần dịch không hiển thị, khó check tham chiếu".

V3.1 **giải quyết toàn bộ** bằng cách **REUSE form admin gốc 100%** cho trang dịch:

| Khía cạnh                | V3.0                                              | V3.1                                                     |
| ------------------------ | ------------------------------------------------- | -------------------------------------------------------- |
| UI trang dịch            | Custom rút gọn (fields nested namespace)          | Reuse form gốc 100% (admin.tour.view, admin.ship.view, …) |
| Cách lấy giá trị bản dịch | Server query `<entity>_translations` rồi pass vào view | Magic accessor `HasTranslations::getAttribute()` tự return |
| Disable trường non-dịch   | View không render                                  | JS auto-disable trong browser, vẫn show value gốc        |
| Cấu hình relations       | Có `translation_relations`                         | Mở rộng thêm `input_layout`, `input_array_name`, `input_field_aliases` để parse form gốc |
| Body content             | Textarea trong form custom                         | JS inject card vào form gốc (sau khi load)               |
| Thay đổi form gốc        | Không                                              | Inject hidden id row vào formTimetable + formAnswer      |

### 0.1. Triết lý thiết kế

> **Mỗi ngôn ngữ là một “trang” đầy đủ nội dung dịch riêng, các phiên bản ngôn ngữ của cùng 1 entity được link với nhau qua `seo_id` chung.**

Nguyên lý cốt lõi:

1. **Một entity gốc — N bản dịch**. Bảng entity (`tour_info`, `ship_info`, `hotel_info`, `combo_info`, …) chỉ có **1 row/entity** chứa các field **không đổi theo ngôn ngữ** (giá, ngày, FK, ảnh, status). Mọi field text dịch được nằm ở `<table>_translations` (1 row/locale).
2. **Deep translations cho relations**. Không chỉ entity gốc, MỌI relation table chứa text (`tour_content`, `tour_timetable`, `tour_option`, `combo_content`, `hotel_content`, `question_answer_info`, …) đều có bảng `<table>_translations` riêng.
3. **Body content vào DB**. Cơ chế file blade legacy (`storage/app/public/contents/<type>/<slug>.blade.php`) được thay bằng bảng `seo_content_translations(seo_id, language_id, content)`. File legacy vẫn được fallback đọc tự động.
4. **Trang dịch riêng cho từng ngôn ngữ** trong admin. Trang admin gốc (`/he-thong/<entity>/view?id=…`) chỉ chỉnh **default locale + master data**. Để dịch sang ngôn ngữ khác, click nút **“Dịch sang …”** trong panel “Phiên bản dịch” → mở trang `/he-thong/translation/{locale}/{seoId}` chứa MỌI field dịch được (SEO + entity + body + relations).
5. **Frontend tự locale-aware**. `RoutingController` đã sẵn sàng query qua `seo_translations` theo locale; magic accessor `trans()` trên trait `HasTranslations` lấy đúng bản dịch + fallback default.

### 0.2. Sơ đồ ngắn

```
┌──────────────────────────────────────────────────────────────────────┐
│                            ENTITY GROUP                              │
│                  (Logical "Tour Phú Quốc 3N2Đ")                      │
│                                                                      │
│  seo (id=2316, type=tour_info, parent, image, ratings, ordering...)  │
│      │                                                                │
│      ├── seo_translations  ──┬─ vi ─ title, description, slug, …    │
│      │                       ├─ en ─ ...                             │
│      │                       └─ ja ─ ...                             │
│      │                                                                │
│      ├── seo_content_translations ─┬─ vi ─ body HTML/Blade          │
│      │                              └─ en ─ ...                       │
│      │                                                                │
│      └── tour_info (id=211, price, days, departure_id, partner_id)   │
│            │                                                          │
│            ├── tour_info_translations ─┬─ vi ─ name, pick_up,…       │
│            │                            └─ en ─ ...                   │
│            │                                                          │
│            ├── tour_content (id=87, FK=211)                          │
│            │     └── tour_content_translations ─┬─ vi ─ include,…   │
│            │                                     └─ en ─ ...          │
│            │                                                          │
│            ├── tour_timetable (id=400…406, FK=211, 7 rows)           │
│            │     └── tour_timetable_translations ─┬─ vi              │
│            │                                       └─ en              │
│            │                                                          │
│            ├── tour_option (id=180…182, FK=211, 3 rows)              │
│            │     └── tour_option_translations ─┬─ vi                 │
│            │                                    └─ en                 │
│            │                                                          │
│            ├── tour_price (FK=tour_option_id, KHÔNG TRANSLATE)       │
│            │                                                          │
│            └── question_answer_info (FK=reference_id, FAQ)           │
│                  └── question_answer_info_translations               │
└──────────────────────────────────────────────────────────────────────┘

Frontend URL:
   /tour-du-lich-phu-quoc/...      (default locale - vi)
   /en/tour-du-lich-phu-quoc/...   (English)
   /ja/tour-du-lich-phu-quoc/...   (日本語)
   ↑ link với nhau qua seo_id, switcher tự dùng seo_alternates()

Admin:
   /he-thong/tour/view?id=211                   (chỉnh sửa master + default locale)
       └── panel "Phiên bản dịch": list 10 ngôn ngữ + status + nút "Dịch sang"
   /he-thong/translation/en/2316                (trang dịch riêng cho EN)
       chứa: SEO(en) + entity_fields(en) + body(en) + content(en) + timetables(en) + options(en) + FAQ(en)
```

### 0.3. Lịch sử các Phase

| Phase | Trạng thái | Phạm vi |
|---|---|---|
| **Phase 0** | ✅ Done | Hardening đơn ngữ: HtmlCacheService, CheckRedirect middleware, refactor RoutingController, `tablemysql.php` config, DB indexes |
| **Phase 1** | ✅ Done | Multilingual core (V2.0): tables `languages` + `seo_translations` + 21 `<entity>_translations`, middleware `DetectLocale`, helpers `seo_alternates`, hreflang + canonical |
| **Phase 2** | ✅ Done | **V3.0 Deep Translations** (xem §0.4) |
| Phase 3+ | (planned) | Auto-translate API, content review workflow, A/B test theo locale, AI-assisted translation pipeline |

### 0.4. Phase 2 — Những gì V3.0 thêm vào Phase 1

| Thành phần | Phase 1 (V2.0) | Phase 2 (V3.0) |
|---|---|---|
| Entity translation | ✅ `<entity>_translations` cho 21 model gốc | ✅ Giữ nguyên |
| **Relation translation** | ❌ Không dịch tour_content, tour_timetable, tour_option, FAQ, … | ✅ **11 bảng `<rel>_translations` mới + models + trait** (xem §0.5) |
| **Body content** | File `<slug>.<locale>.blade.php` | ✅ DB `seo_content_translations` (file legacy vẫn fallback đọc) |
| Admin UI dịch | Tabs đa ngôn ngữ NHÚNG TRONG form chính (chỉ SEO) | ✅ **Trang dịch RIÊNG cho từng locale** (`/he-thong/translation/{locale}/{seoId}`) — chứa MỌI field dịch được |
| Panel hiển thị status | ❌ | ✅ Snippet `translationStatusPanel.blade.php` — list 10 ngôn ngữ + badge SEO/Body status + nút “Dịch sang …” |
| Sync default locale | Manual | ✅ Auto-sync `seo_translations[default]` mỗi lần `Seo::saved` |
| Frontend body content | File-only | ✅ DB-first → file fallback, locale-aware |
| Breadcrumb fallback | 500 nếu thiếu translation 1 segment | ✅ Fallback default locale từng segment, không 500 |

### 0.5. Translation tables thêm trong V3.0

| Bảng dịch | Bảng gốc | Field dịch |
|---|---|---|
| `tour_content_translations` | `tour_content` | special_content, special_list, include, not_include, policy_child, menu, hotel, policy_cancel, note |
| `tour_timetable_translations` | `tour_timetable` | title, content, content_sort |
| `tour_option_translations` | `tour_option` | name |
| `tour_content_foreign_translations` | `tour_content_foreign` | (giống tour_content) |
| `tour_timetable_foreign_translations` | `tour_timetable_foreign` | title, content, content_sort |
| `tour_option_foreign_translations` | `tour_option_foreign` | option |
| `hotel_content_translations` | `hotel_content` | name, content |
| `combo_content_translations` | `combo_content` | name, content |
| `combo_option_translations` | `combo_option` | name |
| `service_option_translations` | `service_option` | name |
| `question_answer_info_translations` | `question_answer_info` | question, answer (FAQ chung) |
| `seo_content_translations` | (mới) | content longtext — body trang per locale |

Migration: `database/migrations/2026_05_05_150000_create_extended_translation_tables.php`. Idempotent, có backfill default locale từ master + import 1500+ blade file content vào `seo_content_translations`.

### 0.6. Files V3.0 thêm/sửa

```
[ MIGRATIONS ]
  + database/migrations/2026_05_05_150000_create_extended_translation_tables.php

[ MODELS ]
  + app/Models/TourContentTranslation.php
  + app/Models/TourTimetableTranslation.php
  + app/Models/TourOptionTranslation.php
  + app/Models/TourContentForeignTranslation.php
  + app/Models/TourTimetableForeignTranslation.php
  + app/Models/TourOptionForeignTranslation.php
  + app/Models/HotelContentTranslation.php
  + app/Models/ComboContentTranslation.php
  + app/Models/ComboOptionTranslation.php
  + app/Models/ServiceOptionTranslation.php
  + app/Models/QuestionAnswerTranslation.php
  + app/Models/SeoContentTranslation.php
  ~ app/Models/TourContent.php           (apply HasTranslations trait)
  ~ app/Models/TourTimetable.php         (idem)
  ~ app/Models/TourOption.php            (idem)
  ~ app/Models/TourContentForeign.php    (idem)
  ~ app/Models/TourTimetableForeign.php  (idem)
  ~ app/Models/TourOptionForeign.php     (idem)
  ~ app/Models/HotelContent.php          (idem)
  ~ app/Models/ComboContent.php          (idem)
  ~ app/Models/ComboOption.php           (idem)
  ~ app/Models/ServiceOption.php         (idem)
  ~ app/Models/QuestionAnswer.php        (idem)

[ CONTROLLERS ]
  + app/Http/Controllers/AdminTranslationController.php   (3 actions: edit, save, delete)
  ~ app/Http/Controllers/RoutingController.php
        - protected dispatch() (sửa visibility)
        - renderContentBlade() đọc DB trước, file legacy sau

[ VIEWS ]
  + resources/views/admin/translation/edit.blade.php       (form dịch riêng)
  + resources/views/admin/snippets/translationStatusPanel.blade.php
  ~ resources/views/admin/form/formSeo.blade.php           (gắn translationStatusPanel, bỏ tabs cũ)
  ~ resources/views/admin/tour/view.blade.php              (bỏ duplicate inclusion cũ)
  ~ resources/views/main/schema/breadcrumb.blade.php       (guard $data null)

[ CONFIG ]
  ~ config/tablemysql.php                                  (thêm `translation_relations` cho 5 entity types: tour_info, tour_info_foreign, service_info, combo_info, hotel_info)

[ ROUTES ]
  ~ routes/web.php                                         (thêm 3 route admin.translation.*)

[ PROVIDERS ]
  ~ app/Providers/AppServiceProvider.php                   (thêm bootDefaultLocaleSeoSync)

[ HELPERS ]
  ~ app/Helpers/Url.php                                    (buildBreadcrumb fallback default locale)
```

### 0.6.0. Hotfix UI/UX (sau V3.1 release)

| Issue                                                            | Fix                                                                                              |
| ---------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Trang gốc còn box "Phiên bản dịch" trong card SEO (duplicate)    | Bỏ panel cũ trong `formSeo.blade.php`. Chỉ còn 1 nguồn duy nhất là banner đầu trang.             |
| Trang gốc CHƯA có UI switcher ngôn ngữ                            | Tạo `translationOriginBanner.blade.php` (XANH) + View Composer auto-inject context vào layout.   |
| Banner: cờ ngôn ngữ render thành **text path** `/images/flags/vi.svg` | `flag` trong DB là path SVG, không phải emoji. Render qua helper `<img src="...">` thay vì `{!! $lang->flag !!}`. |
| Banner: tràn hàng, vỡ giao diện trên màn hình hẹp                 | CSS responsive: flex-wrap nội bộ, `min-width:0` cho text container, ẩn label switcher ≤ 768px.   |
| Translation mode CSS rò rỉ ra mọi trang                          | Selector đổi `[data-translatable=...]` → `body.translation-mode [data-translatable=...]`.        |

**Origin Banner (XANH)** ở đầu trang gốc hiển thị:
- Cờ + tên ngôn ngữ hiện tại (default = VI)
- Counter "Bản dịch các ngôn ngữ khác: X/Y đã có nội dung"
- Switcher với mọi ngôn ngữ active: cờ + code + dấu ✓ nếu đã có bản dịch
- Click vào ngôn ngữ khác → chuyển sang trang dịch (admin.translation.edit)

**Mode Banner (CAM)** ở đầu trang dịch hiển thị:
- Cờ + tên ngôn ngữ đang dịch
- Legend: "tô vàng" = dịch được / "tô xám" = bản gốc khoá
- Switcher giống origin (dùng để chuyển nhanh giữa các locale)
- Nút "Bản gốc" để quay về trang admin gốc

Auto-inject context qua **View Composer** trong `AppServiceProvider::bootTranslationOriginContext()`:
- Hook vào view `admin.layouts.main`
- Tra route name → seo.type theo `AppServiceProvider::routeNameToSeoType()`
- Lookup entity + seo, đếm `seo_translations` để biết ngôn ngữ nào đã dịch
- Share biến `$translationOriginSeo`, `$translationOriginCurrent`, `$translationOriginStatus`, ...

### 0.6.1. Files V3.1 thêm/sửa (Phase 2.5 — Reuse form gốc)

```
[ TRAIT ]
  ~ app/Models/Concerns/HasTranslations.php
        + getAttribute() override: tự return giá trị bản dịch khi locale != default
        + __cachedTranslation() request-scope cache
        + bypass via app()->bind('translation.bypass', fn() => true)

[ CONTROLLER ]
  ~ app/Http/Controllers/AdminTranslationController.php
        + $TYPE_VIEW_MAP[seo.type] = [ControllerClass, action]   (29 entity types)
        + edit(): app()->setLocale + view()->share + delegate sang controller gốc
        + save(): parse form gốc theo translation_relations.input_layout
            - persistSeoTranslation()        (top-level title/description/seo_title/seo_description/slug/link_canonical)
            - persistEntityTranslation()     (translatable + alias title→name)
            - persistBodyContent()           (textarea body_content_translation)
            - persistAllRelations()          (top_level / array / ajax)
            - persistRelTopLevel()           (hasOne, fields ngay ở root POST)
            - persistRelArray()              (hasMany, nested array với input_id_alias)
        + buildTranslatableInputWhitelist() — gom field name cho JS auto-disable

[ CONFIG ]
  ~ config/tablemysql.php
        ~ tour_info.translation_relations.* — bổ sung input_layout/input_array_name/
          input_id_alias/input_field_aliases cho content/timetables/options/faqs
        ~ tour_info.translatable_input_aliases = ['name' => 'title']  (form gốc không có ô name)
        ~ tour_info_foreign.translatable_input_aliases (idem)

[ VIEWS / FORM PARTIALS ]
  ~ resources/views/admin/layouts/main.blade.php
        + Detect $translationMode → render banner snippet + script globals + load CSS/JS
  + resources/views/admin/snippets/translationModeBanner.blade.php
        Banner cam trên đầu trang dịch + locale switcher + nút "Bản gốc"
  ~ resources/views/admin/form/formSeo.blade.php
        Hide translationStatusPanel khi $translationMode = true (tránh duplicate banner)
  ~ resources/views/admin/tour/formTimetable.blade.php
        + Hidden <input name="tour_timetable_id"> (để translation save map row → translation)
  ~ resources/views/admin/form/formAnswer.blade.php
        + Hidden <input name="question_answer[X][question_answer_info_id]"> (idem)

[ ASSETS ]
  + public/css/admin/translation-mode.css
        Banner styles, viền cam highlight cho translatable inputs, viền đứt xám cho disabled
  + public/js/admin/translation-mode.js
        + Override form.action → admin.translation.save URL
        + Auto-disable inputs có name không trong window.TRANSLATABLE_INPUTS
        + Inject hidden _translation_mode + _translation_locale markers
        + Visual mark inputs (data-translatable=1)
        + Hide nút data-repeater-create / data-repeater-delete (block thêm/xoá row master)
        + Đổi label submit button thành "Lưu bản dịch <LOCALE>"
        + Inject card "Nội dung trang dạng HTML — bản dịch" với textarea body_content_translation
```

### 0.6.2. Flow request V3.1

```
                          ┌─ User click "Dịch sang EN" trên trang admin gốc ─┐
                          ▼                                                   │
GET /he-thong/translation/en/2063 → AdminTranslationController::edit()        │
   │                                                                          │
   ├─ resolve(seo, entity, cfg, language)                                     │
   ├─ app()->setLocale('en')   ← bật magic accessor cho HasTranslations       │
   ├─ load body content (DB) cho default + en                                 │
   ├─ view()->share([translationMode, translatableInputs, body content, ...]) │
   └─ delegate → AdminTourController::view($delegateRequest)                  │
        │                                                                     │
        ├─ load $tour, $partners, $locations, $staffs (như HTTP gốc)          │
        ├─ render view('admin.tour.view', compact($item, $type, ...))         │
        │     │                                                               │
        │     ├─ admin.layouts.main detect $translationMode                   │
        │     │     ├─ Render translationModeBanner ở top                     │
        │     │     ├─ Inject <script> với window.TRANSLATABLE_INPUTS=[...]  │
        │     │     └─ Load translation-mode.css + .js                       │
        │     │                                                               │
        │     ├─ admin.tour.formPage / formInfo / formTimetable / formAnswer / formSeo
        │     │     ↑ TẤT CẢ DÙNG `$item->X`, `$item->seo->X`, `$timetable->title` …
        │     │     ↑ Magic accessor tự return bản dịch EN (fallback VI nếu rỗng)
        │     │                                                               │
        │     └─ Hidden ID inputs (formTimetable, formAnswer) cho server map  │
        │                                                                     │
        └─ Trả View instance → HTTP response                                  │
                                                                              ▼
                          ▶ Browser load page, JS chạy:                       
                          ▶  - form#formAction.action = /he-thong/translation/en/2063
                          ▶  - input matching whitelist → data-translatable=1 (highlight)
                          ▶  - input outside whitelist → disabled (giữ value bản gốc)
                          ▶  - Inject card body_content_translation
                          ▶  - Đổi label "Lưu" → "Lưu bản dịch EN"

POST /he-thong/translation/en/2063 → AdminTranslationController::save()
   │
   ├─ DB::transaction(...)
   │     ├─ persistSeoTranslation()    — title/description/seo_title/seo_description/slug → seo_translations(en)
   │     ├─ persistEntityTranslation() — pick_up/transport (+ name←title) → tour_translations(en)
   │     ├─ persistBodyContent()       — body_content_translation → seo_content_translations(en)
   │     └─ persistAllRelations() per cfg.translation_relations:
   │           ├─ content (top_level, hasOne) → tour_content_translations(en)
   │           ├─ timetables (array) → loop timetable[X][...] → tour_timetable_translations(en)
   │           ├─ options (ajax) → SKIP (modal flow handle riêng — TODO)
   │           └─ faqs (array) → loop question_answer[X][...] → question_answer_info_translations(en)
   │
   └─ Redirect back với flash success
```

### 0.6.3. Smoke test V3.1 (được verify)

```
✓ Routes load: admin.translation.{edit,save,delete}
✓ GET /he-thong/translation/en/2063 → render View instance "admin.tour.view"
  ✓ Banner snippet rendered (translationBanner)
  ✓ JS globals injected (TRANSLATION_MODE = true)
  ✓ Form gốc rendered (input name="seo_title")
  ✓ formInfo (textarea name="special_content")
  ✓ formTimetable (textarea name="tour_timetable_title")
  ✓ Hidden id input <input name="tour_timetable_id">
  ✓ Magic accessor trả về bản dịch:
      ✓ tour title (qua Seo magic accessor)
      ✓ tour pick_up / transport (qua Tour magic accessor)
      ✓ tour content fields (qua TourContent magic accessor)
      ✓ timetable title (qua TourTimetable magic accessor)
      ✓ seo_title / slug (qua Seo magic accessor)

✓ POST /he-thong/translation/en/2063 → 200 redirect with flash "success"
  ✓ tour_translations: name (alias title), pick_up, transport
  ✓ tour_content_translations: special_content, include, ...
  ✓ tour_timetable_translations: title (cho từng tour_timetable_id)
  ✓ seo_translations: title, description, seo_title, slug
  ✓ seo_content_translations: body_content_translation
```

### 0.7. Cách dùng (admin & dev)

**Admin: dịch 1 entity sang ngôn ngữ X**
1. Mở trang admin entity gốc, ví dụ `/he-thong/tour/view?id=211`.
2. Cuộn xuống panel **“Phiên bản dịch”** ở cuối form SEO.
3. Bấm **“Dịch sang EN”** → mở `/he-thong/translation/en/2316`.
4. **V3.1: Trang dịch reuse 100% form admin gốc**:
   - Banner cam ở trên đầu cho biết đang dịch sang EN, có locale switcher
   - Form layout y hệt trang gốc (mọi card, mọi section, mọi sidebar)
   - Các trường **dịch được** (SEO, name/pick_up/transport, content, timetable, FAQ, options name) tô **viền cam + nền vàng nhạt**
   - Các trường **không dịch** (giá, ngày, FK, status, image) tự động **disabled** (xám, viền đứt) hiển thị giá trị bản gốc để tham khảo
   - Magic accessor `HasTranslations::getAttribute()` tự trả về giá trị bản dịch khi locale != default → form gốc hiển thị đúng bản dịch hiện tại mà không cần sửa view
5. Bấm **“Lưu bản dịch EN”** (nút Lưu bị JS đổi label) → POST đi tới `admin.translation.save` thay vì controller gốc:
   - Server đọc input theo whitelist trong `config/tablemysql.php > translation_relations`
   - SEO fields (`title`, `description`, `seo_title`, `seo_description`, `slug`, `link_canonical`) → `seo_translations`
   - Entity translatable fields (vd `pick_up`, `transport`) → `<entity>_translations`
   - Body content (`body_content_translation`, JS inject) → `seo_content_translations`
   - Top-level fields cho relation hasOne (vd `tour_content`) → `<rel>_translations`
   - Nested arrays cho relation hasMany (vd `timetable[X][tour_timetable_title]`) → `<rel>_translations`, map row qua hidden id

**Dev: thêm 1 entity type mới vào hệ thống dịch**
1. Tạo migration mới sinh `<table>_translations` + backfill (mẫu: `2026_05_05_150000`).
2. Tạo `<Model>Translation extends BaseTranslationModel` với `$table`.
3. Vào `<Model>` master: `use HasTranslations`, set `$translationModel` + `$translatableFields`.
4. Vào `config/tablemysql.php`:
   - Bổ sung `translatable` (cột entity dịch được)
   - Bổ sung `translation_relations` (relations dịch được, mỗi block khai báo `model`, `fk`, `fields`, `multiple`, `input_layout`)
   - Nếu form gốc không có ô `name` riêng (derive từ `title`): set `translatable_input_aliases => ['name' => 'title']` (hoặc dựa trên auto-fallback có sẵn)
5. Vào `AdminTranslationController::$TYPE_VIEW_MAP`: thêm mapping `seo.type → [ControllerClass, action]`.
6. Nếu form gốc có repeater (timetable, FAQ, options): inject `<input type="hidden" name="<rel>[<idx>][<rel>_id]" value="$item->id">` trong partial blade.
7. Xong — admin click "Dịch sang EN" tự lên trang reuse form gốc, JS auto-disable các trường không dịch.

### 0.7.1. Schema cấu hình `translation_relations`

```php
'tour_info' => [
    'model'         => Tour::class,
    'translatable'  => ['name', 'pick_up', 'transport'],
    'translatable_input_aliases' => ['name' => 'title'],
    'translation_relations' => [
        // hasOne — fields nằm ở root form (vd tour_content)
        'content' => [
            'model' => TourContent::class, 'fk' => 'tour_info_id',
            'fields' => ['special_content', 'include', 'not_include', ...],
            'multiple' => false,
            'input_layout' => 'top_level',
        ],
        // hasMany — fields nằm trong array nested (vd tour_timetable)
        'timetables' => [
            'model' => TourTimetable::class, 'fk' => 'tour_info_id',
            'fields' => ['title', 'content', 'content_sort'],
            'multiple' => true,
            'input_layout' => 'array',
            'input_array_name' => 'timetable',           // tên array trong $request
            'input_id_alias'   => 'tour_timetable_id',   // tên hidden id input
            'input_field_aliases' => [                    // map input_name → DB column
                'tour_timetable_title'   => 'title',
                'tour_timetable_content' => 'content',
                'tour_timetable_id'      => '__id',       // sentinel
            ],
        ],
        // ajax — modal AJAX (tour_option), bỏ qua trong save() generic
        'options' => [
            'model' => TourOption::class, 'fk' => 'tour_info_id',
            'fields' => ['name'],
            'multiple' => true,
            'input_layout' => 'ajax',  // skip — endpoint riêng xử lý
        ],
    ],
],
```

### 0.8. Smoke test sau Phase 2.5

```bash
# 1. Migrate (Phase 2)
php artisan migrate
   ✓ 2026_05_05_150000_create_extended_translation_tables  ~13s
   ✓ Backfilled 1508 seo_content_translations rows from blade files

# 2. HTTP smoke (frontend)
curl -sk https://hitour.dev/                                                              → 200
curl -sk https://hitour.dev/tour-du-lich-phu-quoc/tour-du-lich-ben-tre-phu-quoc-3-ngay-2-dem  → 200
curl -sk https://hitour.dev/en/tour-du-lich-phu-quoc/tour-du-lich-ben-tre-phu-quoc-3-ngay-2-dem  → 200

# 3. Routes mới
php artisan route:list --name=admin.translation
   GET   /he-thong/translation/{locale}/{seoId}        admin.translation.edit
   POST  /he-thong/translation/{locale}/{seoId}        admin.translation.save
   GET   /he-thong/translation/{locale}/{seoId}/delete admin.translation.delete

# 4. V3.1 smoke (admin trang dịch tour 208 / seo 2063)
#    GET /he-thong/translation/en/2063
#    Render: View "admin.tour.view" (REUSE form gốc)
#    Verify trong rendered HTML:
#      ✓ banner snippet rendered (translationBanner)
#      ✓ JS globals injected (TRANSLATION_MODE)
#      ✓ form gốc rendered (input name="seo_title")
#      ✓ formInfo (special_content) included
#      ✓ formTimetable (tour_timetable_title) included
#      ✓ formTimetable injected hidden id (tour_timetable_id)
#      ✓ tour title via seo (magic accessor trả về bản dịch)
#      ✓ pick_up/transport (entity translatable)
#      ✓ tour content fields (TourContent magic accessor)
#      ✓ timetable title (TourTimetable magic accessor)
#      ✓ seo_title/slug (Seo magic accessor)
#
#    POST /he-thong/translation/en/2063 (giả lập form gốc submit)
#    Verify DB sau save:
#      ✓ tour_translations (name, pick_up, transport)
#      ✓ tour_content_translations (special_content, include, ...)
#      ✓ tour_timetable_translations (title cho từng timetable_id)
#      ✓ seo_translations (title, description, seo_title, ..., slug)
#      ✓ seo_content_translations (body content)
```

---

---

## 1. Tổng quan hệ thống hiện tại

### 1.1. Tech stack

| Thành phần       | Phiên bản / Công nghệ                                   |
| ---------------- | ------------------------------------------------------- |
| Framework        | Laravel `^9.19` (PHP `^8.0.2`)                          |
| Database         | MySQL (kết nối qua `mysql` driver)                      |
| Storage          | Local + Google Cloud Storage (`spatie/laravel-google-cloud-storage`) |
| Cache            | File cache (HTML render cache vào `storage`)             |
| Front-end build  | Vite + SCSS                                              |
| PDF              | `barryvdh/laravel-dompdf` (xuất booking)                 |
| Crawler          | `fabpot/goutte`, `symfony/browser-kit`                   |

### 1.2. Mô hình routing tổng thể

Toàn bộ trang public dùng **một catch-all route duy nhất** (file `routes/web.php`, dòng cuối):

```645:646:routes/web.php
/* ===== ROUTING ALL ===== */
Route::get("/{slug}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}/{slug7?}/{slug8?}/{slug9?}/{slug10?}", [RoutingController::class, 'routing'])->name('routing');
```

Route catch-all này đẩy tất cả request về `RoutingController::routing()` để xử lý. Phía trước nó là các nhóm route cố định:

- `prefix(he-thong)` → toàn bộ Admin (`auth + role:admin`).
- `prefix(*Booking)` → các flow đặt chỗ public (tour, ship, service, combo, hotel).
- Một số endpoint kỹ thuật: `sitemap.xml`, `sitemap/{type}.xml`, `auth/google/callback`, AJAX, …
- Loop foreach `Redirect::all()` để đăng ký route 301 redirect (lưu trong DB).

Cơ chế "URL là duy nhất → Slug là khoá" được hiện thực bằng bảng `seo` (xem mục 1.4).

### 1.3. Kiến trúc thư mục

```
app/
├── Http/Controllers/        # ~100 controller (Admin*Controller + main controllers)
├── Models/                  # ~133 model — Eloquent, plus *_foreign, Relation* (pivot tables)
├── Helpers/Url.php          # Helper check slug & build breadcrumb
├── Services/
│   └── BuildInsertUpdateModel.php  # Builder dữ liệu form → DB cho SEO/Tour/...
├── Jobs/CheckSeo.php
config/
├── admin.php                # cấu hình storage, image, region, prefix
├── main.php
database/migrations/         # ~150 migration
resources/views/main/        # views public (FE)
resources/views/admin/       # views CMS
storage/app/public/contents/ # nội dung dài (long-text) lưu thành file .blade.php
```

### 1.4. Bảng `seo` — "trái tim" của routing & SEO

```17:36:database/migrations/2022_05_17_132751_create_seo_table.php
$table->id();
$table->string('title');
$table->text('description')->nullable();
$table->text('image')->nullable();
$table->text('image_small')->nullable();
$table->integer('level');
$table->integer('parent')->nullable();
$table->integer('ordering')->nullable();
$table->integer('topic')->nullable();
$table->text('seo_title')->nullable();
$table->text('seo_description')->nullable();
$table->text('slug');
$table->string('type', '100');
$table->string('rating_author_name', 1)->nullable();
$table->string('rating_author_star', 6)->default(5);
$table->integer('rating_aggregate_count')->nullable();
$table->string('rating_aggregate_star', 6)->nullable();
$table->integer('created_by');
$table->timestamps();
```

Các cột bổ sung (qua các migration sau):

| Cột                | Ý nghĩa                                                                 |
| ------------------ | ----------------------------------------------------------------------- |
| `slug_full`        | Đường dẫn đầy đủ kế thừa qua `parent` (build bởi `Seo::buildFullUrl`). |
| `link_canonical`   | Override canonical URL (nếu cần).                                       |
| `auto_post`        | Cờ cho job auto-post.                                                   |
| `video`            | Đính kèm video (cho schema).                                            |

Cơ chế hoạt động lõi:

- Mỗi entity (Tour, Ship, Service, Hotel, Combo, …) đều **có 1 `seo_id`** trỏ về dòng `seo` tương ứng.
- Cột `type` chính là discriminator phân loại: `tour_info`, `tour_location`, `ship_info`, …
- Cột `slug` là phần URL của riêng entity đó. `slug_full` ghép từ chuỗi `parent`.
- Toàn bộ trang public lookup: `Url::checkUrlExists(end($arraySlug))` → so sánh `slug_full` với URL hiện tại để quyết định "redirect 301" hay "render".

```11:17:app/Helpers/Url.php
public static function checkUrlExists($slug){
    $infoPage   = Seo::select('*')
                    ->where('slug', $slug)
                    ->first();
    if(!empty($infoPage->slug_full)) return $infoPage;
    return null;
}
```

### 1.5. Cơ chế lưu nội dung dài (Blade-as-content)

Một đặc điểm rất riêng của hệ thống: phần "content body" của tour/blog/page/… **không lưu trong DB** mà lưu thành file Blade dưới `storage/app/public/contents/<type>/<slug>.blade.php`, render runtime bằng `Blade::render(Storage::get(...))`.

Sau Phase 0:
- Đường dẫn các thư mục content **đã được tập trung về `config/tablemysql.php`** (cột `content_dir`) thay vì dùng `config/admin.php`.
- Khi đổi slug, file blade tự động được rename qua `Seo::renameContentBladeFiles()` (trong `Seo::updateItem`).
- **Hạn chế "1 file / 1 slug" vẫn còn** — sẽ chuyển sang lưu DB (`seo_content` / `tour_content_translations`...) ở Phase 1 khi đa ngôn ngữ.

### 1.6. Cơ chế cache HTML (sau Phase 0)

Trước Phase 0: logic cache nằm rải rác trong `RoutingController` (file thuần, TTL hard-code 1800s, không gzip, không minify, không hỗ trợ Google Cloud Storage).

Sau Phase 0: chuyển sang `app/Services/HtmlCacheService.php` (port từ zenpot, đã thích nghi cho Laravel 9):

- Dùng chung cho `RoutingController`, `SitemapController`, và mọi component cần cache HTML khác.
- Bật/tắt qua `APP_CACHE_HTML` env.
- TTL, disk, gzip, minify HTML, minify JS/CSS inline đều cấu hình ở `config/main.php`.
- Disk có thể là `local` hoặc `gcs` (Google Cloud Storage).
- File cache mặc định lưu dạng `.gz` (giảm disk + IO).
- API `getOrRender(string $cacheKey, callable $renderCallback)` + `clear($key)` + `clearAll()`.
- `HtmlCacheService::buildKey($slugFull, $params, $namespace)` — `namespace` chính là chỗ Phase 1 sẽ đặt locale (ví dụ `'en'`) để cache đa ngôn ngữ tách biệt.

---

## 2. Phân tích chi tiết các loại trang

> Quy ước: với mỗi loại trang, ghi rõ **(URL → DB → Logic → Quan hệ)**.

### 2.1. Vé tàu (Ship)

#### 2.1.1. Danh mục vé tàu (Ship Location)

| Hạng mục            | Chi tiết                                                                       |
| ------------------- | ------------------------------------------------------------------------------ |
| URL                 | `/{ship_location.slug}` (ví dụ: `/ve-tau-cao-toc-phu-quoc`)                   |
| `seo.type`          | `ship_location`                                                                |
| Bảng chính          | `ship_location(id, seo_id, name, description, district_id, province_id, region_id, note)` |
| Quan hệ             | `ship_location` 1-N `ship_info` qua `ship_info.ship_location_id`               |
| Quan hệ chéo        | Nhiều-nhiều với `tour_location` (qua `relation_tour_location_ship_location`), `category_info` (`relation_ship_location_category_info`) |
| View                | `resources/views/main/shipLocation/index.blade.php`                            |
| Content blade       | `storage/app/public/contents/shipLocations/<slug>.blade.php`                   |
| Schedule blade      | `storage/app/public/contents/shipSchedule/<slug>.blade.php` (lịch tàu)         |
| Logic chính         | Render danh sách tàu + lịch tàu + blog liên quan theo category gắn ship_location |

#### 2.1.2. Đối tác tàu (Ship Partner)

- URL: `/{ship_partner.slug}`
- `seo.type` = `ship_partner`
- Bảng: `ship_partner` + `ship_partner_contact`
- Logic: trang giới thiệu hãng tàu, content blade riêng.

#### 2.1.3. Chi tiết vé tàu (Ship Info)

| Hạng mục       | Chi tiết                                                                     |
| -------------- | ---------------------------------------------------------------------------- |
| URL            | `/{ship_location.slug}/{ship_info.slug}` (kế thừa qua `seo.parent`)          |
| `seo.type`     | `ship_info`                                                                  |
| Bảng           | `ship_info(id, seo_id, name, ship_location_id, ship_departure_id, ship_port_departure_id, ship_port_location_id, note, …)` |
| Bảng phụ       | `ship_price`, `ship_time`, `ship_partner` (M-N qua `relation_ship_partner`), `ship_departure`, `ship_port` |
| Booking        | `ship_booking`, `ship_booking_quantity_and_price`, `ship_booking_status`     |

### 2.2. Tour trong nước

#### 2.2.1. Danh mục Tour Location

| Hạng mục    | Chi tiết                                                                              |
| ----------- | ------------------------------------------------------------------------------------- |
| URL         | `/{tour_location.slug}` (ví dụ `/tour-phu-quoc`)                                      |
| `seo.type`  | `tour_location`                                                                       |
| Bảng        | `tour_location(id, seo_id, name, display_name, description, district_id, province_id, region_id, island, special, note)` |
| Content     | `contents/tourLocations/<slug>.blade.php`                                             |
| Quan hệ     | M-N với `air_location`, `service_location`, `ship_location`, `combo_location`, `hotel_location`, `carrental_location`, `guide_info`, `category_info`, `destination_list`, `special_list` |

#### 2.2.2. Chi tiết Tour (`tour_info`)

```16:34:database/migrations/2022_05_24_153536_create_tour_info_table.php
Schema::create('tour_info', function (Blueprint $table) {
    $table->id();
    $table->integer('seo_id');
    $table->integer('tour_departure_id');
    $table->string('pick_up', 255)->nullable();
    $table->string('transport', 255)->nullable();
    $table->string('code', 20);
    $table->text('name');
    $table->integer('price_show');
    $table->integer('price_del')->nullable();
    $table->string('departure_schedule', 100);
    $table->integer('days');
    $table->integer('nights');
    $table->string('time_start', 100)->nullable();
    $table->string('time_end', 100)->nullable();
    $table->boolean('status_show')->default(1);
    $table->boolean('status_sidebar')->default(1);
    $table->timestamps();
});
```

Bảng phụ: `tour_content` (1-1, longtext include/not_include/menu/hotel/policy_child), `tour_timetable` (1-N), `tour_option` (1-N) → `tour_price` (1-N), `relation_tour_location` (M-N), `relation_tour_partner`, `relation_tour_staff`, `question_answer_info` (FAQ).

URL: `/{tour_location.slug}/{tour_info.slug}` (kế thừa qua `seo.parent`).

### 2.3. Tour nước ngoài

> **Lưu ý kiến trúc hiện tại**: hệ thống đã có `tour_continent → tour_country → tour_info_foreign` riêng biệt với tour trong nước. Đây là **sản phẩm khác**, **không phải bản dịch của tour trong nước**.

| Trang             | `seo.type`           | Bảng                       | Quan hệ |
| ----------------- | -------------------- | -------------------------- | ------- |
| Châu lục          | `tour_continent`     | `tour_continent`           | 1-N với `tour_country` |
| Quốc gia          | `tour_country`       | `tour_country`             | 1-N với `tour_info_foreign` (qua relation) |
| Chi tiết tour     | `tour_info_foreign`  | `tour_info_foreign`, `tour_content_foreign`, `tour_option_foreign`, `tour_price_foreign`, `tour_timetable_foreign` | M-N với `tour_country`, `staff`, `partner` |

Cấu trúc bảng `tour_info_foreign` giống hệt `tour_info` (chỉ khác tên & relation về `tour_country`). URL: `/{tour_continent.slug}/{tour_country.slug}/{tour_info_foreign.slug}`.

### 2.4. Dịch vụ (Service)

| Trang              | `seo.type`         | Bảng |
| ------------------ | ------------------ | ----- |
| Danh mục dịch vụ   | `service_location` | `service_location(id, seo_id, name, display_name, description, region/province/district)` |
| Chi tiết dịch vụ   | `service_info`     | `service_info(id, seo_id, service_location_id, code, name, price_show, price_del, time_start, time_end, status_show, status_sidebar)` |
| Bảng phụ           |                    | `service_price`, `service_option`, `relation_service_staff` |

URL: `/{service_location.slug}/{service_info.slug}`.

### 2.5. Vé máy bay (Air)

| Trang             | `seo.type`     | Bảng                                                                           |
| ----------------- | -------------- | ------------------------------------------------------------------------------ |
| Danh mục          | `air_location` | `air_location`                                                                 |
| Đối tác hãng      | `air_partner`  | `air_partner`, `air_partner_contact`                                           |
| Chi tiết          | `air_info`     | `air_info`, `air_port`, `air_departure`                                        |

### 2.6. Combo

| Trang              | `seo.type`        | Bảng                                          |
| ------------------ | ----------------- | --------------------------------------------- |
| Danh mục           | `combo_location`  | `combo_location`, `relation_combo_location`   |
| Chi tiết           | `combo_info`      | `combo_info`, `combo_content`, `combo_option`, `combo_price` |
| Đối tác combo      | (không là trang public) | `combo_partner`, `combo_partner_contact` |

### 2.7. Khách sạn (Hotel)

| Trang             | `seo.type`        | Bảng                                                |
| ----------------- | ----------------- | --------------------------------------------------- |
| Danh mục KS       | `hotel_location`  | `hotel_location`                                    |
| Chi tiết KS       | `hotel_info`      | `hotel_info`, `hotel_content`, `hotel_room`, `hotel_image`, `hotel_facility`, `hotel_room_facility`, `hotel_contact`, `hotel_comment` |
| Booking           | (không là trang public) | `hotel_booking`, `hotel_booking_request`, `hotel_booking_status` |

### 2.8. Cho thuê xe (Carrental)

| Trang                 | `seo.type`           | Bảng                  |
| --------------------- | -------------------- | --------------------- |
| Danh mục địa điểm     | `carrental_location` | `carrental_location`  |

(Chưa có bảng `carrental_info` chi tiết riêng — hiện chỉ ở mức location).

### 2.9. Hướng dẫn viên (Guide)

| Trang        | `seo.type`    | Bảng                          |
| ------------ | ------------- | ----------------------------- |
| Chi tiết HDV | `guide_info`  | `guide_info`, gắn M-N với `tour_location`, `tour_continent`, `tour_country` |

### 2.10. Blog & Category

| Trang             | `seo.type`     | Bảng                                                       |
| ----------------- | -------------- | ---------------------------------------------------------- |
| Chuyên mục        | `category_info`| `category_info` (cây cha-con qua `seo.parent`)             |
| Bài viết          | `blog_info`    | `blog_info`, `relation_category_info_blog_info` (M-N)      |

URL: `/{cat_lv1.slug}/{cat_lv2.slug?}/.../{blog.slug}`.

### 2.11. Trang phụ trợ (Page)

| Trang        | `seo.type`  | Bảng                |
| ------------ | ----------- | ------------------- |
| Trang tĩnh   | `page_info` | `page` (gắn với `seo`), content blade ở `contents/pages/` |

Dùng cho: about, liên hệ, chính sách, điều khoản, …

### 2.12. Sơ đồ quan hệ tổng thể (rút gọn)

```
seo (1) ────────┬───── tour_location ──┬── M:N ──── tour_info  ──┬── tour_content
                │                       │                          ├── tour_timetable
                │                       │                          ├── tour_option ── tour_price
                │                       │                          ├── relation_tour_partner ── tour_partner
                │                       │                          └── relation_tour_staff ── staff
                │                       │
                │                       ├── M:N ── air_location, service_location, ship_location,
                │                       │          combo_location, hotel_location, carrental_location,
                │                       │          guide_info, category_info
                │
                ├───── tour_continent ── 1:N ── tour_country ── M:N ── tour_info_foreign (cấu trúc song song tour_info)
                │
                ├───── service_location ── 1:N ── service_info ── service_price/option
                ├───── ship_location ── 1:N ── ship_info ── ship_price, ship_time, ship_partner (M:N)
                ├───── air_location  ── 1:N ── air_info  ── air_partner (M:N)
                ├───── combo_location ── 1:N ── combo_info ── combo_price/option
                ├───── hotel_location ── 1:N ── hotel_info ── hotel_room ── hotel_room_facility (M:N)
                ├───── category_info (tree) ── M:N ── blog_info
                └───── page (page_info)

booking, ship_booking, hotel_booking, ... (transactional, không liên quan đa ngôn ngữ trực tiếp)
```

### 2.13. CRUD pattern hiện hành

Mọi entity public-facing đều theo pattern admin chung:

1. `Admin*Controller@list` — danh sách, search, paginate (cookie nhớ `viewPerPage`).
2. `@view` — xem/sửa/tạo (dùng chung view, phân biệt `type=create|edit`).
3. `@create` (POST) → `BuildInsertUpdateModel::buildArrayTableSeo()` build payload → `Seo::insertItem()` → build payload entity → `<Entity>::insertItem()` → ghi content blade ra storage.
4. `@update` (POST) → `Seo::updateItem()` (tự động rebuild `slug_full` cho toàn bộ con đệ quy) → `<Entity>::updateItem()` → ghi đè content blade.
5. `@delete` (GET) — soft check & xoá.

```49:58:app/Models/Seo.php
/* mỗi lần cập nhật lại slug thì phải build lại slug_full của toàn bộ children */
if($flag==true){
    $childs = self::select('id', 'level', 'parent', 'slug')
                ->where('parent', $id)
                ->get();
    foreach($childs as $child){
        $urlNew         = self::buildFullUrl($child->slug, $child->level, $child->parent);
        $paramsUpdate   = ['slug_full' => $urlNew];
        self::updateItem($child->id, $paramsUpdate);
    }
}
```

---

## 3. Đánh giá hệ thống hiện tại & Trạng thái Phase 0

### 3.1. Điểm mạnh (giữ nguyên — không refactor)

| # | Điểm mạnh                                                                                |
| - | ---------------------------------------------------------------------------------------- |
| 1 | **`seo` table tập trung** — bộ "single source of truth" cho mọi URL. Routing 1 chỗ, dễ tổng hợp sitemap. |
| 2 | **Cấu trúc URL dạng cây** (`level`, `parent`, `slug_full`) khá linh hoạt. |
| 3 | **Content lưu Blade** giúp custom HTML/markup ở từng trang (tốt cho SEO on-page). |
| 4 | Schema markup (Organization, Article, Product, Breadcrumb, FAQ, ItemList) đã được tách thành partial, dễ tái sử dụng. |
| 5 | Đã có module `Redirect` quản lý 301 → cơ sở để xử lý SEO khi đổi URL. |
| 6 | Tách rõ entity (Tour/Ship/Service/Combo/Hotel/Air) — domain rõ ràng. |

### 3.2. Vấn đề kỹ thuật & Trạng thái xử lý

| # | Vấn đề                                                                    | Trạng thái |
| - | ------------------------------------------------------------------------- | ---------- |
| 1 | **Không có khái niệm `locale` ở mọi cấp độ** (DB, route, view, helper).  | ✅ **RESOLVED — Phase 1**: bảng `languages` + middleware `DetectLocale` + helpers `current_locale()`, `seo_url()`, `SeoAlternates`. |
| 2 | `seo.title/description/seo_title/seo_description/slug` là **đơn ngữ**.    | ✅ **RESOLVED — Phase 1**: bảng `seo_translations` chứa toàn bộ trường này; `Seo` model đọc qua magic accessor; admin form đẩy data về `seo_translations`. |
| 3 | `seo.slug` được dùng làm **tên file content blade** → đổi slug phải đổi file. | ⚠️ **Phase 0 đã giảm thiểu** (auto rename qua `Seo::renameContentBladeFiles`). Phase 1 đã thêm cơ chế đọc theo locale (`<slug>.<locale>.blade.php`) trong `RoutingController::renderContentBlade()`. Phase 2 sẽ migrate sang `<seo_id>/<locale>.blade.php` (tuỳ chọn). |
| 4 | `tour_info_foreign` không phải bản dịch — là entity riêng. | Còn — sẽ review ở Phase 5+ (giữ tách hay gộp). Hiện tại `tour_info_foreign` đã có translation table riêng nên hoạt động đa ngữ độc lập. |
| 5 | Routing là catch-all `/{slug}/...` không có prefix `/{lang}/`. | ✅ **RESOLVED — Phase 1**: `routes/web.php` có 2 group (default + `Route::prefix('{locale}')`), gắn middleware `DetectLocale`. |
| 6 | `Seo::buildFullUrl` đệ quy fetch toàn bảng seo mỗi update → O(n²). | ✅ **RESOLVED — Phase 0**. |
| 7 | Cache HTML đặt theo `slug_full` không gắn locale → đa ngôn ngữ sẽ collide cache. | ✅ **RESOLVED — Phase 0+1**: `HtmlCacheService::buildKey($slugFull, $params, $namespace)` được Phase 0 thiết kế sẵn; Phase 1 đã set `$namespace = $locale` trong `RoutingController` và `SitemapController`. |
| 8 | `og:locale` hard-code `vi_VN` trong `schema/social.blade.php`. Không có hreflang/canonical. | ✅ **RESOLVED — Phase 1**: `head.blade.php` emit hreflang + canonical + x-default qua `SeoAlternates::for($seo)`; `social.blade.php` đã đổi sang `current_language()->og_locale` + `og:locale:alternate`. |
| 9 | `foreach Redirect::all()` đăng ký N route mỗi request → tải nặng. | ✅ **RESOLVED — Phase 0**. |
| 10 | Hơn 100 controller admin lặp pattern. | ⚠️ **Phase 1 đã chuẩn hoá pattern** (qua `EntityTranslationService::persistFromRequest()` + blade `formMultilingualTabs.blade.php`); 3 controller mẫu (`AdminTourController`, `AdminBlogController`, `AdminPageController`) đã áp pattern. **Phase 2** sẽ áp cho ~50+ controller còn lại bằng cách copy snippet (xem §5.4). |
| 11 | Cache HTML logic nằm rải rác. | ✅ **RESOLVED — Phase 0**. |
| 12 | `RoutingController::routing()` 250+ dòng, switch type bằng if-else dài. | ✅ **RESOLVED — Phase 0**. |
| 13 | `Url::checkUrlExists($slug)` chỉ check theo phần slug cuối → collision. | ✅ **RESOLVED — Phase 0+1**: Phase 0 chuyển sang `slug_full`; Phase 1 mở rộng để query `seo_translations` theo `(language_id, slug_full)` collation `utf8mb4_bin`. |
| 14 | Mapping `seo.type → model/view/content_dir` rải rác. | ✅ **RESOLVED — Phase 0+1**: tập trung tại `config/tablemysql.php`; Phase 1 thêm thuộc tính `translatable` để tự sinh `<entity>_translations`. |
| 15 | Đổi slug không tự sinh redirect 301 và không thay internal link cũ. | ✅ **RESOLVED — Phase 0+1**: Phase 0 cho `Seo`; Phase 1 mở rộng `SeoTranslation::upsertTranslation()` cũng tự cascade slug_full theo locale + tự tạo 301 (URL có prefix locale chuẩn). |
| 16 | Bảng `seo` thiếu index trên `slug_full`, `parent`, `type`. | ✅ **RESOLVED — Phase 0**. |
| 17 | Bảng `redirect_info` thiếu index `url_old` và collation phân biệt dấu. | ✅ **RESOLVED — Phase 0**. |
| 18 | Sitemap render runtime, không cache → mỗi request tốn DB query. | ✅ **RESOLVED — Phase 0+1**: Phase 0 thêm cache; Phase 1 split per-locale + xhtml:link hreflang annotation. |
| 19 | Một số bảng trùng pattern (`tour_info` vs `tour_info_foreign`, `tour_content` vs `tour_content_foreign`...). | ⚠️ Vẫn giữ — Phase 1 chỉ thêm translation cho các bảng hiện hữu (foreign cũng có translation riêng). Quyết định gộp lùi sang Phase 5+. |
| 20 | `link_canonical` không có ràng buộc với phiên bản ngôn ngữ. | ✅ **RESOLVED — Phase 1**: `seo_translations.link_canonical` nullable per-locale. |

> **Tóm tắt sau Phase 1**: 17 / 20 vấn đề đã được giải quyết. 3 vấn đề còn lại (foreign entity gộp, blade-by-id, áp pattern admin cho 50+ controller) là incremental work, không block public-facing UX.

### 3.3. Phase 0 — Tóm tắt hardening đã triển khai

| Thành phần | Mô tả |
| --- | --- |
| `app/Services/HtmlCacheService.php` | Service cache HTML thống nhất: gzip + minify HTML/JS/CSS optional, đa disk, TTL configurable, key có namespace cho locale. Tất cả cache HTML toàn trang & sitemap đi qua đây. |
| `app/Http/Middleware/CheckRedirect.php` | Middleware 301: 1 query/request lên `redirect_info` với collation `utf8mb4_bin`. Bỏ luôn `foreach Redirect::all()` trong `routes/web.php`. Đăng ký key `checkRedirect` trong `app/Http/Kernel.php`. |
| `app/Models/Seo.php` | `buildFullUrl()` từ O(n²) → O(1); `updateItem()` tự cascade slug_full cho con, tự tạo bản ghi 301 trong `redirect_info`, tự rename file content blade. |
| `app/Helpers/Url.php` | `checkUrlExists()` query theo `slug_full` thay vì `slug` (an toàn tuyệt đối, dùng index); thêm in-memory cache, helper `cleanRequestPath()`. |
| `app/Http/Controllers/RoutingController.php` | Refactor: `dispatch()` switch-by-type bằng `match()`, mỗi type là 1 method riêng, dùng `HtmlCacheService` qua DI. |
| `app/Http/Controllers/SitemapController.php` | Cache sitemap XML qua `HtmlCacheService`; sitemap-index lấy danh sách type từ `config/tablemysql.php`. |
| `app/Http/Controllers/AdminCacheController.php` | Xoá cache qua `HtmlCacheService::clearAll()` (xoá được cả file `.gz`, hỗ trợ mọi disk). |
| `config/tablemysql.php` | Mapping `seo.type → model + view + content_dir + with` — single source of truth, sẵn sàng mở rộng `translations` ở Phase 1. |
| `config/main.php` | Thêm cấu hình `cache.disk`, `cache.ttl`, `cache.use_gzip`, `cache.use_html_min`, `cache.use_jscss_min`. |
| `routes/web.php` | Bỏ `foreach Redirect::all()` đăng ký N route, gắn middleware `checkRedirect` vào group catch-all routing. |
| `database/migrations/2026_05_04_120000_add_indexes_to_redirect_info_table.php` | Thêm index `url_old(191)`, `url_new(191)` + ép collation `utf8mb4_bin`. |
| `database/migrations/2026_05_04_120100_add_indexes_to_seo_table.php` | Thêm index `slug(191)`, `slug_full(191)`, `parent`, `type` cho bảng `seo`. |
| `composer.json` | Thêm `voku/html-min` và `matthiasmullie/minify` (dùng cho minify HTML/JS/CSS inline trong `HtmlCacheService`). |

> **Quan trọng**: Trước khi triển khai Phase 1, chạy `composer install` và `php artisan migrate` để các tối ưu trên có hiệu lực trên môi trường staging/production.

### 3.4. Phase 1 — Tóm tắt tất cả file đã thêm/sửa

#### Database
| Tệp | Mô tả |
| --- | --- |
| `database/migrations/2026_05_04_130000_create_languages_table.php` | Tạo bảng `languages` + seed 5 ngôn ngữ (vi default, en, zh, ja, ko). |
| `database/migrations/2026_05_04_130100_create_seo_translations_table.php` | Tạo `seo_translations` + index `(seo_id, language_id)` UNIQUE, `(language_id, slug_full)` UNIQUE collation `utf8mb4_bin`. |
| `database/migrations/2026_05_04_130200_create_entity_translations_tables.php` | Migration **động** — đọc `config('tablemysql')` và tạo ~21 bảng `<entity>_translations` (tour_info, ship_info, service_info, blog_info, page_info, hotel_info, combo_info, ship_location, ...) + backfill data hiện tại sang locale `vi`. |

#### Models / Traits
| Tệp | Mô tả |
| --- | --- |
| `app/Models/Language.php` | Eloquent + cache `Language::active()`, `default()`, `byCode()`, `flushCache()`. |
| `app/Models/SeoTranslation.php` | Eloquent + `alternates()`, `buildFullUrl()`, `upsertTranslation()` (cascade slug_full + auto 301 theo locale), `createRedirect301()`. |
| `app/Models/Concerns/HasTranslations.php` | Trait dùng chung: `translations()`, `translation($locale)`, `translate()`, `trans('field', $locale)`, `upsertTranslation()`. |
| `app/Models/BaseTranslationModel.php` | Abstract base cho tất cả `*Translation` model — giảm boilerplate. |
| `app/Models/Seo.php` | Refactor: thêm hằng `LOCALIZABLE`, `translations()`, `translation()`, `alternates()`, magic `getAttribute()` ưu tiên đọc translation theo locale; `insertItem()`/`updateItem()` tự đồng bộ default locale `seo_translations`; `buildFullUrl` dùng `getRawOriginal('slug_full')`. |
| `app/Models/{Tour,Ship,Service,Blog,Page,Category,Hotel,Combo,Air,Carrental,Guide,TourLocation,ShipLocation,...}.php` | 21 model áp `HasTranslations`, khai báo `$translationModel` + `$translatableFields`. |

#### Routing / Middleware
| Tệp | Mô tả |
| --- | --- |
| `app/Http/Middleware/DetectLocale.php` | Đọc segment đầu URL, set `app()->setLocale()`, share `currentLocale/currentLanguage/availableLocales` ra view. |
| `app/Http/Kernel.php` | Đăng ký key `'detectLocale'` trong `$routeMiddleware`. |
| `routes/web.php` | Thêm `Route::prefix('{locale}')->whereIn('locale','en|zh|ja|ko')` cho mọi route public (homepage, sitemap, booking forms, catch-all routing); group default vẫn không prefix. Tất cả đều đi qua middleware `detectLocale`. |
| `app/Http/Controllers/RoutingController.php` | `routing()` dùng `Url::cleanRequestPathWithLocale()`; `Url::checkUrlExists($url, $locale)`; cache key `HtmlCacheService::buildKey($slug, $params, $locale)`; `dispatch($itemSeo, string $locale)`; mỗi `render<Type>($itemSeo, string $locale)`; `renderContentBlade($type, $slug, $locale)` ưu tiên `<slug>.<locale>.blade.php` rồi fallback `<slug>.<defaultLocale>.blade.php` rồi `<slug>.blade.php`. |
| `app/Http/Controllers/SitemapController.php` | Đọc locale từ request; cache key `'sitemaps/<locale>/index'`; query `seo_translations`; emit `xhtml:link rel="alternate"` + `hreflang="x-default"` cho mỗi `<url>`. |

#### Helpers / Services
| Tệp | Mô tả |
| --- | --- |
| `app/Helpers/Url.php` | `checkUrlExists($slugFull, $locale)` query `seo_translations` (collation `utf8mb4_bin`); `buildBreadcrumb()` cũng dùng translations; thêm `cleanRequestPathWithLocale()` tách prefix locale. |
| `app/Helpers/SeoAlternates.php` | `for($entityOrSeo)` trả collection alternates `(code, og_locale, is_default, url, language, translation)`; `urlFor($entityOrSeo, $locale)`; `xDefaultUrl($entityOrSeo)`. |
| `app/Helpers/global.php` | `current_locale()`, `current_language()`, `default_locale()`, `is_default_locale()`, `seo_url()`, `seo_url_full()`, `seo_alternates()`, `locale_url()`. |
| `composer.json` | Autoload `app/Helpers/global.php`. |
| `app/Services/EntityTranslationService.php` | Service tổng quát cho admin: `saveSeoTranslation()`, `saveEntityTranslation()`, `saveAll()`, `persistFromRequest()` (đọc `translations[]` từ request + auto fallback default locale), `loadAllTranslations()`. |

#### Views / i18n
| Tệp | Mô tả |
| --- | --- |
| `resources/views/main/snippets/head.blade.php` | Emit `<link rel="alternate" hreflang>` cho từng locale, `x-default`, `canonical`. |
| `resources/views/main/schema/social.blade.php` | `og:locale` từ `current_language()->og_locale`; `og:locale:alternate` cho mỗi locale khác. |
| `resources/views/main/layouts/main.blade.php` + `layouts/booking.blade.php` | `<html lang="{{ current_locale() }}" dir="...">`. |
| `resources/views/main/snippets/headerTop.blade.php` | Language switcher tự động từ `Language::active()`; URL theo `SeoAlternates::for()` nếu có entity; fallback strip-locale-prefix. |
| `resources/views/admin/snippets/formMultilingualTabs.blade.php` | Tabbed UI tái sử dụng cho admin form (SEO + entity fields per-locale, badge default). |
| `lang/vi/main.php`, `lang/en/main.php`, `lang/zh/main.php`, `lang/ja/main.php`, `lang/ko/main.php` | UI strings (booking, navigation, common labels, validation). |
| `resources/views/main/tourBooking/form.blade.php` | Localize toàn bộ label/placeholder bằng `__('main.booking.*')`. |

#### Admin (mẫu, áp dụng pattern)
| Tệp | Mô tả |
| --- | --- |
| `app/Http/Controllers/AdminTourController.php` | Áp pattern: `view()` nạp `Language::active()` + `EntityTranslationService::loadAllTranslations()`; `create()`/`update()` gọi `EntityTranslationService::persistFromRequest()`. |
| `app/Http/Controllers/AdminBlogController.php` | Như trên. |
| `app/Http/Controllers/AdminPageController.php` | Như trên. |
| `resources/views/admin/tour/view.blade.php` | Thêm card "Phiên bản đa ngôn ngữ" include `formMultilingualTabs.blade.php`. |

### 3.5. Phần giữ nguyên

- Logic booking (tour/ship/service/combo/hotel) — chỉ cần i18n UI strings (đã làm cho `tourBooking`).
- Domain models entity (Tour, Ship, Service, …) — chỉ tách phần "ngôn ngữ" ra translation table.
- Quan hệ relation/pivot — gần như không đổi.
- Cấu trúc thư mục view + schema partials.

---

## 4. Thiết kế lại hệ thống đa ngôn ngữ

> **Lưu ý V3.0**: phần §4 dưới đây mô tả chi tiết kiến trúc Phase 1 + Phase 2 đã triển khai (xem §0 để có TLDR). Phần §4.6 (admin UI) **đã thay đổi căn bản trong V3.0**: thay vì tabs nhúng trong form chính, mỗi ngôn ngữ là 1 trang dịch riêng — xem §0.7 và `app/Http/Controllers/AdminTranslationController.php`.

### 4.1. Triết lý thiết kế

1. **Mỗi ngôn ngữ là một "trang đầy đủ nội dung dịch riêng"**: tiêu đề, mô tả, slug, body content, **và toàn bộ relations (timetables, options, FAQ, content sections)** đều có bản dịch độc lập (không phải auto-translate).
2. **Vẫn giữ một entity gốc duy nhất** (1 tour = 1 dòng `tour_info`). Mỗi ngôn ngữ chỉ là một bản dịch gắn vào entity đó qua `seo_id`.
3. **Các phiên bản ngôn ngữ được link qua `seo_id`** — UI public có language switcher tự build URL từ `seo_alternates()`; admin có panel "Phiên bản dịch" hiển thị status từng locale.
4. **URL chứa locale** (subfolder) — chuẩn SEO Google khuyến nghị, dễ deploy, dễ cache, dễ maintain.
5. **`hreflang` đầy đủ** cho mọi trang public → Google biết ánh xạ giữa các phiên bản.
6. **V3.0 fallback policy**: ưu tiên trả nội dung locale hiện tại; nếu thiếu translation cho 1 segment breadcrumb / 1 field → fallback default locale (không 500). Nếu KHÔNG có URL hợp lệ ở locale hiện tại → 404. (Phase 1 cũ trả 404 quá strict, gây UX kém khi rollout từng phần.)
7. **Backward compatible URL VN**: URL tiếng Việt hiện tại giữ nguyên (mặc định `vi`, không thêm prefix `/vi/`) → **không mất SEO**.

### 4.2. Lựa chọn URL strategy

Bảng so sánh:

| Phương án                | Ưu                                                              | Nhược                                                       |
| ------------------------ | --------------------------------------------------------------- | ----------------------------------------------------------- |
| `vi.hitour.dev` (subdomain) | Phân vùng index sạch, dễ phân server theo region.             | Chia sẻ link juice yếu, SSL/cookie tách, deploy phức tạp.   |
| **`/en/...`, `/vi/...` (subfolder)** ⭐ | **Cùng domain → chia sẻ authority**, dễ deploy, GSC quản 1 property. | Phải xử lý middleware locale.                              |
| `?lang=en` (query)        | Đơn giản nhất.                                                 | **Không nên** — Google đề xuất tránh, gây canonical phức tạp. |
| `hitour.com.en` (ccTLD)   | Tốt nhất cho geo-targeting cứng.                               | Tốn nhiều domain, phức tạp deploy.                         |

**Khuyến nghị: subfolder** — và ngôn ngữ mặc định `vi` **không có prefix** để giữ nguyên SEO hiện tại.

```
URL chuẩn dự kiến:
https://hitour.dev/tour-phu-quoc                       → vi (default, no prefix)
https://hitour.dev/en/phu-quoc-tours                   → en
https://hitour.dev/zh/fu-guo-dao-lv-you                → zh
https://hitour.dev/en/phu-quoc-tours/discover-island   → en (chi tiết tour)
```

### 4.3. Database design

Có 3 hướng tiếp cận chính. Chúng tôi đề xuất hướng **(B) Translation Table** vì rõ ràng, dễ index, dễ admin nhập liệu, scale tốt.

#### 4.3.1. So sánh các hướng

| Hướng                                | Ưu                                              | Nhược                                              |
| ------------------------------------ | ----------------------------------------------- | -------------------------------------------------- |
| (A) JSON column trong `seo` (`title->{vi,en,zh}`) | 1 row 1 entity, query gọn.                  | Khó index, khó full-text search theo locale, validate phức tạp. |
| **(B) Translation table chuyên biệt** ⭐ | Index theo `(locale, slug)` siêu nhanh; rõ ràng schema; dễ partial-translate; dễ admin UI. | Phải JOIN nhiều hơn 1 chút.                       |
| (C) Nhân bảng theo locale (`seo_en`, `seo_zh`) | Đơn giản đọc.                                  | Bảo trì kinh khủng, khó thêm ngôn ngữ mới.         |

#### 4.3.2. Schema đề xuất (B)

##### `languages` — danh mục ngôn ngữ

```sql
CREATE TABLE languages (
    id              TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code            VARCHAR(10) NOT NULL UNIQUE,    -- vi, en, zh, ko, ja, fr, ...
    name            VARCHAR(50) NOT NULL,           -- "Tiếng Việt", "English"
    locale          VARCHAR(10) NOT NULL,           -- vi_VN, en_US, zh_CN
    hreflang        VARCHAR(10) NOT NULL,           -- vi, en, zh-CN, x-default
    flag            VARCHAR(255) NULL,
    is_default      TINYINT(1) NOT NULL DEFAULT 0,  -- 1 cho 'vi'
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    ordering        SMALLINT UNSIGNED DEFAULT 0,
    fallback_id     TINYINT UNSIGNED NULL,          -- ngôn ngữ thay thế nếu không có bản dịch
    direction       ENUM('ltr','rtl') DEFAULT 'ltr',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

##### `seo` — giữ "thông tin chung không phụ thuộc ngôn ngữ"

```sql
ALTER TABLE seo
    DROP COLUMN title,                      -- chuyển sang seo_translation
    DROP COLUMN description,
    DROP COLUMN seo_title,
    DROP COLUMN seo_description,
    DROP COLUMN slug,
    DROP COLUMN slug_full;
-- giữ lại: id, image, image_small, level, parent, ordering, topic, type,
--          rating_*, video, link_canonical, created_by, timestamps
ALTER TABLE seo
    ADD COLUMN reference_table VARCHAR(64) NOT NULL AFTER type,   -- tên bảng entity
    ADD COLUMN reference_id    BIGINT UNSIGNED NOT NULL AFTER reference_table;
CREATE INDEX idx_seo_ref ON seo(reference_table, reference_id);
```

> Cột `reference_*` cho phép truy ngược từ `seo` về entity gốc mà không phải JOIN từng bảng.

##### `seo_translations` — phiên bản nội dung theo ngôn ngữ ⭐ trái tim mới

```sql
CREATE TABLE seo_translations (
    id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    seo_id                  BIGINT UNSIGNED NOT NULL,
    language_id             TINYINT UNSIGNED NOT NULL,

    -- Nội dung phụ thuộc ngôn ngữ
    title                   VARCHAR(255) NOT NULL,
    description             TEXT NULL,
    seo_title               VARCHAR(255) NULL,        -- override tag <title>
    seo_description         VARCHAR(320) NULL,        -- meta description
    slug                    VARCHAR(255) NOT NULL,    -- phần URL của riêng node này
    slug_full               VARCHAR(1024) NOT NULL,   -- URL đầy đủ (đã ghép parent)
    link_canonical          VARCHAR(1024) NULL,       -- override canonical (ít dùng)
    keywords                VARCHAR(500) NULL,        -- meta keywords (optional)
    og_image                VARCHAR(255) NULL,        -- override og:image theo locale (optional)

    -- Trạng thái dịch
    status                  ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    translation_status      ENUM('manual','auto','reviewed') NOT NULL DEFAULT 'manual',
    translated_by           BIGINT UNSIGNED NULL,
    translated_at           TIMESTAMP NULL,
    created_at              TIMESTAMP NULL,
    updated_at              TIMESTAMP NULL,

    UNIQUE KEY uk_seo_language (seo_id, language_id),
    UNIQUE KEY uk_language_slugfull (language_id, slug_full(255)),  -- chống trùng URL/locale
    KEY idx_language_slug (language_id, slug(191)),
    KEY idx_status (status),
    CONSTRAINT fk_seo_translations_seo FOREIGN KEY (seo_id) REFERENCES seo(id) ON DELETE CASCADE,
    CONSTRAINT fk_seo_translations_language FOREIGN KEY (language_id) REFERENCES languages(id)
);
```

##### Bảng entity (Tour, Ship, Service, …)

Giữ nguyên cột `seo_id` & các cột "không phụ thuộc ngôn ngữ" (giá, ngày, đối tác, location, …). **Tách phần phụ thuộc ngôn ngữ** sang bảng `<entity>_translations`. Ví dụ:

```sql
-- BỎ các cột không cần trong tour_info: name (đa ngữ → đẩy sang translation)
-- Giữ: seo_id, code, price_show, price_del, departure_schedule, days, nights, time_start, ...

CREATE TABLE tour_info_translations (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tour_info_id    BIGINT UNSIGNED NOT NULL,
    language_id     TINYINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,         -- tên tour theo ngôn ngữ
    pick_up         VARCHAR(255) NULL,
    transport       VARCHAR(255) NULL,
    UNIQUE KEY uk_tour_lang (tour_info_id, language_id),
    KEY idx_lang (language_id),
    CONSTRAINT fk_titrans_tour FOREIGN KEY (tour_info_id) REFERENCES tour_info(id) ON DELETE CASCADE,
    CONSTRAINT fk_titrans_lang FOREIGN KEY (language_id) REFERENCES languages(id)
);
```

Áp dụng tương tự cho:

- `tour_location_translations(name, display_name, description)`
- `tour_content_translations(special_content, special_list, include, not_include, policy_child, menu, hotel, policy_cancel, note)` — dài text, đa ngữ
- `tour_timetable_translations(content)`
- `tour_option_translations(name, description)`
- `service_info_translations(name)`, `service_location_translations(name, display_name, description)`
- `ship_info_translations(name, name_round, note)`, `ship_location_translations(name, description, note)`
- `air_*_translations`, `combo_*_translations`, `hotel_*_translations`, `category_translations`, `blog_translations`, `page_translations`, `guide_translations`, `question_answer_translations`.

> Pattern chung: bảng `<entity>_translations` luôn có `(id, <entity>_id, language_id, ... text fields, UNIQUE(<entity>_id, language_id))`.

##### Trait Eloquent dùng chung

```php
// app/Models/Concerns/HasTranslations.php
namespace App\Models\Concerns;

use App\Models\Language;

trait HasTranslations {
    /** Quan hệ N translations */
    public function translations(){
        $class = $this->translationClass();
        $fk    = $this->getForeignKey(); // tour_info_id
        return $this->hasMany($class, $fk, 'id');
    }

    /** Lấy translation theo locale hiện tại (lazy). */
    public function trans(?string $locale = null){
        $locale  = $locale ?: app()->getLocale();
        $langId  = Language::idByCode($locale); // cache lookup
        return $this->translations->firstWhere('language_id', $langId);
    }

    /** Magic getter: $tour->name → trans()->name (fallback null) */
    public function getAttribute($key){
        if(in_array($key, $this->translatable ?? [])){
            return optional($this->trans())->{$key};
        }
        return parent::getAttribute($key);
    }

    abstract protected function translationClass(): string;
}
```

Mỗi model entity:

```php
class Tour extends Model {
    use HasTranslations;
    protected $translatable = ['name', 'pick_up', 'transport'];
    protected function translationClass(): string { return TourInfoTranslation::class; }
    public function seo(){ return $this->hasOne(Seo::class, 'id', 'seo_id'); }
}
```

#### 4.3.3. Slug & URL hierarchy đa ngôn ngữ

`slug_full` được build trong `seo_translations` cho từng ngôn ngữ:

```php
// SeoTranslationService.php
public static function buildSlugFull(int $seoId, int $languageId): string {
    $node = SeoTranslation::where('seo_id', $seoId)
                ->where('language_id', $languageId)
                ->firstOrFail();
    $seo  = Seo::find($seoId);
    if($seo->parent == 0 || $seo->level == 1) return $node->slug;

    $parentSeo  = Seo::find($seo->parent);
    $parentTrans= SeoTranslation::where('seo_id', $parentSeo->id)
                                ->where('language_id', $languageId)
                                ->first();
    // Nếu cha chưa có bản dịch trong locale này → fallback sang locale default
    // hoặc reject (tuỳ business rule). Khuyến nghị: REJECT để admin phải dịch theo cây
    if(!$parentTrans){
        throw new \DomainException("Parent slug missing in locale {$languageId}");
    }
    return $parentTrans->slug_full . '/' . $node->slug;
}
```

> **Quy tắc bắt buộc**: muốn xuất bản node X locale L → toàn bộ cha của X phải đã được dịch ở L. Điều này đảm bảo không có URL "lai" giữa các locale.

### 4.4. Routing & SEO logic

#### 4.4.1. Cấu trúc route

```php
// routes/web.php (đoạn cuối, thay thế catch-all hiện tại)

use App\Http\Middleware\DetectLocale;

Route::middleware([DetectLocale::class])->group(function () {

    // Locale prefix optional (vi không có prefix)
    Route::get('/', [HomeController::class, 'home'])->name('main.home');
    Route::get('/sitemap.xml',          [SitemapController::class, 'main']);
    Route::get('/sitemap/{locale}.xml', [SitemapController::class, 'index']);
    Route::get('/sitemap/{locale}/{type}.xml', [SitemapController::class, 'child']);

    // Catch-all cho ngôn ngữ default (không prefix)
    Route::get('/{slug1?}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}/{slug7?}/{slug8?}', [RoutingController::class, 'route'])
         ->where(['slug1' => '^(?!en|zh|ko|ja|fr).*'])  // không match prefix locale
         ->name('routing.default');

    // Catch-all cho non-default locale
    Route::prefix('{locale}')->whereIn('locale', ['en','zh','ko','ja','fr'])->group(function () {
        Route::get('/', [HomeController::class, 'home'])->name('main.home.localized');
        Route::get('/{slug1?}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}/{slug7?}/{slug8?}', [RoutingController::class, 'route'])
             ->name('routing.localized');
    });
});
```

#### 4.4.2. Middleware `DetectLocale`

```php
// app/Http/Middleware/DetectLocale.php
class DetectLocale {
    public function handle($request, Closure $next){
        $segments = $request->segments();
        $first    = $segments[0] ?? null;
        $supported= Language::activeCodes();         // ['vi','en','zh',...] (cache)
        $default  = Language::defaultCode();         // 'vi'

        if(in_array($first, $supported, true) && $first !== $default){
            app()->setLocale($first);
            $request->attributes->set('locale', $first);
            $request->attributes->set('strippedPath', '/' . implode('/', array_slice($segments, 1)));
        }else {
            app()->setLocale($default);
            $request->attributes->set('locale', $default);
            $request->attributes->set('strippedPath', '/' . implode('/', $segments));
        }
        return $next($request);
    }
}
```

#### 4.4.3. RoutingController v2 — kế hoạch nâng cấp từ Phase 0

> **Phase 0 đã chuẩn bị sẵn nền tảng**: `RoutingController` hiện đã sử dụng `dispatch()` với `match()` + `render<Type>()` riêng cho từng kiểu trang, đi qua `HtmlCacheService` injection. Phase 1 chỉ cần thay đổi nhỏ:
> 1. Đặt thêm middleware `DetectLocale` để gán `$locale` vào request.
> 2. Đổi `Url::checkUrlExists()` để query `seo_translations` thay vì `seo`.
> 3. Truyền `$locale` cho cả `HtmlCacheService::buildKey()` (param `$namespace`) lẫn các `render<Type>()`.

Pseudo-code phase 1 (kế thừa code Phase 0):

```php
public function routing(Request $request, HtmlCacheService $cache) {
    $locale     = $request->attributes->get('locale');         // do middleware DetectLocale gán
    $segments   = Url::cleanRequestPath(rawurldecode($request->path()));
    $urlRequest = implode('/', $segments);

    // Lookup từ seo_translations qua (locale, slug_full)
    $trans = SeoTranslation::with('seo', 'alternates')
                ->whereHas('language', fn($q) => $q->where('code', $locale))
                ->where('slug_full', $urlRequest)
                ->where('status', 'published')
                ->first();
    if (!$trans) return ErrorController::error404();

    // Cache key đa ngôn ngữ — namespace = locale (HtmlCacheService đã hỗ trợ từ Phase 0)
    $cacheKey = HtmlCacheService::buildKey($trans->slug_full, ['page' => $request->query('page')], $locale);

    $html = $cache->getOrRender($cacheKey, fn() => $this->dispatch($trans, $locale));
    if (empty($html)) return ErrorController::error404();
    echo $html;
}

private function dispatch(SeoTranslation $trans, string $locale): ?string {
    return match ($trans->seo->type) {
        'tour_location' => $this->renderTourLocation($trans, $locale),
        'tour_info'     => $this->renderTour($trans, $locale),
        // ... mọi handler khác đã có từ Phase 0, chỉ thêm tham số $locale
        default         => null,
    };
}
```

Lý do giữ pattern hiện tại (sau Phase 0) thay vì chuyển hẳn sang `*PageRenderer` riêng biệt:

- `*PageRenderer` lý tưởng nhưng chi phí refactor lớn (20+ class) và lợi ích hiệu năng không đáng kể so với `dispatch()` hiện có.
- Khi entity nào trở nên phức tạp (>30 dòng) → tách riêng class renderer ngay tại Phase 2 (incremental, không cần refactor toàn bộ).

#### 4.4.4. Fallback ngôn ngữ — quy tắc khuyến nghị

| Trường hợp                                                    | Hành xử |
| ------------------------------------------------------------- | ------- |
| Có URL chuẩn xác (`(locale, slug_full)` khớp `published`)     | 200 render. |
| Có translation nhưng `status != published`                    | 404 (an toàn cho SEO; tránh thin content). |
| Không có translation cho locale, có ở locale khác             | 404 + (tùy chọn) thêm `<link rel="alternate">` trong 404 page để hint. **Không** auto-redirect sang locale khác → tránh duplicate SEO. |
| Có translation nhưng cha chưa dịch                            | 404 (URL không thể xây). |
| URL prefix locale tồn tại nhưng path rỗng                     | Render homepage locale đó. |

#### 4.4.5. Hreflang & Canonical

`resources/views/main/snippets/head.blade.php` thêm:

```php
@php
    $alternates = $alternates ?? collect();   // truyền từ controller — danh sách (locale, url_full)
    $currentLocale = app()->getLocale();
    $defaultLocale = config('app.default_locale', 'vi');
@endphp

@foreach($alternates as $alt)
    <link rel="alternate" hreflang="{{ $alt->hreflang }}" href="{{ $alt->url }}" />
@endforeach
{{-- x-default trỏ về phiên bản default (vi) --}}
@if($default = $alternates->firstWhere('hreflang', $defaultLocale))
    <link rel="alternate" hreflang="x-default" href="{{ $default->url }}" />
@endif

<link rel="canonical" href="{{ $canonicalUrl }}" />
<meta property="og:locale" content="{{ $ogLocale }}" />
@foreach($alternates as $alt)
    @if($alt->hreflang !== $currentLocale)
        <meta property="og:locale:alternate" content="{{ $alt->ogLocale }}" />
    @endif
@endforeach
```

Service helper:

```php
// app/Services/SeoAlternates.php
public function for(Seo $seo): Collection {
    return SeoTranslation::where('seo_id', $seo->id)
        ->where('status','published')
        ->with('language')
        ->get()
        ->map(fn($t) => (object)[
            'hreflang' => $t->language->hreflang,
            'ogLocale' => $t->language->locale,
            'url'      => url($t->language->is_default ? '/'.$t->slug_full : '/'.$t->language->code.'/'.$t->slug_full),
        ]);
}
```

Các yêu cầu hreflang chuẩn Google:

1. **Tự tham chiếu (self)**: trang en phải có `hreflang="en"` trỏ về chính nó.
2. **Đối xứng (bidirectional)**: A trỏ B thì B phải trỏ A.
3. **`x-default`**: bắt buộc cho ít nhất 1 phiên bản (thường là `vi`).
4. **Tuyệt đối**: dùng absolute URL (`https://hitour.dev/...`).
5. **Khớp robots/index**: trang nào noindex thì không liệt vào hreflang.

#### 4.4.6. Sitemap đa ngôn ngữ

```xml
<!-- sitemap/index.xml -->
<sitemapindex>
  <sitemap><loc>/sitemap/vi.xml</loc></sitemap>
  <sitemap><loc>/sitemap/en.xml</loc></sitemap>
  <sitemap><loc>/sitemap/zh.xml</loc></sitemap>
</sitemapindex>

<!-- sitemap/en.xml -->
<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml">
  <url>
    <loc>https://hitour.dev/en/phu-quoc-tours</loc>
    <xhtml:link rel="alternate" hreflang="vi" href="https://hitour.dev/tour-phu-quoc"/>
    <xhtml:link rel="alternate" hreflang="en" href="https://hitour.dev/en/phu-quoc-tours"/>
    <xhtml:link rel="alternate" hreflang="zh" href="https://hitour.dev/zh/fu-guo-dao-lv-you"/>
    <xhtml:link rel="alternate" hreflang="x-default" href="https://hitour.dev/tour-phu-quoc"/>
    <lastmod>2026-05-04T10:00:00+07:00</lastmod>
    <changefreq>weekly</changefreq>
  </url>
  ...
</urlset>
```

### 4.5. Storage content blade — multi-locale

> **Trạng thái Phase 0**: vẫn lưu theo pattern cũ `storage/app/public/contents/<type>/<slug>.blade.php`, nhưng đã được "thuần hoá": `Seo::renameContentBladeFiles()` tự rename file khi đổi slug, và `RoutingController::renderContentBlade()` đọc qua `config/tablemysql.php` (không hard-code path).
>
> **Phase 1 tiến hoá**: chuyển sang một trong hai phương án dưới đây.

#### Phương án A — Lưu theo `seo_id` (khuyến nghị nếu giữ Blade)

```
storage/app/public/contents/tours/<seo_id>/<locale>.blade.php
```

- Bền vững khi rename slug — chỉ ánh xạ qua ID.
- Không lo trùng slug giữa các entity cùng type.

```php
// app/Services/ContentStorage.php
public function path(string $type, int $seoId, string $locale): string {
    $dir = config("tablemysql.{$type}.content_dir");   // đã có từ Phase 0
    return $dir . "{$seoId}/{$locale}.blade.php";
}

public function get(string $type, int $seoId, string $locale): string {
    $primary = $this->path($type, $seoId, $locale);
    if (\Storage::exists($primary)) return \Storage::get($primary);
    // Khuyến nghị: empty + cảnh báo, KHÔNG tự fallback nội dung VN cho EN.
    return '';
}
```

#### Phương án B — Lưu DB (theo zenpot)

```sql
CREATE TABLE seo_content (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    seo_id BIGINT NOT NULL,                  -- liên kết tới seo (entity gốc)
    seo_translation_id BIGINT NOT NULL,      -- liên kết tới seo_translations (locale-aware)
    content LONGTEXT NULL,
    ordering INT DEFAULT 0,
    INDEX (seo_id),
    INDEX (seo_translation_id)
);
```

- Ưu: lưu DB → query/replace internal links dễ; backup/restore đồng bộ; admin WYSIWYG/blocks.
- Nhược: mất khả năng nhúng PHP/Blade directives. Đổi lại bằng helper `[[component]]` / Markdown.

> **Quyết định** giữa A và B sẽ chọn ở đầu Phase 1 dựa trên: (1) số file blade hiện tại, (2) số directive Blade thực sự đang dùng trong content, (3) workflow biên tập viên.

### 4.6. CMS / Admin UI — workflow nhập đa ngôn ngữ

> **THAY ĐỔI V3.0**: §4.6.1 và §4.6.2 dưới đây mô tả phương án Phase 1 (tabs nhúng trong form chính, chỉ dịch SEO). Phương án này đã bị THAY THẾ trong Phase 2 vì không scale được khi cần dịch sâu (timetables, options, FAQ, content sections). Phương án V3.0:
>
> - **Form admin gốc** (`/he-thong/<entity>/view?id=…`) chỉ chỉnh **default locale + master data** (giá, ngày, FK, ảnh). Cuối form có panel **"Phiên bản dịch"** (`resources/views/admin/snippets/translationStatusPanel.blade.php`) liệt kê 10 ngôn ngữ active với badge SEO/Body status + nút **"Dịch sang …"**.
> - **Trang dịch riêng** (`/he-thong/translation/{locale}/{seoId}`, controller `AdminTranslationController`) chứa MỌI field dịch được trong 1 form duy nhất:
>   - SEO (title/description/seo_*/slug/canonical)
>   - Entity translatable fields (name, pick_up, transport, …)
>   - Body content (`seo_content_translations.content` — HTML/Blade)
>   - **Mọi relation** mô tả trong `config('tablemysql.<type>.translation_relations')`: tour_content (sections), tour_timetable, tour_option, FAQ, …
> - Mỗi field hiển thị **placeholder = nội dung default locale** để translator dễ đối chiếu.
> - Submit POST 1 lần → 1 transaction → upsert SEO + entity + body + relations.
> - Có nút **"Xoá bản dịch"** xoá toàn bộ translation cho locale đó (an toàn, không động đến default).

(Phần dưới đây giữ nguyên để tham chiếu lịch sử Phase 1.)

#### 4.6.1. UI/UX (Phase 1 — đã thay thế bởi V3.0)

- Mỗi form `view` của entity (Tour/Ship/Service/…) thêm **tab Language Switcher** trên cùng. Mỗi tab tương ứng 1 record `*_translation` + `seo_translation`.
- Tab có badge trạng thái: `Đã dịch / Bản nháp / Chưa dịch / Cần review`.
- Cho phép **copy nội dung từ ngôn ngữ X → Y** làm starting point (đánh dấu `translation_status = auto`).
- Slug/Title/Description nhập riêng từng tab — không auto-fill (để giữ keyword độc lập).
- Hiển thị **preview hreflang** ngay trong form.
- Có nút **"Xuất bản tất cả phiên bản"** hoặc xuất bản từng locale riêng.
- Validate: không cho `published` nếu cha ở cùng locale chưa `published`.

#### 4.6.2. Backend pattern

```php
// AdminTourController@update (sau refactor)
public function update(TourRequest $req){
    DB::transaction(function() use ($req) {
        // 1. cập nhật entity gốc (không phụ thuộc lang)
        Tour::updateItem($req->id, $req->only(['code','price_show','days', 'tour_departure_id', ...]));

        // 2. cập nhật seo gốc (không phụ thuộc lang)
        Seo::updateItem($seo->id, $req->only(['image','image_small','ordering','rating_*']));

        // 3. lặp qua từng locale gửi lên
        foreach($req->translations as $localeCode => $payload){
            $langId = Language::idByCode($localeCode);
            SeoTranslation::updateOrCreate(
                ['seo_id' => $seo->id, 'language_id' => $langId],
                [
                    'title'           => $payload['title'],
                    'description'     => $payload['description'],
                    'seo_title'       => $payload['seo_title'] ?? $payload['title'],
                    'seo_description' => $payload['seo_description'] ?? $payload['description'],
                    'slug'            => Str::slug($payload['slug']),
                    'slug_full'       => SeoTranslationService::buildSlugFull($seo->id, $langId),
                    'status'          => $payload['status'] ?? 'draft',
                    'translated_by'   => auth()->id(),
                    'translated_at'   => now(),
                ]
            );

            TourInfoTranslation::updateOrCreate(
                ['tour_info_id' => $tour->id, 'language_id' => $langId],
                ['name' => $payload['name'], 'pick_up' => $payload['pick_up'], 'transport' => $payload['transport']]
            );

            // 4. ghi content blade theo locale
            ContentStorage::put('tour', $seo->id, $localeCode, $payload['content']);
        }

        // 5. trigger rebuild slug_full của children theo từng locale (đệ quy)
        SeoTranslationService::rebuildChildrenSlugs($seo->id);

        // 6. xoá cache HTML các locale liên quan
        Cache::tags(["seo:{$seo->id}"])->flush();
    });
}
```

#### 4.6.3. Workflow

```
[Tạo entity ở locale gốc vi]
        ↓
[Copy sang nháp các locale khác]
        ↓
[Translator dịch / chỉnh từng tab]
        ↓
[QA review → set status = reviewed]
        ↓
[Editor publish locale tương ứng]
        ↓
[Trigger flush cache + invalidate sitemap]
```

Bổ sung **bảng audit** `translation_logs(seo_translation_id, action, by_user, before_json, after_json, created_at)` để truy vết.

### 4.7. UI strings (i18n FE)

- Sử dụng Laravel Localization native: `resources/lang/{locale}/messages.php` cho các nhãn UI (button, navbar, footer, validation).
- Helper `__('messages.book_now')` ở mọi blade.
- Giữ tách bạch **content (lưu DB/Blade storage) ↔ UI labels (lưu lang file)**.

### 4.8. Cache strategy đa ngôn ngữ

> Cơ sở hạ tầng cache đã có sẵn từ Phase 0 (`HtmlCacheService`). Phase 1 chỉ cần truyền `namespace = $locale` vào `HtmlCacheService::buildKey()`.

| Loại cache         | Key (qua `HtmlCacheService::buildKey`)     | TTL    | Hiện trạng |
| ------------------ | ------------------------------------------ | ------ | ---------- |
| HTML toàn trang    | `{locale}/{slug_full-with-params}.html.gz` | `config('main.cache.ttl')` (mặc định 30 ngày, có thể tinh chỉnh xuống 30m) | ✅ Service đã sẵn (Phase 0) |
| Header menu        | `{locale}/menu` (đặt tay khi build header) | 1h     | Phase 1 |
| Sitemap            | `sitemaps/{locale}/{type}` | TTL service | ✅ SitemapController đã đi qua HtmlCacheService (Phase 0) |
| Language list      | `languages:active` (Laravel Cache)         | 24h    | Phase 1 |
| Mapping `slug→trans`| in-memory cache `Url::checkUrlExists`     | 1 request | ✅ Phase 0 |

Phase 2 (tùy chọn): chuyển sang Redis tag-based invalidation — mỗi entity có tag `seo:{id}` → khi update tự flush.

### 4.9. Bảng tổng hợp các bảng dịch sẽ tạo

| Entity             | Bảng translations                      | Cột `*_translatable` |
| ------------------ | -------------------------------------- | -------------------- |
| `seo`              | `seo_translations`                     | title, description, seo_title, seo_description, slug, slug_full, link_canonical |
| `tour_location`    | `tour_location_translations`           | name, display_name, description |
| `tour_info`        | `tour_info_translations`               | name, pick_up, transport |
| `tour_content`     | `tour_content_translations`            | special_content, special_list, include, not_include, policy_child, menu, hotel, policy_cancel, note |
| `tour_timetable`   | `tour_timetable_translations`          | content |
| `tour_option`      | `tour_option_translations`             | name, description |
| `tour_continent`   | `tour_continent_translations`          | name, description |
| `tour_country`     | `tour_country_translations`            | name, description |
| `tour_info_foreign`| `tour_info_foreign_translations`       | name, pick_up, transport |
| `tour_content_foreign` | `tour_content_foreign_translations`| (giống `tour_content_translations`) |
| `service_location` | `service_location_translations`        | name, display_name, description |
| `service_info`     | `service_info_translations`            | name |
| `service_option`   | `service_option_translations`          | name, description |
| `ship_location`    | `ship_location_translations`           | name, description, note |
| `ship_info`        | `ship_info_translations`               | name, name_round, note |
| `ship_partner`     | `ship_partner_translations`            | name, description |
| `air_location`     | `air_location_translations`            | name, description |
| `air_info`         | `air_info_translations`                | name |
| `air_partner`      | `air_partner_translations`             | name, description |
| `combo_location`   | `combo_location_translations`          | name, description |
| `combo_info`       | `combo_info_translations`              | name, ... |
| `hotel_location`   | `hotel_location_translations`          | name, description |
| `hotel_info`       | `hotel_info_translations`              | name, address |
| `hotel_room`       | `hotel_room_translations`              | name, description |
| `hotel_facility`   | `hotel_facility_translations`          | name |
| `carrental_location`| `carrental_location_translations`     | name, description |
| `guide_info`       | `guide_info_translations`              | name, description |
| `category_info`    | `category_info_translations`           | name, description |
| `blog_info`        | `blog_info_translations`               | name, description |
| `page`             | `page_translations`                    | name, description |
| `question_answer_info` | `question_answer_translations`     | question, answer |

> Tổng cộng ~30 bảng `_translations`. Có thể sinh tự động bằng artisan command custom (xem mục 5.6).

---

## 5. Kế hoạch nâng cấp & Migration

### 5.1. Nguyên tắc bất di bất dịch

1. **Không downtime production VN**: triển khai song song; URL VN giữ nguyên 100%.
2. **Không mất index Google**: tất cả URL hiện tại tiếp tục `200` hoặc redirect `301` về URL mới (nếu thay đổi).
3. **Migrate có thể rollback**: từng giai đoạn deploy có flag tắt/bật.
4. **Tách tách tách**: logic, data, view tách rõ — đa ngôn ngữ là layer trên cùng.

### 5.2. Lộ trình 6 giai đoạn

```
Phase 0  ─────►  Phase 1  ─────►  Phase 2  ─────►  Phase 3  ─────►  Phase 4  ─────►  Phase 5
Hardening        Schema +         Áp pattern         Frontend          Nhập liệu        Mở rộng
✅ DONE          Multilingual     admin còn lại     Multi-lang         EN/ZH/...        ngôn ngữ mới
                 ✅ DONE          IN PROGRESS       ✅ DONE
```

#### Phase 0 — Hardening hệ thống đơn ngữ ✅ HOÀN THÀNH

Phase 0 đã được triển khai trước khi viết tài liệu này (xem chi tiết tại §3.3). Tóm tắt:

- ✅ `HtmlCacheService` (gzip + minify HTML/JS/CSS, đa disk, key có namespace cho locale).
- ✅ Middleware `CheckRedirect` (utf8mb4_bin) thay foreach `Redirect::all()`.
- ✅ Tối ưu `Seo::buildFullUrl` về O(1) + auto cascade slug_full + auto tạo 301 redirect khi đổi slug.
- ✅ `Url::checkUrlExists` query bằng `slug_full` (an toàn, dùng index).
- ✅ Refactor `RoutingController` thành dispatch + render handlers nhỏ gọn.
- ✅ `config/tablemysql.php` — single source of truth cho mapping `seo.type → model/view/content_dir/with`.
- ✅ Sitemap cache qua `HtmlCacheService`.
- ✅ Migrations: indexes + collation cho `seo` & `redirect_info`.
- ✅ `composer.json` đã thêm `voku/html-min`, `matthiasmullie/minify`.

> **Việc cần làm trước khi vào Phase 1**:
> 1. `composer install` để cài 2 package minify.
> 2. `php artisan migrate` để apply 2 migration mới.
> 3. Backup full DB + storage.
> 4. Sinh **inventory URL hiện tại** (crawl `sitemap.xml` + DB `seo`) → CSV `slug_full | type | seo_id`.
> 5. Setup Google Search Console: properties, sitemap, top 100 trang traffic.

#### Phase 1 — Multilingual core ✅ HOÀN THÀNH

- [x] Migration `languages` + seed `vi/en/zh/ja/ko` (xem `database/migrations/2026_05_04_130000_create_languages_table.php`).
- [x] Migration `seo_translations` (xem `database/migrations/2026_05_04_130100_create_seo_translations_table.php`).
- [x] Migration **động** sinh ~21 bảng `<entity>_translations` + backfill `vi` (xem `database/migrations/2026_05_04_130200_create_entity_translations_tables.php`).
- [x] `HasTranslations` trait + áp cho 21 entity model.
- [x] `Seo` model refactor đọc/ghi qua `seo_translations` (magic accessor + `insertItem/updateItem` đồng bộ).
- [x] Middleware `DetectLocale` + đăng ký key `'detectLocale'` trong Kernel.
- [x] `routes/web.php` thêm group `Route::prefix('{locale}')` cho non-default + giữ group default không prefix.
- [x] `Url::checkUrlExists` query `seo_translations` theo `(language_id, slug_full)`; thêm `cleanRequestPathWithLocale()`.
- [x] `RoutingController` áp `$locale` vào dispatch + `HtmlCacheService::buildKey($slug, $params, $locale)`.
- [x] `SitemapController` split per-locale + emit `xhtml:link` hreflang annotation.
- [x] `head.blade.php` emit hreflang + canonical + x-default; `social.blade.php` og:locale động.
- [x] `<html lang>` + `dir` động (`layouts/main.blade.php` + `layouts/booking.blade.php`).
- [x] Language switcher header (`headerTop.blade.php`).
- [x] `lang/{vi,en,zh,ja,ko}/main.php` UI strings.
- [x] `EntityTranslationService` + blade `formMultilingualTabs.blade.php` (admin tabs đa ngôn ngữ).
- [x] 3 admin controller mẫu áp pattern: `AdminTourController`, `AdminBlogController`, `AdminPageController`.
- [x] Booking form `tourBooking/form.blade.php` localize bằng `__()`.

> **Việc cần làm trước khi vào Phase 2**:
> 1. Backup full DB + storage.
> 2. `php artisan migrate` để apply 3 migration mới (languages, seo_translations, dynamic entity translations).
> 3. Smoke test: `SELECT COUNT(*) FROM seo` == `SELECT COUNT(*) FROM seo_translations WHERE language_id = (vi.id)`.
> 4. Đảm bảo `composer dump-autoload` chạy lại để load `app/Helpers/global.php`.
> 5. Vào admin → mở 1 tour bất kỳ, chỉ tab `vi` được điền — confirm form hoạt động bình thường.

#### Phase 2 — Áp pattern admin cho 50+ controller còn lại (1 tuần) — IN PROGRESS

Pattern đã chuẩn hoá; mỗi controller chỉ cần 4 bước (xem chi tiết §5.4). Danh sách cần cập nhật:

- [ ] `AdminCategoryController` (entity: Category)
- [ ] `AdminTourLocationController`, `AdminTourDepartureController`, `AdminTourContinentController`, `AdminTourCountryController`, `AdminTourInfoForeignController`
- [ ] `AdminShipController`, `AdminShipLocationController`, `AdminShipPartnerController`, `AdminShipDepartureController`, `AdminShipPortController`
- [ ] `AdminServiceController`, `AdminServiceLocationController`
- [ ] `AdminAirController`, `AdminAirLocationController`, `AdminAirDepartureController`, `AdminAirPartnerController`, `AdminAirPortController`
- [ ] `AdminComboInfoController`, `AdminComboLocationController`, `AdminComboPartnerController`
- [ ] `AdminHotelInfoController`, `AdminHotelLocationController`
- [ ] `AdminCarrentalLocationController`
- [ ] `AdminGuideController`
- [ ] **Booking forms còn lại**: `shipBooking/form.blade.php`, `serviceBooking/form.blade.php`, `comboBooking/form.blade.php`, `hotelBooking/form.blade.php` — localize bằng `__('main.booking.*')` (template đã có sẵn ở `tourBooking/form.blade.php`).
- [ ] **Admin views còn lại**: thêm card "Phiên bản đa ngôn ngữ" include `admin.snippets.formMultilingualTabs` cho mỗi `view.blade.php` của entity (mẫu xem `admin/tour/view.blade.php`).
- [ ] **Quyết định Storage content blade**: chọn Phương án A (`<seo_id>/<locale>.blade.php`) hoặc Phương án B (lưu DB) — xem §4.5. Hiện Phase 1 đã hỗ trợ đọc theo locale từ `<slug>.<locale>.blade.php` (fallback `<slug>.blade.php`).

#### Phase 3 — Frontend chấp nhận locale ✅ HOÀN THÀNH

- [x] Route prefix `{locale}` đã hoạt động (`routes/web.php`).
- [x] Middleware `DetectLocale` set `app()->setLocale()` và share view variables.
- [x] Lang files `lang/{vi,en,zh,ja,ko}/main.php` đã tạo.
- [x] `<html lang>` + `og:locale` động.
- [x] Language switcher header.

#### Phase 4 — Nhập liệu & Publish locale `en` (3-6 tuần, tuỳ volume)

- [ ] Bật `is_active=1` cho `en`.
- [ ] Đào tạo team biên tập sử dụng tab dịch.
- [ ] Bắt đầu dịch theo thứ tự: `homepage → category lv1 → top 50 trang traffic → mở rộng`.
- [ ] Mỗi entity được publish `en` → ngay lập tức xuất hiện hreflang trên cả 2 phiên bản.
- [ ] Submit sitemap mới `/sitemap/index.xml` lên Google Search Console.
- [ ] Setup Google Search Console property cho subfolder `/en/`.
- [ ] Theo dõi `Coverage`, `Hreflang errors` trong GSC.

#### Phase 5 — Mở rộng locale tiếp theo + tối ưu (rolling)

- [ ] Lặp Phase 4 với `zh`, `ko`, `ja`, …
- [ ] Setup translation memory / glossary (có thể tích hợp DeepL/Google API hỗ trợ dịch nháp).
- [ ] Tối ưu cache: chuyển HTML cache sang Redis tag-based.
- [ ] Tối ưu DB: thêm index `(language_id, slug)` đã có; xem xét partial index theo `status='published'`.

### 5.3. Mapping URL cũ → URL mới (chống mất SEO)

Vì URL `vi` giữ nguyên **không có prefix** → 99% URL không đổi. Chỉ rủi ro:

| Tình huống                                                  | Giải pháp                                                                |
| ----------------------------------------------------------- | ------------------------------------------------------------------------ |
| URL VN cũ vô tình bắt đầu bằng `en/`, `zh/`, `ko/`, …       | Audit toàn bộ `slug_full` trong `seo`. Nếu có → đổi tên slug + 301.       |
| URL VN có ký tự đặc biệt / cũ không URL-safe                | Đã cleaning trong `BuildInsertUpdateModel`, kiểm thêm.                    |
| Thay đổi cấu trúc thư mục content blade                      | Tạo command `php artisan content:migrate-blade-to-id-folder`.            |
| URL `tour_info_foreign` muốn gộp vào `tour_info` trong tương lai | Quyết định business: nên giữ tách (vì là "sản phẩm khác") hay gộp. Khuyến nghị **giữ tách** ở Phase 1-5; chỉ gộp ở phase tối ưu sau (v3+). |

### 5.4. Hướng dẫn áp pattern admin cho controller còn lại

Toàn bộ pattern multilingual đã được chuẩn hoá. Để cập nhật 1 controller mới, làm 4 bước:

#### Bước 1 — Import service vào controller

```php
use App\Services\EntityTranslationService;
use App\Models\Language;
```

#### Bước 2 — Sửa method `view()` (form edit)

```php
$item = MyEntity::with('seo', 'translations.language', 'seo.translations.language', /* các quan hệ cũ */)->find($id);

$languages          = Language::active();
$translationData    = EntityTranslationService::loadAllTranslations($item->seo ?? null, $item ?? null);
$seoTranslations    = $translationData['seo'];
$entityTranslations = $translationData['entity'];
$translatableFields = (new MyEntity)->translatableFields ?? [];

return view('admin.myentity.view', compact(
    'item', 'type', /* biến cũ */,
    'languages', 'seoTranslations', 'entityTranslations', 'translatableFields'
));
```

#### Bước 3 — Sửa method `create()` và `update()` (sau `Seo::insertItem`/`updateItem` + entity insert/update)

```php
$seoModel    = Seo::find($seoId);              // hoặc $request->get('seo_id') với update
$entityModel = MyEntity::find($entityId);
if ($seoModel && $entityModel) {
    EntityTranslationService::persistFromRequest(
        $seoModel,
        $entityModel,
        (array) $request->input('translations', []),
        // SEO data cho default locale (khi form không có tab default)
        [
            'title'           => $request->get('title'),
            'description'    => $request->get('description'),
            'seo_title'      => $request->get('seo_title'),
            'seo_description'=> $request->get('seo_description'),
            'slug'           => $request->get('slug'),
            'link_canonical' => $request->get('link_canonical'),
        ],
        // Entity data cho default locale (theo $translatableFields của model)
        [
            'name'        => $request->get('title'),
            'description' => $request->get('description'),
            // ... các field khác trong $translatableFields
        ]
    );
}
```

#### Bước 4 — Thêm card "Phiên bản đa ngôn ngữ" vào `view.blade.php`

```blade
<div class="pageAdminWithRightSidebar_main_content_item">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Phiên bản đa ngôn ngữ</h4>
        </div>
        <div class="card-body">
            @include('admin.snippets.formMultilingualTabs', [
                'tabId'           => 'myentity-i18n',
                'languages'       => $languages       ?? \App\Models\Language::active(),
                'translationsSeo' => $seoTranslations ?? [],
                'translationsEnt' => $entityTranslations ?? [],
                'fields'          => $translatableFields ?? [],
                'fieldLabels'     => [
                    'name'        => 'Tên hiển thị',
                    'description' => 'Mô tả',
                ],
                'longTextFields'  => ['description'],
            ])
        </div>
    </div>
</div>
```

> Form sẽ submit về controller dưới dạng `translations[<code>][seo][...]` và `translations[<code>][entity][...]` — `EntityTranslationService::persistFromRequest()` đã xử lý sẵn.

### 5.5. Migration data scripts (gợi ý — đã có sẵn migration tự động)

> **Phase 1 đã có migration tự động** ở `database/migrations/2026_05_04_130200_create_entity_translations_tables.php`. Phần dưới chỉ giữ lại làm tham khảo cho trường hợp cần backfill thủ công.

```php
// database/migrations/2026_05_05_000001_create_languages_table.php
Schema::create('languages', function (Blueprint $t) {
    $t->tinyIncrements('id');
    $t->string('code', 10)->unique();
    $t->string('name', 50);
    $t->string('locale', 10);
    $t->string('hreflang', 10);
    $t->string('flag', 255)->nullable();
    $t->boolean('is_default')->default(false);
    $t->boolean('is_active')->default(true);
    $t->unsignedSmallInteger('ordering')->default(0);
    $t->unsignedTinyInteger('fallback_id')->nullable();
    $t->enum('direction', ['ltr','rtl'])->default('ltr');
    $t->timestamps();
});

// seed
DB::table('languages')->insert([
    ['code'=>'vi','name'=>'Tiếng Việt','locale'=>'vi_VN','hreflang'=>'vi','is_default'=>1,'is_active'=>1,'ordering'=>1],
    ['code'=>'en','name'=>'English',   'locale'=>'en_US','hreflang'=>'en','is_default'=>0,'is_active'=>0,'ordering'=>2],
    ['code'=>'zh','name'=>'中文',       'locale'=>'zh_CN','hreflang'=>'zh-CN','is_default'=>0,'is_active'=>0,'ordering'=>3],
]);
```

```php
// database/migrations/2026_05_05_000002_create_seo_translations_table.php
Schema::create('seo_translations', function (Blueprint $t) {
    $t->id();
    $t->foreignId('seo_id')->constrained('seo')->cascadeOnDelete();
    $t->unsignedTinyInteger('language_id');
    $t->string('title');
    $t->text('description')->nullable();
    $t->string('seo_title')->nullable();
    $t->string('seo_description', 320)->nullable();
    $t->string('slug');
    $t->string('slug_full', 1024);
    $t->string('link_canonical', 1024)->nullable();
    $t->string('keywords', 500)->nullable();
    $t->string('og_image')->nullable();
    $t->enum('status', ['draft','published','archived'])->default('draft');
    $t->enum('translation_status', ['manual','auto','reviewed'])->default('manual');
    $t->unsignedBigInteger('translated_by')->nullable();
    $t->timestamp('translated_at')->nullable();
    $t->timestamps();

    $t->unique(['seo_id','language_id'], 'uk_seo_lang');
    $t->unique(['language_id','slug_full'], 'uk_lang_slugfull');
    $t->index(['language_id','slug'], 'idx_lang_slug');
    $t->foreign('language_id')->references('id')->on('languages');
});
```

```php
// database/migrations/2026_05_05_000003_backfill_seo_translations_vi.php
public function up(){
    $vi = DB::table('languages')->where('code','vi')->value('id');
    DB::statement("
        INSERT INTO seo_translations
            (seo_id, language_id, title, description, seo_title, seo_description, slug, slug_full, link_canonical, status, created_at, updated_at)
        SELECT id, ?, title, description, seo_title, seo_description, slug, slug_full, link_canonical, 'published', created_at, updated_at
        FROM seo
    ", [$vi]);
}
```

```php
// database/migrations/2026_05_05_000010_create_tour_info_translations.php
Schema::create('tour_info_translations', function (Blueprint $t){
    $t->id();
    $t->foreignId('tour_info_id')->constrained('tour_info')->cascadeOnDelete();
    $t->unsignedTinyInteger('language_id');
    $t->string('name');
    $t->string('pick_up', 255)->nullable();
    $t->string('transport', 255)->nullable();
    $t->timestamps();
    $t->unique(['tour_info_id','language_id'], 'uk_tour_lang');
    $t->index('language_id');
});

// backfill
$vi = DB::table('languages')->where('code','vi')->value('id');
DB::statement("
    INSERT INTO tour_info_translations(tour_info_id, language_id, name, pick_up, transport, created_at, updated_at)
    SELECT id, ?, name, pick_up, transport, NOW(), NOW() FROM tour_info
", [$vi]);
```

> Tương tự cho 30 bảng entity.

### 5.6. Khử đơn ngôn ngữ ở bảng gốc (ở Phase 2 hoặc 3)

Sau khi đã backfill an toàn:

```php
// database/migrations/2026_05_30_000099_drop_translatable_cols_from_seo.php
Schema::table('seo', function (Blueprint $t){
    // Sau khi đã backfill và backend đã đọc qua seo_translations
    $t->dropColumn(['title','description','seo_title','seo_description','slug','slug_full','link_canonical']);
});
```

> Khuyến nghị: **giữ cột song song trong vài tuần** + dual-write để rollback dễ dàng. Drop chỉ khi monitoring confirm `seo_translations` đã ổn 100%.

### 5.7. Artisan command tự sinh translation tables (tuỳ chọn)

Nên viết 1 command đỡ tay:

```php
php artisan make:translation-table tour_info --columns=name,pick_up,transport
```

Tự sinh migration + model + áp trait, đảm bảo đồng nhất pattern 30 bảng.

### 5.8. Đảm bảo SEO không sụt

1. **Thử nghiệm A/B trên staging**: dùng Screaming Frog crawl 10K URL → diff với production trước khi deploy.
2. **Theo dõi Search Console** sát sao 4 tuần đầu sau Phase 2: chỉ số *Coverage*, *Mobile Usability*, *Page Indexing*.
3. **301 đầy đủ** với mọi URL đổi (qua bảng `redirects` đã có).
4. **Internal link audit**: mọi `route('routing', ...)` hoặc link `<a href="/abc">` phải đi qua helper `seo_url($entity, $locale)` để tự động đúng prefix locale.
5. **Đặt rel=alternate self trên trang VN ngay từ Phase 2** dù chưa có EN — để chuẩn bị.
6. **Tránh thin/duplicate content**: không xuất bản trang EN khi nội dung body trống.
7. **Robots / noindex** cho trang `draft` của locale chưa publish.

---

## 6. Best Practices & các lỗi thường gặp

### 6.1. Best practices kiến trúc đa ngôn ngữ Laravel scale lớn

1. **Tách rõ 4 layer**: `Routing` → `Locale Detection` → `Domain (entity + translation)` → `View (i18n strings)`. Đừng nhồi locale logic vào Eloquent attributes một cách vội vàng.
2. **`slug` là 1 entity của riêng locale** — không bao giờ "auto-translate slug". Để biên tập viên SEO chọn keyword phù hợp thị trường.
3. **Indexing**: bắt buộc `UNIQUE(language_id, slug_full)` để chống collision và tăng tốc lookup.
4. **Soft-delete cẩn trọng**: nếu entity gốc soft-deleted, `*_translations` nên cascade hoặc cùng soft-delete. Lookup phải `whereNull(deleted_at)` ở entity.
5. **Eager load translations theo locale hiện tại** thay vì load all:

   ```php
   $tour->load(['translations' => fn($q) => $q->where('language_id', $currentId)]);
   ```
6. **Cache map slug→entity ở mỗi locale** ở Redis với TTL ngắn (1h) — giảm query đáng kể cho catch-all routing.
7. **Test E2E mỗi locale** bằng dataset đại diện (Cypress/Dusk).
8. **Sitemap chunk** (mỗi sitemap ≤50K URL hoặc ≤50MB) — split theo locale + theo type là vừa đẹp.
9. **Schema.org `inLanguage`**: thêm vào Article/Product schema.
10. **Hreflang theo URL tuyệt đối + nhất quán** (https vs http, trailing slash) — dùng helper duy nhất.
11. **Avoid `Accept-Language` redirect tự động** — Google warning: gây cloaking, flicker. Dùng banner gợi ý chuyển ngôn ngữ thay vì redirect.
12. **Multi-region vs Multi-lingual**: nếu ngày sau cần tách `en-US` vs `en-GB` → schema `language` đã hỗ trợ qua `code` + `hreflang`.
13. **Sử dụng UUID thay AUTO_INCREMENT cho `seo` (option)** nếu cần merge data từ nhiều domain — nhưng giai đoạn hiện tại không cần.
14. **Dual-write giai đoạn migrate**: viết cả vào cột cũ + bảng mới trong 2 tuần để rollback an toàn.
15. **Không nén HTML cache theo locale chung**: tránh cross-contaminate.

### 6.2. Các lỗi thường gặp cần tránh

| # | Lỗi                                                                  | Hậu quả                                  | Phòng tránh |
| - | -------------------------------------------------------------------- | ---------------------------------------- | ----------- |
| 1 | Hreflang không đối xứng (A→B nhưng B→null)                            | Google bỏ qua hreflang, mất ranking đa locale | Helper sinh hreflang dùng cùng 1 source `seo_translations`. |
| 2 | Thiếu `x-default`                                                    | Google chọn ngẫu nhiên cho user "lạ"     | Bắt buộc emit `x-default` trỏ default locale. |
| 3 | URL có cả `/en/` lẫn `?lang=en` cùng index                           | Duplicate content                        | Chuẩn hoá: chỉ dùng subfolder, redirect query string. |
| 4 | Auto-redirect `Accept-Language`                                       | Cloaking warning, soft 404               | Chỉ gợi ý qua banner. |
| 5 | Slug locale auto = `Str::slug(translate(name))`                       | Keyword sai, mất search intent thị trường | Để con người đặt slug. |
| 6 | Cache HTML key bỏ quên `locale`                                      | Hiển thị sai ngôn ngữ                    | Đã xử lý ở Phase 0: gọi `HtmlCacheService::buildKey($slugFull, $params, $locale)` — luôn truyền `$locale` cho mọi entity public-facing. |
| 7 | Robots.txt chặn `/en/`                                               | Mất index                                | Test với `robots-tester` GSC. |
| 8 | Gửi `Vary: Accept-Language` mà không thực sự thay đổi nội dung       | CDN cache miss tăng đột biến             | Chỉ set `Vary` khi cần thật sự. |
| 9 | Form admin update entity gốc + translation không atomic              | Dữ liệu lệch                             | `DB::transaction()`. |
| 10| Thay đổi slug nhưng quên rebuild `slug_full` của con                 | Toàn bộ URL con lệch                     | Đã xử lý ở Phase 0: `Seo::updateItem()` tự gọi `updateSlugFullChildrenRecursively()` + auto tạo 301 cho cả node hiện tại và con. Phase 1 sẽ port logic này sang `seo_translations`. |
| 11| `og:locale:alternate` thiếu                                          | Facebook share sai locale                | Emit đầy đủ. |
| 12| Trộn entity gốc và translation khi truyền vào view                   | Bug "Null name" khó tìm                  | Chuẩn hoá Resource (`TourResource`) trả về dữ liệu đã merge. |
| 13| Translator nhập trùng slug đã có ở locale khác                       | Conflict route                           | UNIQUE per `(language_id, slug_full)` + form validate realtime. |
| 14| 404 page không có hreflang                                           | Cũng quan trọng cho UX (gợi ý phiên bản khác) | Render 404 partial với link sang locale có nội dung. |
| 15| Sitemap quá lớn không split                                          | Google không index hết                   | Split ≤50K URL/file. |
| 16| Migration drop cột gốc trước khi backend chuyển sang đọc translation| Sự cố production                         | Phase rõ + dual-read. |
| 17| Mất index cũ vì redirect chain (>3 hops)                              | Loss link juice                          | Redirect 1 bước trực tiếp đến đích cuối. |
| 18| Quên `noindex` trang `?page=2` đã tách locale                        | Index thừa                               | Add `<meta robots>` cho page > 1 (nếu cần). |

### 6.3. KPI / Metric theo dõi sau khi triển khai

1. **GSC**: `Pages indexed`, `Hreflang invalid`, `Coverage errors`, `Average position` từng locale.
2. **GA4**: `% session theo locale`, `bounce rate` trang dịch.
3. **Server**: tỉ lệ 200/301/404, p95 latency của catch-all route.
4. **DB**: query count `seo_translations` (nên chỉ 1 query/1 lookup nhờ index).
5. **Cache**: hit ratio theo locale.
6. **Translation coverage**: `% entity đã có bản dịch published / locale`.

---

## 7. Phụ lục

### 7.1. Checklist nhanh cho dev

**Phase 0 (đã hoàn thành — kiểm tra trước khi qua Phase 1):**
- [x] `app/Services/HtmlCacheService.php` đã có.
- [x] `app/Http/Middleware/CheckRedirect.php` đã có & đăng ký key `checkRedirect` trong `Kernel.php`.
- [x] `routes/web.php` không còn `foreach Redirect::all()`; group catch-all đã bọc `checkRedirect`.
- [x] `app/Models/Seo.php`: `buildFullUrl` O(1), `updateItem` cascade + auto 301.
- [x] `app/Helpers/Url.php`: `checkUrlExists` query `slug_full`.
- [x] `app/Http/Controllers/RoutingController.php`: dispatch + render handlers.
- [x] `app/Http/Controllers/SitemapController.php`: cache qua `HtmlCacheService`.
- [x] `app/Http/Controllers/AdminCacheController.php`: clear qua `HtmlCacheService`.
- [x] `config/tablemysql.php`, `config/main.php` (cache config).
- [x] Migrations indexes cho `seo` & `redirect_info`.
- [ ] **Cần làm**: chạy `composer install` để cài `voku/html-min` + `matthiasmullie/minify`.
- [ ] **Cần làm**: chạy `php artisan migrate` để apply 2 migration mới.

**Phase 1 (multilingual core) ✅ HOÀN THÀNH:**
- [x] DB: `languages`, `seo_translations`, ~21 `<entity>_translations` đã tạo + backfill `vi`.
- [x] Model: trait `HasTranslations` áp xong cho 21 entity public.
- [x] Routing: middleware `DetectLocale` + 2 group (default no-prefix + `Route::prefix('{locale}')`).
- [x] Helper: `seo_url()`, `current_locale()`, `SeoAlternates::for($seo)` (xem `app/Helpers/global.php`, `app/Helpers/SeoAlternates.php`).
- [x] View: `head.blade.php` emit hreflang + canonical + x-default; `social.blade.php` `og:locale` (+ alternate).
- [x] Sitemap: index + per-locale + xhtml:link annotation.
- [x] Admin: tab locale (`formMultilingualTabs.blade.php`); transaction trong `EntityTranslationService::saveAll()`.
- [x] Cache: `HtmlCacheService::buildKey()` đã set `$namespace = $locale` trong `RoutingController` + `SitemapController`.

**Phase 2 (áp pattern cho các controller còn lại):**
- [ ] Áp pattern `EntityTranslationService::persistFromRequest()` + `formMultilingualTabs.blade.php` cho các controller admin còn lại (~20 controller chính + ~30 sub-controller). Xem hướng dẫn 4 bước ở §5.4.
- [ ] Localize 4 booking form còn lại (`shipBooking`, `serviceBooking`, `comboBooking`, `hotelBooking`) — pattern sẵn ở `tourBooking/form.blade.php`.
- [ ] Quyết định storage content blade: A (file theo `<seo_id>/<locale>.blade.php`) hay B (lưu DB) — xem §4.5.
- [ ] Validate "cha publish trước con" trong `SeoTranslation::upsertTranslation()` (hiện chưa enforce — tuỳ business rule).
- [ ] 404 page: hint sang locale khác có nội dung (qua `SeoAlternates`).
- [ ] Robots: cho phép crawl mọi locale; sitemap khai báo trong `robots.txt` (hiện đã có route `/sitemap.xml` + `/{locale}/sitemap.xml`).

### 7.2. Mẫu env mới

```env
APP_DEFAULT_LOCALE=vi
APP_FALLBACK_LOCALE=vi
APP_SUPPORTED_LOCALES=vi,en,zh,ko,ja
APP_LOCALE_STRATEGY=subfolder
APP_HREFLANG_X_DEFAULT=vi
```

### 7.3. Trích dẫn nguồn tham chiếu các file liên quan

**File mới (Phase 0 — hardening hệ thống đơn ngữ):**
- `app/Services/HtmlCacheService.php` — service cache HTML thống nhất (gzip, đa disk, key có namespace).
- `app/Http/Middleware/CheckRedirect.php` — middleware 301 redirect.
- `config/tablemysql.php` — mapping `seo.type → model/view/content_dir/with` + `translatable` (Phase 1 mở rộng).
- `database/migrations/2026_05_04_120000_add_indexes_to_redirect_info_table.php`
- `database/migrations/2026_05_04_120100_add_indexes_to_seo_table.php`

**File đã refactor (Phase 0):**
- `routes/web.php`, `app/Http/Controllers/RoutingController.php`, `app/Http/Controllers/SitemapController.php`, `app/Http/Controllers/AdminCacheController.php`, `app/Helpers/Url.php`, `app/Models/Seo.php`, `app/Http/Kernel.php`, `config/main.php`, `composer.json`.

**File mới (Phase 1 — multilingual core):**

DB / Migrations:
- `database/migrations/2026_05_04_130000_create_languages_table.php`
- `database/migrations/2026_05_04_130100_create_seo_translations_table.php`
- `database/migrations/2026_05_04_130200_create_entity_translations_tables.php` (động — sinh tự động ~21 bảng)

Models:
- `app/Models/Language.php`
- `app/Models/SeoTranslation.php`
- `app/Models/Concerns/HasTranslations.php`
- `app/Models/BaseTranslationModel.php`
- 21 model `*Translation` (TourTranslation, ShipTranslation, BlogTranslation, ...) — auto map qua trait

Routing / Middleware / Services:
- `app/Http/Middleware/DetectLocale.php`
- `app/Helpers/SeoAlternates.php`
- `app/Helpers/global.php` (current_locale, seo_url, seo_alternates, locale_url, ...)
- `app/Services/EntityTranslationService.php`

Views:
- `resources/views/admin/snippets/formMultilingualTabs.blade.php`
- `lang/vi/main.php`, `lang/en/main.php`, `lang/zh/main.php`, `lang/ja/main.php`, `lang/ko/main.php`

**File đã refactor (Phase 1):**
- `app/Models/Seo.php` — magic accessor đọc `seo_translations` theo locale; `insertItem`/`updateItem` đồng bộ default locale.
- 21 entity model (Tour, Ship, Service, Blog, Page, Hotel, Combo, Air, Carrental, Guide, Category, TourLocation, ShipLocation, ServiceLocation, AirLocation, ComboLocation, HotelLocation, CarrentalLocation, ShipPartner, TourContinent, TourCountry, TourInfoForeign) — áp `HasTranslations`.
- `app/Helpers/Url.php` — `checkUrlExists($url, $locale)` query `seo_translations`; thêm `cleanRequestPathWithLocale()`.
- `app/Http/Controllers/RoutingController.php` — locale-aware dispatch + `renderContentBlade($type, $slug, $locale)`.
- `app/Http/Controllers/SitemapController.php` — per-locale + xhtml:link annotation.
- `app/Http/Kernel.php` — đăng ký key `'detectLocale'`.
- `routes/web.php` — thêm `Route::prefix('{locale}')` + áp `detectLocale` middleware.
- `config/tablemysql.php` — thêm `translatable` cho mỗi type.
- `resources/views/main/snippets/head.blade.php` — hreflang + canonical + x-default.
- `resources/views/main/schema/social.blade.php` — og:locale + og:locale:alternate động.
- `resources/views/main/layouts/main.blade.php` + `layouts/booking.blade.php` — `<html lang>` + `dir` động.
- `resources/views/main/snippets/headerTop.blade.php` — language switcher tự động.
- `resources/views/main/tourBooking/form.blade.php` — localize labels.
- `app/Http/Controllers/AdminTourController.php`, `AdminBlogController.php`, `AdminPageController.php` — admin pattern mẫu.
- `resources/views/admin/tour/view.blade.php` — include `formMultilingualTabs`.
- `composer.json` — autoload `app/Helpers/global.php`.

**File cần cập nhật ở Phase 2 (theo pattern §5.4):**
- ~50 admin controller còn lại (xem danh sách ở Phase 2 timeline §5.2).
- `resources/views/main/{ship,service,combo,hotel}Booking/form.blade.php` — copy pattern từ `tourBooking/form.blade.php`.

### 7.4. Quy ước đặt tên

| Loại               | Pattern                                        |
| ------------------ | ---------------------------------------------- |
| Bảng translation   | `<entity>_translations`                        |
| FK                 | `<entity>_id`, `language_id`                   |
| Model translation  | `<Entity>Translation`                          |
| Migration          | `YYYY_MM_DD_HHMMSS_create_<entity>_translations_table` |
| Cache key          | `kebab:locale:slug_full` (vd `tour:en:phu-quoc-tours`) |
| Route name         | `routing.default`, `routing.localized`         |
| Helper             | `seo_url($entity, $locale = null)`             |

### 7.5. Câu hỏi mở (đề xuất review nội bộ)

1. **Có gộp `tour_info_foreign` vào `tour_info`** (1 entity tour duy nhất, phân biệt qua `is_foreign` flag) không?
   - Khuyến nghị: **chưa** trong Phase 1-5. Sau khi đa ngôn ngữ ổn định mới review.
2. **Subfolder vs Subdomain cho EN**?
   - Khuyến nghị: **subfolder**.
3. **Có cần lưu `keywords` riêng trong `seo_translations` không** (Google không dùng meta keywords)?
   - Lưu để dùng cho internal search/analytics, **không emit `<meta name="keywords">`**.
4. **Có dùng AI auto-draft dịch không**?
   - Có thể: lưu `translation_status='auto'` để sau Editor review thành `reviewed → published`. Không bao giờ publish thẳng `auto`.

---

> **Tài liệu sống** — cập nhật mỗi khi có quyết định kiến trúc lớn. Mọi thay đổi schema phải bổ sung mục mới ở chương 4 và 5.
