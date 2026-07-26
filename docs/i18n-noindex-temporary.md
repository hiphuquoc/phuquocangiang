# Tạm thời chặn index cho các locale chưa dịch xong (i18n)

> Trạng thái: **ĐANG BẬT** (block index cho mọi locale ≠ `vi`).
> Khi đã dịch xong, làm theo phần [Cách mở lại](#cách-mở-lại) ở cuối tài liệu này.

## Mục tiêu

Dự án đã có hạ tầng đa ngôn ngữ (vi / en / zh-cn / zh-tw / ja / ko / es / fr / de / ru), nhưng phần dịch nội dung mới chỉ xong ở **`vi` (locale mặc định)**. Trong giai đoạn này, ta cần:

1. **Không submit sitemap** cho các locale ≠ `vi` (để Google không khám phá URL chưa dịch).
2. **Đặt `noindex,nofollow`** trên `<head>` của mọi trang công khai khi locale hiện tại ≠ `vi` (chống index trực tiếp).
3. **Không động** vào hành vi của site `vi` (trang gốc): vẫn `index,follow`, sitemap vẫn chạy đầy đủ như cũ.

> Tham chiếu Google: trang có `noindex` không nên xuất hiện trong sitemap, và hreflang trỏ tới trang `noindex` sẽ bị Google bỏ qua. Do đó cách làm này an toàn về mặt SEO.

---

## Những file đã sửa

Tổng cộng **2 file mã** được sửa (đều có khối comment `TEMP (i18n)` để dễ tìm lại) và **1 file tài liệu** này được tạo mới.

### 1. `resources/views/main/snippets/head.blade.php`

Mục tiêu: đổi thẻ `<meta name="robots">` từ "luôn `index,follow`" sang **chỉ `index,follow` khi locale mặc định, ngược lại `noindex,nofollow`**.

Trạng thái cũ (1 dòng):

```php
<meta name="robots" content="index,follow">
```

Trạng thái hiện tại:

```php
{{-- ============================================================
     TEMP (i18n) — chặn index cho các locale chưa dịch xong.
     Locale mặc định (vi): index,follow như cũ.
     Locale khác:          noindex,nofollow để Google không index.
     Khi dịch xong, mở lại: xem docs/i18n-noindex-temporary.md
============================================================ --}}
@if(is_default_locale())
<meta name="robots" content="index,follow">
@else
<meta name="robots" content="noindex,nofollow">
@endif
```

Helper `is_default_locale()` đã có sẵn ở `app/Helpers/global.php` — so sánh `current_locale()` với `config('language.default_code')` (mặc định `vi`). Khi `APP_DEFAULT_LOCALE` trong `.env` đổi, hành vi tự động theo.

### 2. `app/Http/Controllers/SitemapController.php`

Mục tiêu: hai route sitemap cho locale ≠ default sẽ trả **HTTP 404** (rỗng), không generate XML.

- Route public ảnh hưởng:
  - `GET /{locale}/sitemap.xml`        (named: `sitemap.main.locale`)
  - `GET /{locale}/sitemap/{type}.xml` (named: `sitemap.child.locale`)
- Route default **không bị ảnh hưởng**:
  - `GET /sitemap.xml`        (`sitemap.main`)
  - `GET /sitemap/{type}.xml` (`sitemap.child`)

Đoạn được thêm vào **đầu** mỗi method (`main()` và `child()`) ngay sau khi resolve `$lang`:

```php
// ============================================================
// TEMP (i18n) — Tắt sitemap cho locale chưa dịch xong.
// Chỉ phục vụ sitemap cho default locale (vi). Các locale khác
// trả về 404 để không bị index bởi Google.
// Khi dịch xong, xoá block này — xem docs/i18n-noindex-temporary.md
// ============================================================
if (!$lang->is_default) {
    return response('', 404);
}
```

Ưu điểm cách này:
- Không đụng route definitions (giữ nguyên `routes/web.php`).
- Khi mở lại chỉ cần xoá 2 block trên là chạy lại bình thường.
- Cache cũ (nếu có) sẽ không được serve do early-return chạy **trước** `HtmlCacheService::getOrRender(...)`.

---

## Những file/khu vực **CHƯA** đụng (cố ý)

Để giữ nguyên hành vi trang `vi` (theo yêu cầu *"đừng làm ảnh hưởng trang gốc"*):

| Hạng mục | Vị trí | Ghi chú |
|---|---|---|
| `<link rel="alternate" hreflang="...">` trong `<head>` | `resources/views/main/snippets/head.blade.php` dòng ~30–51 | Vẫn xuất hreflang cho mọi locale active. Vì các URL non-vi đã có `noindex`, Google sẽ bỏ qua hreflang trỏ tới chúng (theo guideline của Google) — không gây sự cố SEO cho `vi`. |
| `<xhtml:link rel="alternate">` trong sitemap `vi` | `SitemapController::child()` dòng ~122–130 | Tương tự — vẫn liệt kê alternates, Google bỏ qua các URL noindex. Nếu muốn sạch tuyệt đối, xem [Tuỳ chọn nâng cao](#tuỳ-chọn-nâng-cao). |
| `public/robots.txt` | `User-agent: * / Disallow:` | Không thêm `Disallow: /en/`, `/ja/`, … vì sẽ chặn cả `noindex` (Google không vào được trang thì không đọc được `noindex`). Để Google **xem** được `noindex` thì phải cho phép crawl. |
| `config/language.php` | — | Giữ nguyên: tất cả locale vẫn `is_active=true` để menu/language-switcher tiếp tục hoạt động. |
| `routes/web.php` | dòng 588–681 | Giữ nguyên: route locale-prefix vẫn tồn tại, người dùng vẫn xem được trang (chỉ là Google không index). |
| Cache key của HtmlCacheService | `app/Services/HtmlCacheService.php` | Cache đang tách theo namespace = locale, nên cache `vi` riêng — không bị ảnh hưởng. |

---

## Sau khi deploy lần đầu — clear cache

Cần clear 2 lớp cache:

```bash
# 1) Compiled blade views (vì head.blade.php đã đổi)
php artisan view:clear

# 2) Optional: framework caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 3) HTML cache của các locale ≠ vi (nếu trước đó site đã serve và cache với meta robots cũ)
#    HtmlCacheService lưu theo namespace locale; xoá namespace của từng locale non-default:
#    (chạy trong tinker hoặc viết artisan command tuỳ team)
php artisan tinker
>>> app(\App\Services\HtmlCacheService::class)->clearAll();   // an toàn nhất: clear toàn bộ
```

> Lưu ý: cache `vi` cũng được clear bằng `clearAll()` nhưng sẽ được tự rebuild ở request kế. Nếu muốn chỉ clear non-default, cần phương thức `clearByNamespace($locale)` (hiện chưa có) — `clearAll()` là phương án đơn giản và an toàn.

---

## Kiểm thử nhanh (sanity check)

Sau khi deploy + clear cache, kiểm tra bằng `curl` hoặc DevTools:

```bash
# 1) Trang chủ vi  → vẫn index,follow
curl -s https://hitour.dev/ | grep 'name="robots"'
#  ⇒ <meta name="robots" content="index,follow">

# 2) Trang chủ en  → noindex,nofollow
curl -s https://hitour.dev/en | grep 'name="robots"'
#  ⇒ <meta name="robots" content="noindex,nofollow">

# 3) Sitemap vi  → XML 200 OK
curl -sI https://hitour.dev/sitemap.xml | head -n 1
#  ⇒ HTTP/2 200

# 4) Sitemap en  → 404
curl -sI https://hitour.dev/en/sitemap.xml | head -n 1
#  ⇒ HTTP/2 404
```

Có thể test thêm với bất kỳ locale nào khác (`zh-cn`, `ja`, `ko`, `es`, `fr`, `de`, `ru`, `zh-tw`).

---

## Cách mở lại (sau khi dịch xong)

Khi đã dịch xong **một** locale (vd. `en`) và muốn cho Google index lại, có hai chiến lược — chọn 1:

### A. Mở **tất cả** non-default locales một lượt (đơn giản)

Khôi phục 2 thay đổi này:

1. **`resources/views/main/snippets/head.blade.php`** — xoá block `@if(is_default_locale()) … @endif` và để lại đúng 1 dòng cũ:

   ```php
   <meta name="robots" content="index,follow">
   ```

2. **`app/Http/Controllers/SitemapController.php`** — xoá 2 block `TEMP (i18n)` trong `main()` và `child()`.

3. Clear cache:

   ```bash
   php artisan view:clear
   php artisan cache:clear
   php artisan tinker
   >>> app(\App\Services\HtmlCacheService::class)->clearAll();
   ```

4. Cập nhật Google Search Console: submit lại các sitemap mới:
   - `https://hitour.dev/sitemap.xml` (đã có sẵn)
   - `https://hitour.dev/en/sitemap.xml`, `/zh-cn/sitemap.xml`, … (mới được mở)

### B. Mở **từng locale** (an toàn hơn, khuyên dùng)

Khi vẫn còn locale dịch dở dang, mở dần theo locale đã hoàn thành. Sửa lại 2 chỗ để dùng danh sách "locale đã sẵn sàng":

1. **`config/language.php`** — thêm cờ tuỳ chỉnh (vd. `seo_ready`) vào từng entry:

   ```php
   'en' => [ /* ... */ 'seo_ready' => true,  /* đã dịch xong */ ],
   'ja' => [ /* ... */ 'seo_ready' => false, /* còn dở */ ],
   ```

2. **`resources/views/main/snippets/head.blade.php`** — thay điều kiện:

   ```php
   @php
       $__seoReady = is_default_locale()
           || (bool) data_get(config('language.list.' . current_locale()), 'seo_ready', false);
   @endphp
   @if($__seoReady)
   <meta name="robots" content="index,follow">
   @else
   <meta name="robots" content="noindex,nofollow">
   @endif
   ```

3. **`app/Http/Controllers/SitemapController.php`** — thay `if (!$lang->is_default)` bằng:

   ```php
   $seoReady = $lang->is_default
       || (bool) data_get(config('language.list.' . $lang->code), 'seo_ready', false);
   if (!$seoReady) {
       return response('', 404);
   }
   ```

4. Clear cache như mục A bước 3.

5. Submit từng sitemap locale vào Google Search Console khi `seo_ready=true`.

---

## Tuỳ chọn nâng cao (không bắt buộc)

Sau khi đã quen với hành vi cơ bản, có thể dọn dẹp tín hiệu SEO sạch hơn:

1. **Bỏ hreflang trỏ tới locale chưa sẵn sàng** trong `head.blade.php` (đoạn `@foreach($__alternates as $__alt)`) — hiện tại vẫn output đầy đủ, Google sẽ ignore nhưng tốn crawl budget.
2. **Bỏ `<xhtml:link rel="alternate">` trong sitemap `vi`** (`SitemapController::child()`) cho các locale chưa sẵn sàng — tương tự lý do trên.
3. **Soft 404 → 410 Gone** cho URL `/{locale}/sitemap.xml` nếu không có dự định bật lại sớm: thay `response('', 404)` bằng `response('', 410)`.

Cả 3 mục trên đều **không cần** cho mục tiêu hiện tại, chỉ là tuning thêm.

---

## Tóm tắt vùng diff (để grep nhanh khi revert)

Tìm chuỗi `TEMP (i18n)` trong codebase sẽ ra đúng các block tạm:

```bash
grep -rn "TEMP (i18n)" app/ resources/
```

Kết quả mong đợi:

- `app/Http/Controllers/SitemapController.php` (2 chỗ — trong `main()` và `child()`)
- `resources/views/main/snippets/head.blade.php` (1 chỗ — quanh thẻ `<meta name="robots">`)
