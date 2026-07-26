# Super Dong — Home v2 (Prototype tĩnh)

Giao diện trang chủ cho **superdong.dev** — du lịch Côn Đảo trọn gói.

## Xem prototype

**Trang chủ `/` đã bật giao diện mới** khi `config/modules.php` → `'use_home_v2' => true`.

```
https://superdong.dev/
https://superdong.dev/prototype/home-v2/index.html
```

Sau khi đổi config: `php artisan config:clear`

## Cấu trúc (một bản duy nhất)

| File | Mô tả |
|------|--------|
| `index.html` | HTML tĩnh đầy đủ section |
| `home-v2.css` | CSS compiled (prototype standalone) |
| `home-v2.js` | Tab booking, menu mobile, FAQ, header scroll, reveal/parallax |

**Design system:** `docs/design/superdong-design-system.md`

## SCSS nguồn

```
resources/sources/main/home-v2.scss
resources/sources/main/home-v2/
```

Compile prototype CSS:

```bash
npx sass resources/sources/main/home-v2.scss public/prototype/home-v2/home-v2.css --no-source-map
```
