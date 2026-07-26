<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ optional(current_language())->dir ?? 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="@yield('document-description', 'Superdong — nền tảng du lịch trọn gói: vé tàu cao tốc, tour, khách sạn, combo và trải nghiệm trên đảo.')">
  <title>@yield('document-title', 'Superdong — Du lịch trọn vẹn')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://storage.googleapis.com" crossorigin>
  <link rel="dns-prefetch" href="https://storage.googleapis.com">
  {{-- Fonts + FA không chặn first paint --}}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@600;700;800&family=Roboto:wght@400;500;700&display=swap" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@600;700;800&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  </noscript>
  @stack('head-custom')
  @vite(['resources/sources/superdong.scss'])
</head>
<body class="sd-home-v2">

@include('superdong.assets.svg-sprite')

<a class="sd-skip" href="#main">Bỏ qua đến nội dung chính</a>

@yield('content')

@include('superdong.chrome.mobile-nav')

{{-- Lazy hydrate: chỉ gán src khi vào viewport (không chặn trang) --}}
<script>
(function () {
  function hydrateLazySrcImages(root) {
    var scope = root || document;
    if (!scope || !scope.querySelectorAll) return;
    var nodes = scope.querySelectorAll('img[data-lazy-src], [data-lazy-bg]');
    if (!nodes.length) return;

    function apply(el) {
      if (el.getAttribute('data-lazy-src-done') === '1') return;
      var url = el.getAttribute('data-lazy-src') || el.getAttribute('data-lazy-bg');
      if (!url || !String(url).trim()) return;
      el.setAttribute('data-lazy-src-done', '1');
      if (el.tagName === 'IMG') {
        el.src = url;
        el.removeAttribute('data-lazy-src');
      } else {
        el.style.setProperty('--sd-img', "url('" + url.replace(/'/g, "\\'") + "')");
        el.removeAttribute('data-lazy-bg');
      }
    }

    if (!('IntersectionObserver' in window)) {
      nodes.forEach(apply);
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        apply(entry.target);
        io.unobserve(entry.target);
      });
    }, { rootMargin: '200px 0px', threshold: 0.01 });

    nodes.forEach(function (el) { io.observe(el); });
  }

  if (!window.hitourHydrateLazySrcImages) {
    window.hitourHydrateLazySrcImages = hydrateLazySrcImages;
  } else {
    // Giữ bản IO nếu layout đã gắn; tránh ghi đè bằng bản eager nếu script khác chạy trước.
  }

  function hydrateAll() {
    window.hitourHydrateLazySrcImages(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hydrateAll);
  } else {
    hydrateAll();
  }
})();
</script>

@vite(['resources/js/superdong.js'])
@stack('scripts-custom')

</body>
</html>
