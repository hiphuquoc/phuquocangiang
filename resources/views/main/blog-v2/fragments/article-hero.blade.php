@php
  $cover = $cover ?? [];
  $src = $cover['src'] ?? null;
  $alt = $cover['alt'] ?? '';
@endphp

@if(!empty($src))
  <figure class="sd-blog-article-hero">
    <img src="{{ $src }}" alt="{{ $alt }}" loading="eager" fetchpriority="high" decoding="async" width="1200" height="675">
  </figure>
@endif
