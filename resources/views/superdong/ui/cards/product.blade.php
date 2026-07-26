{{--
  Product card — ảnh ~2/3 khung + gradient đen, panel inset chèn lên, CTA góc dưới phải.

  Props: image, alt, dealBadge, title, tagline, rating, facts[], price, priceUnit, ctaLabel, ctaHref, reveal
--}}
@php
  $dealBadge = $dealBadge ?? null;
  $tagline = $tagline ?? null;
  $rating = $rating ?? null;
  $facts = $facts ?? [];
  $priceUnit = $priceUnit ?? null;
  $ctaLabel = $ctaLabel ?? 'Xem chi tiết';
  $ctaHref = $ctaHref ?? '#';
  $reveal = $reveal ?? true;
  $hasLink = $ctaHref !== '' && $ctaHref !== '#';
@endphp

<article class="sd-card sd-card--deal" @if($reveal) data-reveal @endif>
  <div class="sd-card__shell">
    <div class="sd-card__hero">
      <div class="sd-card__media">
        @if($hasLink)
          <a href="{{ $ctaHref }}" class="sd-card__media-link" tabindex="-1" aria-hidden="true">
        @endif
        <img src="{{ $image }}" alt="{{ $alt }}" loading="lazy">
        @if($hasLink)
          </a>
        @endif
        @if(!empty($dealBadge))
          <span class="sd-card__deal-badge">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7L12 16.8 5.7 21l2.3-7-6-4.6h7.6L12 2z"/></svg>
            {{ $dealBadge }}
          </span>
        @endif
      </div>
      <div class="sd-card__hero-tail">
        <div class="sd-card__panel">
          <div class="sd-card__panel-main">
            <h3 class="sd-card__title maxLine_2">
              @if($hasLink)
                <a href="{{ $ctaHref }}">{{ $title }}</a>
              @else
                {{ $title }}
              @endif
            </h3>

            @if(!empty($tagline))
              <p class="sd-card__tagline maxLine_4">{{ $tagline }}</p>
            @endif

            @if($rating)
              <div class="sd-card__stars" aria-label="Đánh giá {{ $rating }} sao">
                @for($i = 0; $i < min(5, max(0, (int) round($rating))); $i++)
                  <svg class="sd-card__star" aria-hidden="true"><use href="#icon_star"></use></svg>
                @endfor
              </div>
            @endif

            @include('superdong.ui.cards._facts', ['facts' => $facts ?? []])

            <div class="sd-card__footer">
              <div class="sd-card__price">
                <span class="sd-card__price-label">Giá từ:</span>
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
