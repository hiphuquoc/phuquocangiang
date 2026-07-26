# Đa tiền tệ (Multi-currency)

Module cho phép website hiển thị giá theo nhiều đồng tiền (VND, USD, EUR, JPY,
KRW, CNY, THB...) trong khi DB vẫn lưu nguyên VND. Mỗi user có thể chọn
đồng tiền hiển thị tại currency picker ở `headerTop`; lựa chọn được lưu trong
cookie và áp dụng cho mọi trang.

## Sơ đồ tổng thể

```
Browser ─click─▶ currencySwitcher ─JS─▶ document.cookie = "app_currency=USD"
                                                            │
                                                       window.reload()
                                                            ▼
Request: GET / · Cookie: app_currency=USD
                                                            │
                                                            ▼
DetectLocale  (xác định vi/en/ko/…)
                                                            │
                                                            ▼
DetectCurrency  (đọc cookie → resolve → set CurrencyManager + share view)
                                                            │
                                                            ▼
Controller → cacheKey = "{locale}-{currency}/{slug}-…" → HtmlCacheService
                                                            │
                              ┌─────────────────────────────┴────────────────┐
                              ▼                                              ▼
                       Cache hit (file)                              Cache miss → render
                              │                                              │
                              ▼                                              ▼
                      Response cũ                                 Blade dùng format_price()
                                                                  → convert VND → USD
                                                                  → ghi cache
```

## Cấu hình

File: `config/currency.php`.

| Khóa | Mô tả |
| ---- | ----- |
| `default` | Currency fallback cuối cùng (mặc định `VND`). |
| `rate_base` | Currency dùng làm CHUẨN khi hiển thị tỷ giá so sánh trên picker UI (mặc định `USD`). Khách quốc tế quen với USD → dropdown sẽ hiển thị `1 USD ≈ 25,800 ₫`, `1 USD ≈ 0.92 €`, `1 USD ≈ 156 ¥`. |
| `defaults_by_locale` | Map `locale → currency` để chọn mặc định khi user chưa từng đổi (vd `en → USD`). |
| `currencies[CODE]` | Cấu hình chi tiết từng đồng tiền — xem dưới. |
| `cookie.name` | Tên cookie (mặc định `app_currency`). |
| `cookie.ttl_days` | TTL ngày của cookie. |
| `min_display` | Nếu giá quy đổi nhỏ hơn ngưỡng này → hiển thị `contact_label` thay vì số 0.xx. Mặc định 0 = luôn hiển thị. |

Mỗi entry trong `currencies` gồm:

- `vnd_per_unit`: 1 đơn vị currency này tương đương bao nhiêu VND. Quy đổi:
  `displayed_amount = vnd_amount / vnd_per_unit`. (VND có `vnd_per_unit = 1`).
- `symbol`, `symbol_html`, `symbol_position`: cách hiển thị ký tự ($ trước,
  đ sau dưới dạng `<sup>`...).
- `decimals`, `thousands_sep`, `decimal_sep`: định dạng số.
- `name`, `name_local`, `flag`, `note`: meta cho UI dropdown.
- `enabled`: bật/tắt currency trên picker.

### Cập nhật tỷ giá

Chỉ cần sửa `vnd_per_unit` ở `config/currency.php`. Sau đó:

```bash
php artisan config:clear
php artisan view:clear
# Xoá HTML cache cũ để render lại với tỷ giá mới:
php artisan tinker
> app(App\Services\HtmlCacheService::class)->clearAll();
```

Hoặc nếu đã có route admin xóa cache (như `AdminCacheController`) thì chạy nó.

## Backend

| File | Vai trò |
| ---- | ------- |
| `config/currency.php` | Khai báo danh sách + tỷ giá + cookie. |
| `app/Services/CurrencyManager.php` | Singleton: resolve / convert / format. |
| `app/Helpers/currency.php` | Helpers global cho Blade (`format_price()`...). |
| `app/Http/Middleware/DetectCurrency.php` | Resolve currency từ cookie/locale, share `$currentCurrency` cho view. |
| `app/Http/Middleware/EncryptCookies.php` | Loại `app_currency` khỏi danh sách mã hoá để JS đọc được. |
| `app/Http/Controllers/CurrencyController.php` | Endpoint server fallback `/currency/switch`. |
| `app/Providers/AppServiceProvider.php` | Bind singleton CurrencyManager. |
| `routes/web.php` | Đăng ký `detectCurrency` chain sau `detectLocale`. |
| `app/Services/HtmlCacheService.php` | Cache key có thể nhận namespace `{locale}-{currency}`. |
| `app/Http/Controllers/RoutingController.php` | Truyền namespace `{locale}-{currency}` vào cache key. |
| `app/Http/Controllers/HomeController.php` | Tách file cache home theo `home-{locale}-{currency}.html`. |

### Resolve order

`DetectCurrency` resolve theo thứ tự:

1. Query string `?currency=USD` (1 lần, set cookie).
2. Cookie `app_currency`.
3. `config('currency.defaults_by_locale')[<locale>]`.
4. `config('currency.default')`.

Nếu cookie chưa có, middleware sẽ tự ghi cookie với giá trị resolved — lần
request sau hit thẳng vào HTML cache đúng namespace, không cần re-render.

### Helpers chính (`app/Helpers/currency.php`)

```php
current_currency();                  // 'USD'
current_currency_meta();             // ['symbol'=>'$', 'decimals'=>2, …]
available_currencies();              // [ 'VND'=>[…], 'USD'=>[…], … ]
currency_symbol('EUR');              // '€'
convert_from_vnd(1_000_000);         // 38.76 (nếu USD, rate 25800)
format_price(1_000_000);             // '<sup>đ</sup>' hoặc '$38.76' (HTML)
format_price_plain(1_000_000);       // '1,000,000đ' hoặc '$38.76' (plain)
currency_default_for_locale('en');   // 'USD'

// Tỷ giá hiển thị (chuẩn = config('currency.rate_base'), mặc định 'USD'):
currency_rate_base();                // 'USD'
rate_from_base('VND');               // 25800.0 (1 USD = 25,800 VND)
rate_from_base('EUR');               // 0.9052... (1 USD ≈ 0.91 EUR)
format_rate_from_base('VND');        // '25,800 <sup>đ</sup>'
format_rate_from_base('EUR');        // '0.91 €'
```

`format_price($amountVnd, $opts)`:

- `$amountVnd`: số VND (int/float/string). Nếu rỗng/0/null → trả về
  `config('currency.contact_label')` (mặc định `'Liên hệ'`).
- `$opts['currency']`: override currency (mặc định = current).
- `$opts['html']`: `true` (default) → dùng `symbol_html` (vd `<sup>đ</sup>`).
- `$opts['fallback']`: chuỗi thay thế khi giá rỗng.

⚠️ Vì `format_price()` trả HTML (có `<sup>`), Blade phải dùng `{!! !!}`:

```blade
<div class="price">{!! format_price($tour->price_show) !!}</div>
```

Dùng `format_price_plain()` cho:
- Attribute (`title="…"`, `aria-label="…"`).
- JSON / data attributes.
- Email thuần text.

## Frontend

### UI — Region Switcher (hợp nhất Ngôn ngữ + Đồng tiền, deferred-apply)

Picker được trích thành partial `resources/views/main/snippets/regionSwitcher.blade.php`
và include 2 lần:

- **Desktop** (`headerTop.blade.php`, variant=`desktop`): trigger pill + dropdown thả xuống.
- **Mobile** (`headerMain.blade.php`, variant=`mobile`): icon button 30×30 đồng bộ với
  các action button khác trong header mobile (bars, login,…); click mở 1 dialog
  **fullscreen** (cùng nội dung — language + currency).

Thiết kế thân chung: **1 trigger + 1 panel 3 cột logic (1 lang | 2 currency dưới
chung 1 header) + footer Apply**.

- **Trigger** (`.regionSwitcher_trigger`): flag ngôn ngữ + code (vd `VI`) + dấu `·`
  + symbol + code currency (vd `đ VND`) + chevron. (Không còn icon globe.)
- **Dropdown** (`.regionSwitcher_menu`, 760px desktop / 94vw mobile):
  - Outer grid `.regionSwitcher_grid` — 2 cột `minmax(0,1fr) minmax(0,1.6fr)`:
    1. **Cột "Ngôn ngữ"** (`.regionSwitcher_col--lang`)
       - Header `fa-language` + tiêu đề + ghi chú phụ.
       - Danh sách: 1 cột (`.regionSwitcher_col_list--1col`).
       - Item là `<button data-lang-code data-lang-href>` → chỉ đánh dấu
         `.is-selected` (chưa navigate).
    2. **Cột "Đồng tiền"** (`.regionSwitcher_col--currency`)
       - **1 header chia sẻ** `fa-coins` + "Quy đổi từ USD".
       - Danh sách: 2 sub-cột (`.regionSwitcher_col_list--2col` =
         `grid-template-columns: 1fr 1fr`).
       - Item là `<button data-currency-code>`, compact (subname ẩn ở 2-col)
         + dòng tỷ giá `1 USD ≈ …`; item USD hiển thị `Tham chiếu quốc tế`.
  - **Footer** (`.regionSwitcher_footer`): `[ Hủy ]` + `[ Áp dụng ]`.
- **Apply LUÔN bật** (không cần user click cả 2 cột).
  Logic: trang đã map sẵn `currency` mặc định theo `locale` (`DetectCurrency`
  middleware đọc `config('currency.defaults_by_locale')` ngay request đầu).
  Nên có khi user chỉ cần đổi 1 trong 2 hoặc thậm chí xác nhận state hiện
  tại — Apply không cản. Click Apply mà không có thay đổi → đóng nhẹ
  (no-op, không reload phí tài nguyên).
- **State styling**:
  - `.is-current`: lựa chọn đang dùng (state khởi tạo mỗi lần mở dropdown).
  - `.is-selected`: lựa chọn user vừa pick = sẽ áp dụng khi bấm Apply.
  - `.is-selected:not(.is-current)`: **PENDING** — viền sáng `#0ea5e9`, ring
    dashed pulsing.
  - `.regionSwitcher_btn--primary.is-ready`: nút Apply nhận gradient mạnh +
    glow khi pending state khác current.
- **Loading**: bấm Apply → `.regionSwitcher.is-switching` → overlay
  `.regionSwitcher_loading` (blur + spinner 32px + "Đang áp dụng…") che
  dropdown; spinner nhỏ inline trong Apply (`.regionSwitcher_btn.is-loading`).
- `<noscript>` form POST `/currency/switch` ở cuối dropdown cho user tắt JS.
- **Responsive**:
  - `≤1199px`: dropdown 680px (desktop variant).
  - `≤1023px`: trigger thu nhỏ, dropdown 620px, outer grid 1fr / 1.45fr.
  - `≤767px`: desktop variant ẩn hoàn toàn (đã có `.headerTop { display: none }`).
    Mobile variant chiếm trọn viewport: `position: fixed; inset: 0; width: 100vw;
    height: 100vh; border-radius: 0`. Có sticky header (title + close X) ở top
    và sticky footer (Hủy + Áp dụng) ở bottom. Body bị khoá scroll qua class
    `.is-regionSwitcherFullscreen`.
  - `≤480px`: currency collapse về 1 cột, mở lại subname.

### JS Flow — Deferred Apply (Apply luôn bật)

`headerTop.blade.php` push một block `<script>` (qua `@once @push('scripts-custom')`).
Trạng thái UI là 1 state machine nhỏ:

1. Click trigger → toggle `.open` (đóng các region switcher khác đang mở).
2. Khi mở, gọi `resetSelection()` → đánh dấu lại `is-selected` về đúng
   `is-current` (clear mọi pending từ lần mở trước).
3. Click vào item trong cột Ngôn ngữ HOẶC Đồng tiền:
   - Toggle `.is-selected` trong cột tương ứng (chỉ duy nhất 1 item có).
   - Gọi `refreshApply()`: nếu pending khác current → toggle `.is-ready` cho
     Apply (gradient mạnh hơn). Nút **không bao giờ disabled** khi dropdown
     mở — user có thể nhấn Apply bất cứ lúc nào.
4. Click **Hủy** / click ngoài / ESC → đóng + reset selection (KHÔNG navigate).
5. Click **Áp dụng**:
   - Nếu pending = current ở cả 2 cột → `closeMenu()` (no-op).
   - `root.classList.add('is-switching')` → overlay loading hiện.
   - Button Apply nhận `.is-loading` (spinner) và `disabled` (chỉ trong lúc
     đang switch để khoá double-click).
   - Nếu currency đổi → set cookie `app_currency=<CODE>; Max-Age=…; Path=/; SameSite=Lax`.
   - Nếu language đổi → `window.location.assign(item.dataset.langHref)`.
   - Ngược lại chỉ `window.location.reload()` để middleware đọc cookie mới.

Cookie KHÔNG bị `EncryptCookies` đụng vào (đã `except` trong middleware) → JS
đọc/ghi raw string, server middleware cũng đọc plain `'USD'`.

### CSS

File: `resources/sources/main/style.scss`, class block `.regionSwitcher`.

- Pill trigger màu trắng trong/border mờ (trên nền xanh `#3395ff`),
  focus-visible có outline ring.
- Dropdown:
  - Stack dọc `display: flex; flex-direction: column; gap: 0.7rem`.
  - Mỗi section là 1 card mềm trên gradient `#f8fafc → #f1f5f9`.
  - List dùng `display: grid; grid-template-columns: 1fr 1fr` (≥768px), 1 cột
    (≤767px). Không scroll trên desktop.
  - Item: hover trắng + shadow nhẹ; `.is-selected` gradient xanh + tick góc
    phải; PENDING (`.is-selected:not(.is-current)`) thêm ring dashed pulse.
- Footer: border-top, 2 nút radius pill — Apply gradient `#0ea5e9 → #0284c7`
  có hover lift + spinner inline.
- Overlay loading: position absolute phủ toàn dropdown, blur background +
  spinner 32px + text "Đang áp dụng…".
- Mobile: footer sticky đáy + overflow-y auto cho nội dung dài.

## Cache HTML

### Quy ước đặt tên cache (đã chuẩn hoá)

Cache key được build từ **full URL request path**, locale prefix giữ nguyên,
mọi `/` thay bằng `-`. Default locale (vi) không có prefix trong URL nên
key cũng không có prefix. Trang chủ được gán slug ảo `home` để mọi URL —
trang chủ lẫn trang chi tiết — đều build qua cùng 1 luật. Currency được
ghép suffix riêng để tránh "đầu độc" giá listing.

| URL                             | Cache key                  | File trên đĩa                              |
|---                              |---                         |---                                         |
| `/` (vi default)                | `home-vnd`                 | `caches/home-vnd.html`                     |
| `/` + USD cookie                | `home-usd`                 | `caches/home-usd.html`                     |
| `/en`                           | `en-home-usd`              | `caches/en-home-usd.html`                  |
| `/cn`                           | `cn-home-cny`              | `caches/cn-home-cny.html`                  |
| `/tour-phu-quoc` (vi)           | `tour-phu-quoc-vnd`        | `caches/tour-phu-quoc-vnd.html`            |
| `/en/phu-quoc-tours`            | `en-phu-quoc-tours-usd`    | `caches/en-phu-quoc-tours-usd.html`        |
| `/cn/tour-phu-quoc` (chưa dịch) | `cn-tour-phu-quoc-cny`     | `caches/cn-tour-phu-quoc-cny.html`         |
| `/tour-phu-quoc?page=2`         | `tour-phu-quoc-page-2-vnd` | `caches/tour-phu-quoc-page-2-vnd.html`     |

### Trang chủ vs trang con (quan trọng)

- File `home.html.gz` / `en-home.html.gz` **chỉ** được ghi từ `HomeController::home()` với cờ `getOrRender(..., allowHomepagePersist: true)` và path `/` hoặc `/en`.
- `RoutingController` gọi `getOrRender(..., false)` — **không thể** ghi `home.html.gz` dù cache key trùng tên.
- Cache key trang con build từ `slug_full` SEO (`buildKeyFromSlugFull`), không parse lại `request->path()`.
- Nếu vẫn thấy `home.html.gz` đổi mtime khi mở trang con: mở DevTools → Network, kiểm tra có request `GET /` song song (bot, prefetch, tab khác).
- Trang có slug URL là `home` (nếu có) vẫn dùng key `home` — tránh trùng tên file với trang chủ; nên đổi slug SEO nếu gặp case này.

### Cache menu (desktop + mobile) — theo ngôn ngữ, không theo currency

Menu (`headerMain`: mega menu desktop + header mobile + `#nav-mobile`) được cache
**một file cho mỗi locale**, tái sử dụng khi render trang (không query lại DB menu).

| Locale | Cache key      | File trên đĩa              |
|--------|----------------|----------------------------|
| `vi`   | `menuMain_vi`  | `caches/menuMain_vi.html.gz` |
| `en`   | `menuMain_en`  | `caches/menuMain_en.html.gz` |
| `zh`   | `menuMain_zh`  | `caches/menuMain_zh.html.gz` |

- Layout: `@include('main.snippets.headerMainCached')` → `HtmlCacheService::getOrRenderMenu()`.
- `headerTop` (hotline, region switcher desktop) **không** nằm trong file menu — render mỗi request.
- Sau khi sửa cấu trúc menu trong admin: `HtmlCacheService::clearMenu()` hoặc `clearAll()`.
- Bật/tắt cùng `APP_CACHE_HTML=true` trong `.env`.

Luật build (xem `HtmlCacheService::buildKeyFromSegments`):

```
key = [locale-prefix-]{slug-with-dashes}[-{params...}]-{currency}
   locale-prefix:    none nếu là default locale, ngược lại '{locale}-'.
   slug-with-dashes: implode('-', segments). Segments rỗng -> ['home'].
   params:           ksort($params), append `-{k}-{v}`.
   currency:         hậu tố cuối, lowercase. Mọi controller TỰ ghép sau buildKeyFromRequest().
```

### API chung — KHÔNG tự build cache key trong controller

Mọi controller cần cache HTML đều gọi `HtmlCacheService::buildKeyFromRequest`,
KHÔNG tự ghép locale/slug bằng tay. Nhờ đó nếu sau này muốn đổi convention
chỉ cần sửa 1 chỗ.

```php
use App\Services\HtmlCacheService;

public function someAction(Request $request, HtmlCacheService $cache)
{
    $cacheParams = [];
    if ($p = $request->query('page'))   $cacheParams['page']   = $p;
    if ($s = $request->query('search')) $cacheParams['search'] = $s;
    $currency = strtolower((string) ($request->attributes->get('currency') ?: current_currency()));
    $cacheKey = HtmlCacheService::buildKeyFromRequest($request, $cacheParams) . '-' . $currency;

    $html = $cache->getOrRender($cacheKey, fn() => view(...)->render());
    return response($html);
}
```

Hai API cần biết:

- `HtmlCacheService::buildKeyFromRequest(Request $request, array $params = []): string`
  Đọc `$request->path()`, dùng `Url::cleanRequestPathWithLocale()` để tách
  locale + segments, rồi gọi `buildKeyFromSegments()`. Đây là helper "đầu vào".
- `HtmlCacheService::buildKeyFromSegments(?string $locale, array $segments, array $params = []): string`
  Pure function — không phụ thuộc Request. Có thể dùng cho job/CLI/sitemap.

`HtmlCacheService::buildKey($slugFull, $params, $namespace)` cũ vẫn còn nhưng
được đánh dấu `@deprecated`; chỉ giữ để tương thích code cũ chưa migrate.

Khi user đổi currency:
1. JS set cookie + reload.
2. Server đọc cookie, currency suffix khác → file cache khác → hit/miss
   riêng → render lại với currency mới và ghi file riêng.

⇒ Cache cũ không bị "đầu độc" bởi currency mới, không cần xóa cache.

⚠️ Khi update tỷ giá (`vnd_per_unit`), nên `clearAll()` toàn bộ HTML cache để
các file cũ render lại theo tỷ giá mới.

### Quy tắc đặt path file cache (đã fix)

- **Đường dẫn folder cache phải có dấu `/` phân cách với filename**. Trước
  đây HomeController dùng `Storage::path('public/caches').$name` (thiếu
  trailing slash) khiến cache rơi vào `storage/app/public/cacheshome-vi-cny.html`
  thay vì `storage/app/public/caches/home-vi-cny.html`. Hiện đã chuẩn hoá:
  ```php
  $folderSave = rtrim((string) config('main.cache.folderSave'), '/').'/';
  $pathCache  = Storage::path($folderSave.$nameCache);
  Storage::put($folderSave.$nameCache, $xhtml);
  ```
- **Locale phải đọc từ request attributes / route param, không phải mỗi
  `current_locale()`**. Trong vài context (route caching, controller được
  resolve trước khi `app()->setLocale()` đồng bộ qua helper), gọi thẳng
  `current_locale()` có thể vẫn trả về default `vi` và file cache `/en` bị
  ghi nhầm với tiền tố `-vi-`. HomeController giờ ưu tiên:
  ```php
  $localeAttr = $request->attributes->get('locale') ?: $request->route('locale');
  $localeNs   = strtolower($localeAttr ?: current_locale());
  ```
- **Asset cache busting**: `HtmlCacheService::isFresh()` và HomeController
  đều so sánh mtime của `public/build/manifest.json` với mtime file cache.
  Khi `npm run build` chạy, manifest mới hơn → tất cả file cache HTML
  sinh trước đó bị coi là stale và render lại. Giúp tránh lỗi console
  `Failed to load module script: ... MIME type of "text/html"` do trình
  duyệt request `/build/assets/app.OLDHASH.js` không còn tồn tại sau rebuild.

### Migration: xoá cache cũ

Sau khi pull thay đổi, các file cache cũ theo namespace `{locale}-{currency}/`
sẽ KHÔNG bao giờ được đọc lại (cache key đổi). Để dọn dẹp:

```bash
# Xoá toàn bộ cache HTML cũ (mọi locale, mọi currency)
php artisan tinker --execute="app(App\Services\HtmlCacheService::class)->clearAll();"

# Hoặc xoá tay
rm -rf storage/app/public/caches/*
rm -f  storage/app/public/cacheshome-*    # file rò rỉ do bug thiếu '/'
```

## Endpoint server fallback

`GET|POST /currency/switch?to=USD&redirect=/path`

- Validate `to` qua `CurrencyManager::isSupported()`.
- Set cookie + redirect về `redirect` (sanitize: chỉ cho phép path nội bộ).
- CSRF được skip cho `currency/switch` (an toàn vì chỉ set cookie hiển thị).

JS UI ưu tiên client-side cookie + reload (nhanh hơn); endpoint này chỉ là
backup cho user/bot tắt JS.

## Mở rộng

- **Thêm currency mới**: thêm 1 entry vào `currencies` trong config.
- **Đổi tỷ giá theo API**: override `config/currency.php` tại runtime trong
  `AppServiceProvider::boot()`:
  ```php
  $rates = Cache::remember('currency_rates', 3600, fn() => fetchRatesFromApi());
  config()->set('currency.currencies.USD.vnd_per_unit', $rates['USD']);
  // …
  ```
- **Format theo locale (Intl)**: nâng `CurrencyManager::format()` dùng
  `\NumberFormatter` thay cho `number_format()` thủ công.
- **Áp dụng cho admin**: hiện admin vẫn dùng `number_format() + config('main.unit_currency')` để giữ VND làm canonical cho nghiệp vụ kế toán. Nếu muốn admin cũng đa tiền tệ, chỉ cần đổi sang `format_price()`.

## Test nhanh

1. Load `/` → thấy currency pill "🇻🇳 đ VND" cạnh language pill.
2. Click pill → dropdown 7 currencies.
3. Chọn USD → page reload, mọi giá hiển thị `$xx.yy`.
4. F12 → Application → Cookies → `app_currency = USD`.
5. Đổi sang `/en/…` → vẫn USD (cookie ưu tiên hơn locale default).
6. Xóa cookie → reload `/en/…` → tự resolve về USD theo `defaults_by_locale.en`.
