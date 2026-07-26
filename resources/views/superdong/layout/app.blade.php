<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="{{ optional(current_language())->dir ?? 'ltr' }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="@yield('document-description', 'Superdong — nền tảng du lịch trọn gói: vé tàu cao tốc, tour, khách sạn, combo và trải nghiệm trên đảo.')">
  <title>@yield('document-title', 'Superdong — Du lịch trọn vẹn')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@600;700;800&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  @stack('head-custom')
  @vite(['resources/sources/superdong.scss'])
</head>
<body class="sd-home-v2">

@include('superdong.assets.svg-sprite')

<a class="sd-skip" href="#main">Bỏ qua đến nội dung chính</a>

@yield('content')

@include('superdong.chrome.mobile-nav')

{{-- Hydrate cờ region switcher — phải có trước scripts-custom (không phụ thuộc Vite dev server) --}}
<script>
(function () {
  function hydrateLazySrcImages(root) {
    var scope = root || document;
    if (!scope || !scope.querySelectorAll) return;
    scope.querySelectorAll('img[data-lazy-src]').forEach(function (img) {
      if (img.getAttribute('data-lazy-src-done') === '1') return;
      var url = img.getAttribute('data-lazy-src');
      if (!url || !String(url).trim()) return;
      img.setAttribute('data-lazy-src-done', '1');
      img.src = url;
      img.removeAttribute('data-lazy-src');
    });
  }
  if (!window.hitourHydrateLazySrcImages) {
    window.hitourHydrateLazySrcImages = hydrateLazySrcImages;
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
