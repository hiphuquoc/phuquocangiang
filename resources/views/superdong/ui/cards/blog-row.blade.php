{{--
  Blog row — danh sách dọc, ảnh trái / nội dung phải.

  Props: image, alt, title, desc, date, author, ctaHref, highlight, featured
--}}
@php
  $ctaHref = $ctaHref ?? '#';
  $highlight = $highlight ?? null;
  $featured = !empty($featured);
@endphp

<article @class(['sd-blog-row', 'sd-blog-row--featured' => $featured])>
  <a href="{{ $ctaHref }}" class="sd-blog-row__media" tabindex="-1" aria-hidden="true">
    <img src="{{ $image }}" alt="{{ $alt ?? $title ?? '' }}" loading="lazy" decoding="async">
    @if(!empty($highlight))
      <span class="sd-blog-row__badge">{{ $highlight }}</span>
    @endif
  </a>

  <div class="sd-blog-row__body">
    @if(!empty($date) || !empty($author))
      <div class="sd-blog-row__meta">
        @if(!empty($date))
          <time datetime="{{ $date }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>
            {{ $date }}
          </time>
        @endif
        @if(!empty($author))
          <span class="sd-blog-row__author">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            {{ $author }}
          </span>
        @endif
      </div>
    @endif

    <h2 class="sd-blog-row__title">
      <a href="{{ $ctaHref }}">{{ $title ?? '' }}</a>
    </h2>

    @if(!empty($desc))
      <p class="sd-blog-row__desc">{{ $desc }}</p>
    @endif

    <a href="{{ $ctaHref }}" class="sd-blog-row__cta">
      <span>Đọc bài viết</span>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8-8-8z"/></svg>
    </a>
  </div>
</article>
