{{--
  Blog card — grid listing.

  Props: image, alt, title, desc, date, author, ctaHref, highlight
--}}
@php
  $ctaHref = $ctaHref ?? '#';
  $highlight = $highlight ?? null;
@endphp

<article class="sd-blog-card">
  <a href="{{ $ctaHref }}" class="sd-blog-card__media" tabindex="-1" aria-hidden="true">
    <img src="{{ $image }}" alt="{{ $alt ?? $title ?? '' }}" loading="lazy" decoding="async">
    @if(!empty($highlight))
      <span class="sd-blog-card__badge">{{ $highlight }}</span>
    @endif
  </a>
  <div class="sd-blog-card__body">
    @if(!empty($date) || !empty($author))
      <div class="sd-blog-card__meta">
        @if(!empty($date))
          <time datetime="{{ $date }}">{{ $date }}</time>
        @endif
        @if(!empty($author))
          <span>{{ $author }}</span>
        @endif
      </div>
    @endif
    <h3 class="sd-blog-card__title">
      <a href="{{ $ctaHref }}">{{ $title ?? '' }}</a>
    </h3>
    @if(!empty($desc))
      <p class="sd-blog-card__desc">{{ $desc }}</p>
    @endif
    <a href="{{ $ctaHref }}" class="sd-blog-card__cta">Đọc tiếp →</a>
  </div>
</article>
