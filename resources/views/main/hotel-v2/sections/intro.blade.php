@php
  $intro = $intro ?? [];
  $gallery = $gallery ?? [];
  $galleryCount = count($gallery);
@endphp

<section class="sd-section sd-hotel-intro" aria-labelledby="hotel-intro-title">
  <div class="sd-section__inner sd-hotel-intro__shell">
    <header class="sd-hotel-intro__head">
      <div class="sd-hotel-intro__meta">
        <span class="sd-hotel-intro__kicker">{{ t('kicker_hotel') }}</span>
        <h1 class="sd-hotel-intro__title" id="hotel-intro-title">{{ $intro['title'] ?? '' }}</h1>

        <div class="sd-hotel-intro__badges">
          @if(!empty($intro['typeName']))
            <span class="sd-hotel-intro__type">{{ $intro['typeName'] }}</span>
          @endif
          @if(!empty($intro['typeRating']))
            <span class="sd-hotel-intro__stars" aria-label="{{ t('hotel_rating') }}">
              @for($i = 0; $i < (int) $intro['typeRating']; ++$i)
                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.2 22 12 18.56 5.8 22 7 14.14l-5-4.87 7.1-1.01z"/></svg>
              @endfor
            </span>
          @endif
        </div>

        @if(!empty($intro['rating']))
          <div class="sd-hotel-intro__rating">
            <span class="sd-hotel-intro__rating-score">{{ $intro['rating'] }}</span>
            <span class="sd-hotel-intro__rating-text">
              {{ $intro['ratingText'] ?? '' }}
              (<strong>{{ $intro['ratingCount'] ?? 0 }}</strong> {{ t('hotel_rating_count') }})
            </span>
          </div>
        @else
          <p class="sd-hotel-intro__rating sd-hotel-intro__rating--empty">{{ t('hotel_no_rating') }}</p>
        @endif

        @if(!empty($intro['address']))
          <p class="sd-hotel-intro__address">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg>
            {{ $intro['address'] }}
          </p>
        @endif
      </div>

      @if(!empty($intro['priceFormatted']))
        <aside class="sd-hotel-intro__book" id="js_hotelIntroBook">
          @if(!empty($intro['saleOff']) && !empty($intro['priceOldFormatted']))
            <div class="sd-hotel-intro__book-old">
              <span>{!! $intro['priceOldFormatted'] !!}</span>
              <em>-{{ $intro['saleOff'] }}%</em>
            </div>
          @endif
          <div class="sd-hotel-intro__book-price">
            <span class="sd-hotel-intro__book-from">{{ t('hotel_price_from') ?? 'Từ' }}</span>
            <strong>{!! $intro['priceFormatted'] !!}</strong>
            <span class="sd-hotel-intro__book-unit">/ {{ t('hotel_per_night') ?? 'đêm' }}</span>
          </div>
          <a href="{{ $intro['roomsAnchor'] ?? '#hotel-rooms' }}" class="sd-hotel-intro__book-cta">
            {{ t('hotel_choose_room') }}
          </a>
        </aside>
      @endif
    </header>

    @if($galleryCount > 0)
      <div class="sd-hotel-gallery" data-hotel-gallery>
        <button type="button" class="sd-hotel-gallery__hero" data-hotel-gallery-open="0" aria-label="{{ t('view_more') }}">
          <img
            src="{{ $gallery[0]['src'] }}"
            alt="{{ $gallery[0]['alt'] ?? ($intro['title'] ?? '') }}"
            width="960"
            height="540"
            loading="eager"
            decoding="async"
          >
          @if($galleryCount > 1)
            <span class="sd-hotel-gallery__count">+{{ $galleryCount - 1 }}</span>
          @endif
        </button>

        @if($galleryCount > 1)
          <div class="sd-hotel-gallery__grid">
            @foreach(array_slice($gallery, 1, 5) as $index => $image)
              <button
                type="button"
                class="sd-hotel-gallery__thumb"
                data-hotel-gallery-open="{{ $index + 1 }}"
                aria-label="{{ t('view_more') }}"
              >
                <img
                  src="{{ $image['thumb'] ?? $image['src'] }}"
                  alt="{{ $image['alt'] ?? ($intro['title'] ?? '') }}"
                  width="320"
                  height="220"
                  loading="lazy"
                  decoding="async"
                >
                @if($loop->last && $galleryCount > 6)
                  <span class="sd-hotel-gallery__count">+{{ $galleryCount - 6 }}</span>
                @endif
              </button>
            @endforeach
          </div>
        @endif
      </div>
    @endif
  </div>
</section>
