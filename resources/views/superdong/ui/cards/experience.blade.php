{{--
  Experience card — cùng khung floating panel với deal card, accent sky cho vé giải trí.

  Props: image, alt, category, duration, title, rating, facts[], chips[], price, ctaLabel, ctaHref, reveal
--}}
@php
  $rating = $rating ?? null;
  $facts = $facts ?? [];
  $chips = $chips ?? [];
  $ctaLabel = $ctaLabel ?? 'Đặt vé';
  $ctaHref = $ctaHref ?? '#';
  $reveal = $reveal ?? true;
  $hasLink = $ctaHref !== '' && $ctaHref !== '#';
@endphp

<article class="sd-card sd-card--xp" @if($reveal) data-reveal @endif>
  <div class="sd-card__shell">
    <div class="sd-card__hero">
      <div class="sd-card__media">
        @if($hasLink)
          <a href="{{ $ctaHref }}" class="sd-card__media-link" tabindex="-1" aria-hidden="true">
        @endif
        <img src="{{ $image }}" alt="{{ $alt }}" loading="lazy" decoding="async" width="640" height="420">
        @if($hasLink)
          </a>
        @endif
        <span class="sd-card__xp-category">{{ $category }}</span>
        @if(!empty($duration))
          <span class="sd-card__xp-duration">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            {{ $duration }}
          </span>
        @endif
      </div>
      <div class="sd-card__hero-tail">
        <div class="sd-card__panel">
          <div class="sd-card__panel-main">
            <div class="sd-card__xp-head">
              <h3 class="sd-card__title maxLine_2">
                @if($hasLink)
                  <a href="{{ $ctaHref }}">{{ $title }}</a>
                @else
                  {{ $title }}
                @endif
              </h3>

              @if($rating)
                <div class="sd-card__xp-rating" aria-label="Đánh giá {{ $rating }} sao">
                  <svg aria-hidden="true"><use href="#icon_star"></use></svg>
                  <strong>{{ number_format($rating, 1) }}</strong>
                </div>
              @endif
            </div>

            @include('superdong.ui.cards._facts', ['facts' => $facts])

            @if(!empty($chips))
              <div class="sd-card__xp-chips">
                @foreach($chips as $chip)
                  <span class="maxLine_1">{{ $chip }}</span>
                @endforeach
              </div>
            @endif

            <div class="sd-card__footer">
              <div class="sd-card__price">
                <span class="sd-card__price-label">Giá vé:</span>
                <strong>{{ $price }}<span class="sd-card__price-cur">₫</span></strong>
              </div>
            </div>
          </div>

          <a href="{{ $ctaHref }}" class="sd-card__cta" @if(!$hasLink) aria-disabled="true" @endif>{{ $ctaLabel }}</a>
        </div>
      </div>
    </div>
  </div>
</article>
