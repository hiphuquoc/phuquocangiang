@php
  $intro = $intro ?? [];
  $gallery = $gallery ?? [];
  $galleryCount = count($gallery);
  $galleryId = $galleryId ?? 'product-gallery';
@endphp

<section class="sd-section sd-hotel-intro sd-product-intro" aria-labelledby="{{ $galleryId }}-title">
  <div class="sd-section__inner sd-hotel-intro__shell">
    {{-- Destination-first: ảnh lớn trước, rồi tiêu đề + book --}}
    @if($galleryCount > 0)
      <div class="sd-hotel-gallery" data-hotel-gallery>
        <button type="button" class="sd-hotel-gallery__hero" data-hotel-gallery-open="0" aria-label="{{ t('view_more') }}">
          <img
            src="{{ $gallery[0]['src'] }}"
            alt="{{ $gallery[0]['alt'] ?? ($intro['title'] ?? '') }}"
            width="960"
            height="540"
            loading="eager"
            fetchpriority="high"
            decoding="async"
          >
          @if($galleryCount > 6)
            <span class="sd-hotel-gallery__count">+{{ $galleryCount - 1 }} ảnh</span>
          @elseif($galleryCount > 1)
            <span class="sd-hotel-gallery__count">{{ $galleryCount }} ảnh</span>
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
                  src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                  data-lazy-src="{{ $image['thumb'] ?? $image['src'] }}"
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

    <header class="sd-hotel-intro__head">
      <div class="sd-hotel-intro__meta">
        @if(!empty($intro['kicker']))
          <span class="sd-hotel-intro__kicker">{{ $intro['kicker'] }}</span>
        @endif
        <h1 class="sd-hotel-intro__title" id="{{ $galleryId }}-title">{{ $intro['title'] ?? '' }}</h1>

        @if(!empty($intro['description']))
          <p class="sd-product-intro__desc">{{ $intro['description'] }}</p>
        @endif

        @if(!empty($intro['facts']))
          <ul class="sd-product-intro__facts" aria-label="Thông tin nhanh">
            @foreach($intro['facts'] as $fact)
              <li>{{ $fact }}</li>
            @endforeach
          </ul>
        @endif
      </div>

      @if(!empty($intro['priceFormatted']) || !empty($intro['ctaHref']) || !empty($intro['ctaAnchor']))
        <aside class="sd-hotel-intro__book" id="js_productIntroBook">
          <p class="sd-hotel-intro__book-label">{{ $intro['priceFromLabel'] ?? t('price_from') }}</p>

          @if(!empty($intro['saleOff']) && !empty($intro['priceOldFormatted']))
            <div class="sd-hotel-intro__book-old">
              <span>{!! $intro['priceOldFormatted'] !!}</span>
              <em>-{{ $intro['saleOff'] }}%</em>
            </div>
          @endif

          @if(!empty($intro['priceFormatted']))
            <div class="sd-hotel-intro__book-price">
              <strong>{!! $intro['priceFormatted'] !!}</strong>
              @if(!empty($intro['priceUnit']))
                <span class="sd-hotel-intro__book-unit">{{ $intro['priceUnit'] }}</span>
              @endif
            </div>
          @endif

          <a href="{{ $intro['ctaAnchor'] ?? ($intro['ctaHref'] ?? '#') }}" class="sd-hotel-intro__book-cta">
            {{ $intro['ctaLabel'] ?? t('book_tour') }}
          </a>

          @if(!empty($intro['ctaAnchor']) && !empty($intro['ctaHref']))
            <a href="{{ $intro['ctaHref'] }}" class="sd-product-intro__book-secondary">
              {{ t('form_book_tour_now') ?? ($intro['ctaLabel'] ?? t('book_tour')) }}
            </a>
          @endif

          <p class="sd-hotel-intro__book-trust">Xác nhận nhanh · Hỗ trợ 24/7</p>
        </aside>
      @endif
    </header>
  </div>
</section>
